<?php
namespace Sil\DevPortal\models;

use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\Event;
use Sil\DevPortal\models\Key;

/**
 * Model relations (defined here, overriding base class):
 * @property Api[] $apis
 * @property int $approvedKeyCount
 * @property int $pendingKeyCount
 * @property Key[] $keysProcessed
 * @property Key[] $approvedKeys
 * @property Event[] $affectedByEvents
 * @property Event[] $causedEvents
 * @property Key[] $keys
 */
class User extends \UserBase 
{
    use \Sil\DevPortal\components\FormatModelErrorsTrait;
    use \Sil\DevPortal\components\ModelFindByPkTrait;
    
    const AUTH_PROVIDER_BITBUCKET = 'Bitbucket';
    const AUTH_PROVIDER_GITHUB = 'GitHub';
    const AUTH_PROVIDER_GOOGLE = 'Google';
    const AUTH_PROVIDER_SAML = 'SAML';
    const AUTH_PROVIDER_TEST = 'TEST';
    
    const ROLE_USER = 'user';
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
    protected $currentAccessGroups = null;
    
    /**
     * See if there are any ApiVisibilityUsers specifying this User's email
     * address (which would only happen if they were created before this User
     * record, with this email address, existed). If any are found, update them
     * to use this User's ID instead (to get the foreign key relations all
     * working), setting the invited_user_email to null so that we know they
     * have been "connected" to a User.
     * 
     * WARNING: A User must NEVER be allowed to manually specify their email
     *          address. Otherwise, they could "accept" invitations intended for
     *          others. This is an example of why we must only set a User's
     *          email address to a verified value.
     */
    protected function acceptAnyPendingInvitations()
    {
        /* @var $pendingInvitations ApiVisibilityUser[] */
        $pendingInvitations = ApiVisibilityUser::model()->findAllByAttributes(array(
            'invited_user_email' => $this->email,
        ));
        foreach ($pendingInvitations as $pendingInvitation) {
            
            /* Re-save the invitation in order to trigger it's logic for finding
             * the user with the matching email address (if applicable).  */
            if ($pendingInvitation->save()) {
                Event::log(sprintf(
                    '%s (%s) is now able to see the "%s" API.',
                    $this->getDisplayName(),
                    $this->email,
                    $pendingInvitation->api->display_name
                ));
            }
        }
    }
    
