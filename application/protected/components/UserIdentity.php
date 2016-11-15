<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\UserAuthenticationData;
use Sil\DevPortal\components\WrongAuthProviderException;
use Sil\DevPortal\models\User;

abstract class UserIdentity extends \CBaseUserIdentity
{
    const ERROR_DISABLED_USER = 500;
    const ERROR_WRONG_AUTH_PROVIDER = 600;
    
    /**
     * A value that uniquely represents the identity (e.g. primary key value).
     * 
     * @var mixed The User ID from the database.
     */
    private $id = null;
    
    /**
     * The display name for the identity.
     * 
     * NOTE: Had to specifically add this here or we failed to get back the name
     * set using our setName() function.
     * 
     * @var string|null
     */
    private $name = null;
    
    /**
     * Attempt to authenticate the user, and indicate whether the attempt was
     * successful. This may result in the user's browser being redirected to one
     * of the available authentication services.
     * 
     * NOTE: This method should NOT be overridden. Subclasses should instead
     *       implement/override the applicable functions called by this one.
     * 
     * @param string|null $providerSlug The URL-safe (aka. "slug") version of
     *     the name of what provider to use within the current authentication
     *     type (such as which HybridAuth provider to use).
     * @return boolean Whether the authentication was successful.
     */
    final public function authenticate($providerSlug = null)
    {
        $userAuthData = $this->getUserAuthData($providerSlug);

        try {
            /* @var $user User */
            $user = $this->findUserRecord($userAuthData);

            if ($user === null) {
                $user = $this->createUserRecord($userAuthData);
            } else {
                
                /* Update the user record to match any changes in the name, etc.
                 * as returned by the auth. provider.
                 * 
                 * NOTE: This also provides an opportunity to populate any
                 * missing auth_provider_user_identifier fields for Insite
                 * logins done before we were tracking that data.
                 */
                $this->updateUserRecord($userAuthData);
                
                // Update our local variable with the current values from the
                // database.
                $user->refresh();
            }

            if ($user->isDisabled()) {
                $this->errorCode = self::ERROR_DISABLED_USER;
                $this->errorMessage = 'Your account is disabled.';
            } else {
            
                // Load the necessary pieces of data into the user's session.
                $this->loadIdentity(
                    $user,
                    $userAuthData->getAccessGroups(),
                    $userAuthData->getUuid()
                );
                
                // Record that authentication was successful.
                $this->errorCode = self::ERROR_NONE;
            }
        } catch (WrongAuthProviderException $e) {
            $this->errorCode = self::ERROR_WRONG_AUTH_PROVIDER;
            $this->errorMessage = $e->getMessage();
        }

        // Indicate whether the user is now authenticated (i.e. - whether they
        // can proceed with using the website).
        return $this->getIsAuthenticated();
    }
    
    /**
     * Create (and return) a new User record with the applicable data from the
     * given user auth. data.
     * 
     * @param UserAuthenticationData $userAuthData
     * @return User The new User.
     */
    protected function createUserRecord($userAuthData)
    {
        if (User::isEmailAddressInUse($userAuthData->getEmailAddress())) {
            throw new \Exception(
                'A user with that email address already exists in our '
                . 'database.',
                1444679782
            );
        }
        
        // Create the new User instance.
        $user = new User();
        $user->auth_provider = $userAuthData->getAuthProvider();
        $user->auth_provider_user_identifier = $userAuthData->getAuthProviderUserIdentifier();
        $user->email = $userAuthData->getEmailAddress();
        $user->first_name = $userAuthData->getFirstName();
        $user->last_name = $userAuthData->getLastName();
        $user->display_name = $userAuthData->getDisplayName();

        // Assign them a default role and mark them as active.
        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_ACTIVE;

        // Try to save the new User record.
        if ( ! $user->save()) {
            throw new \Exception(
                'Failed to save new User to database: ' . PHP_EOL
                . $user->getErrorsAsFlatTextList(),
                1444679705
            );
        }
        
        return $user;
    }
    