    public function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        Event::log(sprintf(
            '%s (user_id %s) was deleted%s.',
            $this->getDisplayName(),
            $this->user_id,
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ));
    }
    
    public function afterSave()
    {
        parent::afterSave();
        
        $nameOfCurrentWebUser = \Yii::app()->user->getDisplayName();
        
        Event::log(sprintf(
            'User %s (%s) was %s%s.',
            $this->user_id,
            $this->getDisplayName(),
            ($this->isNewRecord ? 'added' : 'updated'),
            (is_null($nameOfCurrentWebUser) ? '' : ' by ' . $nameOfCurrentWebUser)
        ), null, null, $this->user_id);
        
        $this->acceptAnyPendingInvitations();
    }
    
    protected function beforeDelete()
    {
        if ( ! parent::beforeDelete()) {
            return false;
        }
        
        $apiVisibilityDomainsGranted = ApiVisibilityDomain::model()->findAllByAttributes(array(
            'invited_by_user_id' => $this->user_id,
        ));
        if (count($apiVisibilityDomainsGranted) > 0) {
            $this->addError(
                'user_id',
                'We cannot delete this user because they are responsible for the ability for a domain to see an API.'
            );
            return false;
        }
        
        $apiVisibilityUsersGranted = ApiVisibilityUser::model()->findAllByAttributes(array(
            'invited_by_user_id' => $this->user_id,
        ));
        if (count($apiVisibilityUsersGranted) > 0) {
            $this->addError(
                'user_id',
                'We cannot delete this user because they are responsible for the ability for a user to see an API.'
            );
            return false;
        }
        
        if (count($this->keysProcessed) > 0) {
            $this->addError(
                'user_id',
                'We cannot delete this user because they are recorded as having processed at least one key.'
            );
            return false;
        }
        
        foreach ($this->apis as $api) {
            $api->owner_id = null;
            if ( ! $api->save()) {
                $this->addError('user_id', sprintf(
                    'We cannot delete this user because we could not finish removing them as the owner of their APIs '
                    . '(though we may done so for some of their APIs): %s%s',
                    PHP_EOL,
                    $api->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        foreach ($this->keys as $key) {
            if ( ! $key->delete()) {
                $this->addError('user_id', sprintf(
                    'We cannot delete this user because we could not finish deleting the user\'s keys (though we may '
                    . 'have deleted some of them): %s%s',
                    PHP_EOL,
                    $key->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        /* @var $apiVisibilityUsersReceived ApiVisibilityUser */
        $apiVisibilityUsersReceived = ApiVisibilityUser::model()->findAllByAttributes(array(
            'invited_user_id' => $this->user_id,
        ));
        foreach ($apiVisibilityUsersReceived as $apiVisibilityUserReceived) {
            if ( ! $apiVisibilityUserReceived->delete()) {
                $this->addError('user_id', sprintf(
                    'We cannot delete this user because we could not finish deleting the invitations they received to '
                    . 'see private APIs (though we may have deleted some of them): %s%s',
                    PHP_EOL,
                    $apiVisibilityUserReceived->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        /* NOTE: Simply using $this->affectedByEvents was retrieving a cached
         *       list of Events, and so we were failing to update Events created
         *       earlier in this beforeDelete() method.  */
        /* @var $eventsAffectingUser Event[] */
        $eventsAffectingUser = Event::model()->findAllByAttributes(array(
            'affected_user_id' => $this->user_id,
        ));
        foreach ($eventsAffectingUser as $eventAffectingUser) {
            $eventAffectingUser->affected_user_id = null;
            if ( ! $eventAffectingUser->save()) {
                $this->addError('user_id', sprintf(
                    'We could not delete this User because we were not able to finish updating our records of events '
                    . 'that affected the User: %s%s',
                    PHP_EOL,
                    $eventAffectingUser->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        /* NOTE: Simply using $this->causedEvents fails to retrieve the most
         *       current list of Events caused by this User.  */
        /* @var $eventsCausedByUser Event[] */
        $eventsCausedByUser = Event::model()->findAllByAttributes(array(
            'acting_user_id' => $this->user_id,
        ));
        foreach ($eventsCausedByUser as $eventCausedByUser) {
            $eventCausedByUser->acting_user_id = null;
            if ( ! $eventCausedByUser->save()) {
                $this->addError('user_id', sprintf(
                    'We could not delete this User because we were not able to finish updating our records of events '
                    . 'performed by the User: %s%s',
                    PHP_EOL,
                    $eventCausedByUser->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Find out whether this User is allowed to approve the given Key. Note that
     * only pending Keys can be approved, so false will be returned for any
     * non-pending Keys.
     * 
     * @param Key $key The Key to be approved.
     * @return boolean
     */
    public function canApproveKey($key)
    {
        // If no Key was given, say no.
        if ( ! ($key instanceof Key)) {
            return false;
        }
        
        // Only pending keys can be approved.
        if ( ! $key->isPending()) {
            return false;
        }
        
        // Otherwise, only say yes any of the following situations.
        return $key->isToApiOwnedBy($this) ||
               $this->isAdmin();
    }
    
    /**
     * Find out whether this User is allowed to deny the given (pending) Key.
     * 
     * @param Key $key The (pending) Key.
     * @return boolean
     */
    public function canDenyKey($key)
    {
        /* NOTE: The authority to deny a key is the same as the authority to
         *       approve a key.  */
        return $this->canApproveKey($key);
    }
    
    /**
     * Find out whether this user is allowed to delete the given Key.
     * 
     * @param Key $key The Key in question.
     * @return boolean
     */
    public function canDeleteKey($key)
    {
        return (($key instanceof Key) && $key->canBeDeletedBy($this));
    }
    
    /**
     * Find out whether this User is allowed to invite all users with email
     * addresses in a particular domain name to see the given Api.
     * 
     * @param Api $api The Api in question.
     * @return boolean
     */
    public function canInviteDomainToSeeApi($api)
    {
        if ( ! ($api instanceof Api)) {
            return false;
        }
        
        return $this->isOwnerOfApi($api);
    }
    
    /**
     * Find out whether this User is allowed to invite a user (by email address)
     * to see the given Api.
     * 
     * @param Api $api The Api in question.
     * @return boolean
     */
    public function canInviteUserToSeeApi($api)
    {
        if ( ! ($api instanceof Api)) {
            return false;
        }
        
        return $this->isOwnerOfApi($api);
    }
    
    /**
     * Find out whether this user is allowed to reset the given Key.
     * 
     * @param Key $key The Key in question.
     * @return boolean
     */
    public function canResetKey($key)
    {
        // If no Key was given, say no.
        if ($key === null) {
            return false;
        }
        
        // Only approved Keys can be reset.
        if ( ! $key->isApproved()) {
            return false;
        }
        
        // Finally, only allow it if the Key belongs to this User.
        return $key->isOwnedBy($this);
    }
    
    /**
     * Find out whether this user is allowed to revoke the given Key.
     * 
     * @param Key $key The Key in question.
     * @return boolean
     */
    public function canRevokeKey($key)
    {
        // If no key was given, say no.
        if ( ! ($key instanceof Key)) {
            return false;
        }
        
        if ( ! $key->isApproved()) {
            return false;
        }
        
        // A user cannot revoke their own key (just reset/delete it).
        if ($key->isOwnedBy($this)) {
            return false;
        }
        
        // If the key is to an API that belongs to this user, say yes.
        if ($key->isToApiOwnedBy($this)) {
            return true;
        }
        
        // If the user is an admin, say yes.
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }
        
        // Otherwise, say no.
        return false;
    }
    
    /**
     * Find out whether this User is allowed to see the given Key.
     * 
     * @param Key $key The Key in question.
     * @return boolean Whether the user is allowed to see the Key.
     */
    public function canSeeKey($key)
    {
        // If NOT given a Key, say no
        if ( ! ($key instanceof Key)) {
            return false;
        }
        
        // If the user is an admin, say yes.
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }
        
        // If the user owns the API that the Key is for, say yes.
        if ($this->isOwnerOfApi($key->api)) {
            return true;
        }
        
        // If the user is the one this Key belongs to, say yes.
        if ($key->isOwnedBy($this)) {
            return true;
        }
        
        // No one else is allowed to see the Key.
        return false;
    }
    
    /**
     * Find out whether this User is allowed to see the Keys to the given Api.
     * 
     * @param Api $api The API in question.
     * @return boolean Whether the user is allowed to see the API's Keys.
     */
    public function canSeeKeysForApi($api)
    {
        // If NOT given an API, say no
        if ( ! ($api instanceof Api)) {
            return false;
        }
        
        // If the user is an admin, say yes.
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }
        
        // If the user owns the given API, say yes.
        if ($this->isOwnerOfApi($api)) {
            return true;
        }
        
        // No one else is allowed to see the API's keys.
        return false;
    }
    
    /**
     * Get the list of access groups that this User is in.
     * 
     * @return array|null
     */
    public function getAccessGroups()
    {
        // Return the list of access groups if set, or an empty array if not.
        return $this->currentAccessGroups ?: array();
    }
    
    /**
     * Get a display name for the User. If no explicit display name has been
     * set, combine the first and last names and use that.
     * 
     * @return string A display name for this User
     */
    public function getDisplayName()
    {
        return ($this->display_name ?: $this->first_name . ' ' . $this->last_name);
    }
    
    public function getEmailAddressDomain()
    {
        list(, $domain) = explode('@', $this->email);
        return $domain;
    }
    
    /**
     * @return Key[]
     * @throws \Exception
     */
    public function getKeysWithApiNames()
    {
        // Get the ID of the current user (as an integer).
        $currentUserId = (int)$this->user_id;
        
        // If it's an invalid value, throw an exception.
        if ($currentUserId <= 0) {
            throw new \Exception(
                'Cannot get Keys / APIs for this user because we do '
                . 'not know the user\'s ID.'
            );
        }
        
        // Get all of this user's keys, but also include the API names.
        return Key::model()->with('api')->findAllByAttributes(array(
            'user_id' => $currentUserId,
        ), array(
            'order' => 'api.display_name',
        ));
    }
    
    public static function getRoles()
    {
        return array(
            self::ROLE_USER => 'User',
            self::ROLE_OWNER => 'API Owner',
            self::ROLE_ADMIN => 'Admin',
        );
    }
    
    /**
     * Convert a role value to a user-friendly string.
     * 
     * @param mixed $roleValue The role value to be converted to a user-friendly
     *     string. Should be equal to the value of one of the User class's
     *     constants whose name begins with "ROLE_".
     * @return (string|null) The string, or null if that role value is unknown.
     */
    public static function getRoleString($roleValue)
    {
        // Get the array of roles.
        $roles = self::getRoles();
        
        // Return the one associated with the given value (if any).
        if (isset($roles[$roleValue])) {
            return $roles[$roleValue];
        } else {
            return null;
        }
    }
    
    public static function getStatuses()
    {
        return array(
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
        );
    }
    
    /**
     * Convert a status value to a user-friendly string.
     * 
     * @param mixed $statusValue The status value to be converted to a
     *     user-friendly string. Should be equal to the value of one of the User
     *     class's constants whose name begins with "STATUS_".
     * @return (string|null) The string, or null if that status value is
     *     unknown.
     */
    public static function getStatusString($statusValue)
    {
        // Get the array of statuses.
        $statuses = self::getStatuses();
        
        // Return the one associated with the given value (if any).
        if (isset($statuses[$statusValue])) {
            return $statuses[$statusValue];
        } else {
            return null;
        }
    }
    
    /**
     * Get the usage stats for all of the APIs.
     * 
     * @param string $interval The name of the time interval (e.g. -
     *     'second', 'minute',  'hour', 'day') by which the data should be
     *     grouped.
     * @param int $rewindBy (Optional:) How many intervals to "back up"
     *     the starting point by. Used for getting older data.
     * @return \UsageStats
     */
    public function getUsageStatsForAllApis($interval, $rewindBy = 0)
    {
        // Ensure that only admins can do this.
        if ( ! $this->isAdmin()) {
            throw new \Exception(
                'You are not allowed to get usage stats for the list of all '
                . 'APIs.',
                1426855754
            );
        }
        
        // Get the list of all APIs.
        /* @var $apis Api[] */
        $apis = Api::model()->findAll();

        // Get the APIs' usage.
        $usageStats = new \UsageStats($interval, $rewindBy);
        foreach ($apis as $api) {
            try {
                $usage = $api->getUsage($interval, true, $rewindBy);
            } catch (\Exception $e) {
                $usage = $e->getMessage();
            }
            $usageStats->addEntry($api->display_name, $usage);
        }
        
        // Return the resulting usage stats.
        return $usageStats;
    }
    
    /**
     * Get the usage stats for the APIs that this user owns.
     * 
     * @param string $interval The name of the time interval (e.g. -
     *     'second', 'minute',  'hour', 'day') by which the data should be
     *     grouped.
     * @param int $rewindBy (Optional:) How many intervals to "back up"
     *     the starting point by. Used for getting older data.
     * @return \UsageStats
     */
    public function getUsageStatsForApis($interval, $rewindBy = 0)
    {
        // Get the APIs that this user owns.
        $apis = $this->apis;

        // Get the APIs' usage.
        $usageStats = new \UsageStats($interval, $rewindBy);
        foreach ($apis as $api) {
            try {
                $usage = $api->getUsage($interval, true, $rewindBy);
            } catch (\Exception $e) {
                $usage = $e->getMessage();
            }
            $usageStats->addEntry($api->display_name, $usage);
        }
        
        // Return the resulting usage stats.
        return $usageStats;
    }
    
    /**
     * Get the usage stats for the approved Keys that this user has.
     * 
     * @param string $interval The name of the time interval (e.g. -
     *     'second', 'minute',  'hour', 'day') by which the data should be
     *     grouped.
     * @param int $rewindBy (Optional:) How many intervals to "back up"
     *     the starting point by. Used for getting older data.
     * @return \UsageStats
     */
    public function getUsageStatsForKeys($interval, $rewindBy = 0)
    {
        // Get all of this user's Keys.
        $keys = Key::model()->with('api')->findAllByAttributes(array(
            'status' => Key::STATUS_APPROVED,
            'user_id' => $this->user_id,
        ), array(
            'order' => 'api.display_name',
        ));

        // Get the keys' usage.
        $usageStats = new \UsageStats($interval, $rewindBy);
        foreach ($keys as $key) {
            try {
                $usage = $key->getUsage($interval, true, $rewindBy);
            } catch (\Exception $e) {
                $usage = $e->getMessage();
            }
            $usageStats->addEntry($key->api->display_name, $usage);
        }
        
        // Return the resulting usage stats.
        return $usageStats;
    }
    
    /**
     * Get the usage stats for all of the APIs.
     * 
     * @param string $interval The name of the time interval (e.g. -
     *     'second', 'minute',  'hour', 'day') by which the data should be
     *     grouped.
     * @param int $rewindBy (Optional:) How many intervals to "back up"
     *     the starting point by. Used for getting older data.
     * @return \UsageStats
     */
    public function getUsageStatsTotals($interval, $rewindBy = 0)
    {
        // Ensure that only admins can do this.
        if ( ! $this->isAdmin()) {
            throw new \Exception(
                'You are not allowed to get usage stats totals.',
                1426860333
            );
        }
        
        // Get the list of all APIs.
        /* @var $apis Api[] */
        $apis = Api::model()->findAll();
        
        // Get the total, combined usage of all APIs.
        $usageStats = new \UsageStats($interval, $rewindBy);
        $totalUsage = array();
        foreach ($apis as $api) {
            try {
                
                // Try to get the usage for this API. If that worked, add it
                // to our total.
                $apiUsage = $api->getUsage($interval, true, $rewindBy);
                $totalUsage = \UsageStats::combineUsageCategoryArrays(
                    $totalUsage,
                    $apiUsage
                );
                
            } catch (\Exception $e) {
                
                // If there was an error, add it as an entry in our usages
                // stats.
                $errorMessage = $e->getMessage();
                $usageStats->addEntry($api->code, $errorMessage);
            }
        }
        $usageStats->addEntry('Total usage (of all APIs)', $totalUsage);
        
        // Return the resulting usage stats.
        return $usageStats;
    }
    
    /**
     * Retrieve the User's active (i.e. - approved, not revoked) Key to the
     * given Api (if any such Key exists).
     * 
     * @param Api $api The Api in question.
     * @return Key|null The Key, or null if it doesn't exist.
     */
    public function getActiveKeyToApi($api)
    {
        // If not given an API, return null.
        if ( ! ($api instanceof Api)) {
            return null;
        }
        
        return Key::model()->findByAttributes(array(
            'api_id' => $api->api_id,
            'user_id' => $this->user_id,
            'status' => Key::STATUS_APPROVED,
        ));
    }
    
    public static function getAuthProviders()
    {
        return array(
            self::AUTH_PROVIDER_BITBUCKET,
            self::AUTH_PROVIDER_GITHUB,
            self::AUTH_PROVIDER_GOOGLE,
            self::AUTH_PROVIDER_SAML,
            self::AUTH_PROVIDER_TEST,
        );
    }
    
    /**
     * Find out whether the User has an active (i.e. - approved, not revoked)
     * Key to the given Api.
     * 
     * @param Api $api The Api in question.
     * @return boolean True if so, otherwise false.
     */
    public function hasActiveKeyToApi($api)
    {
        return ($this->getActiveKeyToApi($api) !== null);
    }
    
    /**
     * Find out whether the User has permission to administer the given Api.
     * 
     * @param Api $api The Api in question.
     * @return boolean True if so, otherwise false.
     */
    public function hasAdminPrivilegesForApi($api)
    {
        // If not given an API, then say no.
        if ( ! ($api instanceof Api)) {
            return false;
        }
        
        return $this->isAdmin() || $this->isOwnerOfApi($api);
    }
    
    public function hasOwnerPrivileges()
    {
        $role = $this->role;
        return (($role === self::ROLE_ADMIN) || ($role === self::ROLE_OWNER));
    }
    
    /**
     * Retrieve the User's pending Key (if such a Key exists) for the given Api.
     * 
     * @param Api $api The Api in question.
     * @return Key|null The pending Key, or null if no such Key exists.
     */
    public function getPendingKeyForApi($api)
    {
        // If not given an API, return null.
        if ( ! ($api instanceof Api)) {
            return null;
        }
        
        return Key::model()->findByAttributes(array(
            'api_id' => $api->api_id,
            'user_id' => $this->user_id,
            'status' => Key::STATUS_PENDING,
        ));
    }
    
    /**
     * Find out whether the User has a pending Key for the given Api.
     * 
     * @param Api $api The Api in question.
     * @return boolean True if so, otherwise false.
     */
    public function hasPendingKeyForApi($api)
    {
        return ($this->getPendingKeyForApi($api) !== null);
    }
    
    /**
     * Determin whether the user is an admin.
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return ($this->role === self::ROLE_ADMIN);
    }
    
    public function isDisabled()
    {
        /* NOTE: This should be a loose comparison (so that 1 and '1' are
        /*       considered equal) because $this->status will come back as a
        /*       string.  */
        return ($this->status != self::STATUS_ACTIVE);
    }
    
    public static function isEmailAddressInUse($emailAddress)
    {
        $user = self::model()->findByAttributes(array(
            'email' => $emailAddress,
        ));
        return ($user !== null);
    }
    
    /**
     * Find out whether this User is in the named access group
     * (case-insensitive).
     * 
     * @param string $groupName The name of the access group.
     * @return boolean True if the user is in the access group, otherwise false.
     */
    public function isInAccessGroup($groupName)
    {
        if ($this->currentAccessGroups === null) {
            return false;
        } else {
            return in_array(
                strtoupper(trim($groupName)),
                $this->currentAccessGroups,
                true
            );
        }
    }
    
    /**
     * Find out whether this User has been individually invited to see this Api.
     * 
     * @param Api $api The Api in question.
     * @return boolean
     */
    public function isIndividuallyInvitedToSeeApi($api)
    {
        $apiVisibilityUser = ApiVisibilityUser::model()->findByAttributes(array(
            'api_id' => $api->api_id,
            'invited_user_id' => $this->user_id,
        ));
        return ($apiVisibilityUser !== null);
    }
    
    /**
     * Find out whether this User has an email address domain that has been
     * invited to see this Api. This should be case-insensitive.
     * 
     * @param Api $api The Api in question.
     * @param integer|null $excludedAvdId (Optional:) The ID of an
     *     ApiVisibilityDomain that should be ignored. Useful for seeing whether
     *     a Key depends on a particular ApiVisibilityDomain.
     * @return boolean
     */
    public function isInvitedByDomainToSeeApi($api, $excludedAvdId = null)
    {
        $criteria = new \CDbCriteria();
        if ($excludedAvdId !== null) {
            $criteria->addNotInCondition(
                'api_visibility_domain_id',
                array($excludedAvdId)
            );
        }
        $apiVisibilityDomain = ApiVisibilityDomain::model()->findByAttributes(array(
            'api_id' => $api->api_id,
            'domain' => $this->getEmailAddressDomain(),
        ), $criteria);
        return ($apiVisibilityDomain !== null);
    }
    
    /**
     * Find out whether this User is the owner of the given Api.
     * 
     * @todo Should this also check whether the user has a role of owner?
     * 
     * @param Api $api The API in question.
     * @return boolean Whether the user is the owner of the API.
     */
    public function isOwnerOfApi($api)
    {
        // If given an API...
        if ($api instanceof Api) {
            
            // If the API has an owner set...
            $apiOwnerId = (int)$api->owner_id;
            if ($apiOwnerId > 0) {
                
                // If the API is owned by this user, indicate that.
                $userId = (int)$this->user_id;
                if ($apiOwnerId === $userId) {
                    return true;
                }
            }
        }
        
        // If we reach this point, then the user is NOT the owner of the API.
        return false;
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array('email', 'email'),
            array(
                'email',
                'unique',
                'caseSensitive' => false,
                'message' => 'That email address ({value}) already belongs to '
                . 'a different account.',
            ),
            array('auth_provider', 'validateAuthProvider'),
            array(
                'updated',
                'default',
                'value' => new \CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'update',
            ),
            array(
                'created,updated',
                'default',
                'value' => new \CDbExpression('NOW()'),
                'setOnEmpty' => true,
                'on' => 'insert',
            ),
            array('display_name', 'assembleDisplayNameIfEmpty'),
            array('auth_provider', 'required'),
        ), parent::rules());
    }
    
    /**
     * NOTE: We are completely overriding (and ignoring) the base class's
     *       relations definition.  This is because Gii autogenerates them
     *       incorrectly due to not understanding the two possible relationships
     *       between Keys and Users (requested by vs. processed by) and between
     *       Users and ApiVisibilityUser/ApiVisibilityDomain. Those latter
     *       relations we simply do not define here at all, leaving those
     *       objects to be retrieve via static model functions (such as
     *       findByAttributes).
     *
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'apis' => array(self::HAS_MANY, '\Sil\DevPortal\models\Api', 'owner_id'),
            'affectedByEvents' => array(self::HAS_MANY, '\Sil\DevPortal\models\Event', 'affected_user_id'),
            'causedEvents' => array(self::HAS_MANY, '\Sil\DevPortal\models\Event', 'acting_user_id'),
            'approvedKeyCount' => array(
                self::STAT,
                '\Sil\DevPortal\models\Key',
                'user_id',
                'condition' => 'status = :status',
                'params' => array(':status' => Key::STATUS_APPROVED),
            ),
            'approvedKeys' => array(
                self::HAS_MANY,
                Key::class,
                'user_id',
                'condition' => 'status = :status',
                'params' => array(':status' => Key::STATUS_APPROVED),
            ),
            'pendingKeyCount' => array(
                self::STAT,
                '\Sil\DevPortal\models\Key',
                'user_id',
                'condition' => 'status = :status',
                'params' => array(':status' => Key::STATUS_PENDING),
            ),
            'keys' => array(self::HAS_MANY, '\Sil\DevPortal\models\Key', 'user_id'),
            'keysProcessed' => array(self::HAS_MANY, '\Sil\DevPortal\models\Key', 'processed_by'),
        );
    }
    
    /**
     * Set the list of access groups that this User is in.
     * 
     * @param array $groups The list of access groups (which will be
     *     uppercased).
     */
    public function setAccessGroups($groups)
    {
        $this->currentAccessGroups = array_map('strtoupper', $groups);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    /**
     * Assemble the user's display name if it does not yet exist.
     * 
     * @param string $attribute The name of the attribute to be validated.
     */
    public function assembleDisplayNameIfEmpty($attribute)
    {
        if (empty($this->$attribute)) {
            $this->display_name = $this->getDisplayName();
        }
    }
    
    /**
     * Validate that the specified auth_provider is an acceptable value.
     * 
     * @param string $attribute The name of the attribute to be validated.
     * @param array $params The options specified in the validation rule.
     */
    public function validateAuthProvider($attribute, $params)
    {
        if ( ! in_array($this->$attribute, self::getAuthProviders())) {
            $this->addError(
                $attribute,
                'An unknown authentication provider was specified: '
                . $this->$attribute
            );
        }
    }
}