    /**
     * Find (and return) the User record that corresponds to the user data
     * provided by the authentication service. If no such user is found, null is
     * returned.
     * 
     * @param UserAuthenticationData $userAuthData
     * @return User|null
     */
    protected function findUserRecord($userAuthData)
    {
        $authProvider = $userAuthData->getAuthProvider();
        $authProviderUserIdentifier = $userAuthData->getAuthProviderUserIdentifier();
        
        if ( ! $authProvider) {
            throw new \InvalidArgumentException(
                'We could not tell which authentication provider you used to '
                . 'sign in, so we do not have enough information to find your '
                . 'account on our website.',
                1444931792
            );
        }
        
        if ( ! $authProviderUserIdentifier) {
            throw new \InvalidArgumentException(
                'The authentication provider (' . $authProvider . ') failed to '
                . 'return an identifier for you when you signed in, so we are '
                . 'unable to find your account on our website.',
                1444931848
            );
        }
        
        $user = User::model()->findByAttributes(array(
            'auth_provider' => $authProvider,
            'auth_provider_user_identifier' => $authProviderUserIdentifier,
        ));
        
        if ($user === null) {
            $this->warnIfEmailIsInUseByDiffAuthProvider($userAuthData);
        }
        
        return $user;
    }

    /**
     * Return the auth. type for the UserIdentity subclass in use.
     * 
     * @return string
     */
    abstract public function getAuthType();

    /**
     * Returns a value that uniquely represents the identity (if known).
     * 
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the URL to send the user's browser to in order to log them out of the
     * applicable authentication service. Returns null if there is no need to
     * send the user to the auth. service's website.
     * 
     * @return string|null
     */
    abstract public function getLogoutUrl();
    
    /**
     * Returns the display name for the identity.
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get the data about this user as returned by the authentication provider.
     * 
     * @param string|null $providerSlug The URL-safe (aka. "slug") version of
     *     the name of what provider to use within the current authentication
     *     type (such as which HybridAuth provider to use).
     * @return \Sil\DevPortal\components\UserAuthenticationData
     */
    abstract public function getUserAuthData($providerSlug);
    
    /**
     * Load the necessary information from the given parameters in order to
     * populate the required fields for this UserIdentity.
     * 
     * @param User $user
     * @param array $accessGroups
     * @param mixed $uuid
     */
    public function loadIdentity(
        $user,
        $accessGroups = array(),
        $uuid = null
    ) {
        // Set the User model's list of access groups.
        $user->setAccessGroups($accessGroups);
        
        // Save the necessary information to the session.
        $this->setState('user', $user);
        $this->setState('uuid', $uuid);
        $this->setState('authType', $this->getAuthType());
        
        // Load the necessary information into this identity class.
        $this->setId($user->user_id);
        $this->setName($user->display_name);
    }
    
    /**
     * Set the ID unique to this user, for use by the user's session.
     * 
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Set the display name for the identity.
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Update the corresponding User record with the applicable data from the
     * given user auth. data, only changing those fields that we decided to
     * update each time the user logs in.
     * 
     * @param UserAuthenticationData $userAuthData
     */
    protected function updateUserRecord($userAuthData)
    {
        // Get the user record.
        $user = $this->findUserRecord($userAuthData);
        
        if ($user === null) {
            throw new \Exception(
                'No such User found.',
                1445976913
            );
        }
        
        // Update the names.
        $user->first_name = $userAuthData->getFirstName();
        $user->last_name = $userAuthData->getLastName();
        $user->display_name = $userAuthData->getDisplayName();

        // Try to save the changes.
        if ( ! $user->save()) {
            throw new \Exception(
                'Failed to update our database with the data from the '
                . 'authentication provider: ' . PHP_EOL
                . $user->getErrorsAsFlatTextList(),
                1444936203
            );
        }
    }
    
    /**
     * If it looks like the user is trying to sign in from the wrong
     * authentication provider, fail loudly.
     * 
     * @param UserAuthenticationData $userAuthData
     * @throws WrongAuthProviderException
     */
    protected function warnIfEmailIsInUseByDiffAuthProvider($userAuthData)
    {
        /* @var $possibleUser User */
        $possibleUser = User::model()->findByAttributes(array(
            'email' => $userAuthData->getEmailAddress(),
        ));
        
        if ($possibleUser !== null) {
            
            $attemptedAuthProvider = $userAuthData->getAuthProvider();
            $authProviderFromDb = $possibleUser->auth_provider;
            
            if ($attemptedAuthProvider !== $authProviderFromDb) {
                throw new WrongAuthProviderException(
                    sprintf(
                        'It looks like you are trying to sign in using %s, but '
                        . 'your account seems to be set up to log in using %s. '
                        . 'Please use %s to log in.',
                        $attemptedAuthProvider,
                        $authProviderFromDb,
                        $authProviderFromDb
                    ),
                    1444936917
                );
            }
        }
    }
}
