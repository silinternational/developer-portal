<?php

use ApiAxle\Api\Api as AxleApi;
use ApiAxle\Api\Key as AxleKey;
use ApiAxle\Api\Keyring as AxleKeyring;

class Key extends KeyBase
{
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_PENDING = 'pending';
    const STATUS_REVOKED = 'revoked';
    
    public function afterSave()
    {
        parent::afterSave();
        
        try {
            if ($this->status === self::STATUS_PENDING) {
                if ($this->isNewRecord) {
                    $this->notifyApiOwnerOfPendingRequest();
                }
            } elseif ($this->status === self::STATUS_DENIED) {
                $this->notifyUserOfDeniedKey();
            } elseif ($this->status === self::STATUS_REVOKED) {
                $this->notifyUserOfRevokedKey();
                $this->notifyApiOwnerOfRevokedKey();
            }
        } finally {
            $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
            \Event::log(sprintf(
                'Key %s (status: %s) for User %s (%s) to Api %s (%s) was %s%s.',
                $this->key_id,
                $this->status,
                $this->user_id,
                (isset($this->user) ? $this->user->getDisplayName() : ''),
                $this->api_id,
                (isset($this->api) ? $this->api->display_name : ''),
                ($this->isNewRecord ? 'created' : 'updated'),
                (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
            ), $this->api_id, $this->key_id, $this->user_id);
        }
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array(
                'updated',
                'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'update',
            ),
            array(
                'created,updated,requested_on',
                'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'insert',
            ),
            array(
                'status',
                'in',
                'range' => self::getValidStatusValues(),
                'allowEmpty' => false,
                'message' => 'That is not a valid key status.',
            ),
            array('processed_on', 'recordDateWhenProcessed'),
            array('api_id', 'onlyAllowOneKeyPerApi', 'on' => 'insert'),
        ), parent::rules());
    }
    
    public function beforeSave()
    {
        if ( ! parent::beforeSave()) {
            return false;
        }
        
        global $ENABLE_AXLE;
        if (isset($ENABLE_AXLE) && !$ENABLE_AXLE) {
            return true;
        }
        
        /* ***** ApiAxle-specific checks: ***** */
        
        if ($this->status === \Key::STATUS_APPROVED) {
        
            $axleKey = new AxleKey(Yii::app()->params['apiaxle']);
            $keyData = array(
                'sharedSecret' => $this->secret,
                'qpd' => (int)$this->queries_day,
                'qps' => (int)$this->queries_second,
            );

            /**
             * If Keyring does not already exist, we need to create it.
             */
            $user = User::model()->findByPk($this->user_id);

            /**
             * @todo Verify that a change to the User's email won't break anything
             *       related to this.
             */
            $keyringName = md5($user->email);
            $axleKeyring = new AxleKeyring(Yii::app()->params['apiaxle']);
            try {
                $axleKeyring->get($keyringName);
            } catch (\Exception $e) {
                $axleKeyring->create($keyringName);
            }
            
            /* Get the current key (if/as it exists in the database) to see
             * whether this key would already exist in ApiAxle.  */
            if ($this->key_id !== null) {
                $current = Key::model()->findByPk($this->key_id);
                $currentValue = (($current !== null) ? $current->value : null);
            } else {
                $currentValue = null; 
            }
            
            if ($currentValue === null) {
                try {
                    // Create new Key in ApiAxle.
                    $axleKey->create($this->value, $keyData);
                    
                    // Link key to keyring.
                    $axleKeyring->linkKey($axleKey);
                    
                    // Link key to Api.
                    $api = Api::model()->findByPk($this->api_id);
                    $axleApi = new AxleApi(Yii::app()->params['apiaxle'], $api->code);
                    $axleApi->linkKey($axleKey);
                    return true;
                } catch (\Exception $e) {
                    $this->addError('value',$e->getMessage());
                    return false;
                }
            }
            
            try {
                if ($currentValue != $this->value) {
                    /*
                     * Need to delete existing key and create new key
                     */
                    $axleKey->delete($current->value);
                    $axleKey->create($this->value, $keyData);
                    /**
                     * Link key to keyring
                     */
                    $axleKeyring->linkKey($axleKey);
                    /**
                     * Link key to Api
                     */
                    $api = Api::model()->findByPk($this->api_id);
                    $axleApi = new AxleApi(Yii::app()->params['apiaxle'], $api->code);
                    $axleApi->linkKey($axleKey);
                } else {
                    /**
                    * Update Key in apiaxle
                    */
                    $axleKey->get($this->value);
                    $axleKey->update($keyData);
                }
                return true;
            } catch (\Exception $e) {
                $this->addError('value',$e->getMessage());
                return false;
            }
        } elseif ($this->status === \Key::STATUS_DENIED) {
            
            /**
             * @todo Figure out what to do in ApiAxle when a Key in our database
             *       is denied, and whether to do it in beforeSave() or
             *       afterSave().
             */
            
            // Make sure the key does not exist in ApiAxle.
            if ($this->value !== null) {
                return $this->deleteFromApiAxle();
            }
            return true;
            
        } elseif ($this->status === \Key::STATUS_PENDING) {
            
            /**
             * @todo Figure out what to do in ApiAxle (if anything) when a Key
             *       in our database is pending, and whether to do it in
             *       beforeSave() or afterSave().
             */
            
            // TEMP
            return true;
            
        } elseif ($this->status === \Key::STATUS_REVOKED) {
            
            /**
             * @todo Figure out how to delete the key from Axle when the Key
             *       is revoked.
             */
            
            // Make sure the key does not still exist in ApiAxle.
            return $this->deleteFromApiAxle();
            
        } else {
            
            $this->addError('status', 'Unknown status value.');
            return false;
        }
    }
    
    public function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        \Event::log(sprintf(
            '%s\'s (user_id %s) Key (key_id %s) to the "%s" API (api_id %s) was deleted%s.',
            (isset($this->user) ? $this->user->getDisplayName() : 'A User'),
            $this->user_id,
            $this->key_id,
            (isset($this->api) ? $this->api->display_name : ''),
            $this->api_id,
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ), $this->api_id, null, $this->user_id);
        
        $this->sendKeyDeletionNotification();
    }
    
    /**
     * Attempt to approve a pending (i.e. - requested) Key, receiving back an
     * indicator of whether it was successful.
     * 
     * @param \User $approvingUser The user to record as the one who approved
     *     the request for this Key (for Keys to Apis that require approval).
     *     Defaults to null (used for auto-approved Keys).
     * @return boolean True if the Key was successfully approved. If not, check
     *     the Key's list of errors to find out why.
     * @throws \Exception
     */
    public function approve($approvingUser = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            $this->addError('status', 'Only pending keys can be approved.');
            return false;
        }
        
        if ($this->requiresApproval()) {
            if ( ! $approvingUser instanceof \User) {
                // This should not happen in the normal flow of things... thus
                // the exception.
                throw new \Exception(
                    'No User provided when trying to approve a Key that '
                    . 'requires approval.',
                    1465926569
                );
            } elseif ( ! $approvingUser->isAuthorizedToApproveKey($this)) {
                $this->addError('processed_by', sprintf(
                    'That user (%s) is not authorized to approve keys to that API.',
                    $approvingUser->getDisplayName()
                ));
                return false;
            }
            
            // At this point, we know the given $approvingUser is authorized
            // to (and needs to) approve this Key.
            $this->processed_by = $approvingUser->user_id;
        }
        $this->status = self::STATUS_APPROVED;
        $this->value = \Utils::getRandStr(32);
        $this->secret = \Utils::getRandStr(128);
        
        if ($this->save()) {
            $this->notifyUserOfApprovedKey();
            
            // Indicate success.
            return true;
        } else {
            return false;
        }
    }
    
    protected function beforeDelete()
    {
        if ( ! parent::beforeDelete()) {
            return false;
        }
        
        foreach ($this->events as $event) {
            $event->key_id = null;
            if ( ! $event->save()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this Key because we were not able to finish updating the related event '
                    . 'records: %s',
                    print_r($event->getErrors(), true)
                ));
                return false;
            }
        }
        
        global $ENABLE_AXLE;
        if (isset($ENABLE_AXLE) && !$ENABLE_AXLE) {
            return true;
        }
        
        /**
         * @todo We will probably only need to delete the key from Axle if it
         *       it was an approved key. Make sure we're deleting keys from Axle
         *       when revoked. Should we also just go ahead and re-try/confirm
         *       that the key has been deleted from Axle at this point?
         */
        if ($this->value) {
            return $this->deleteFromApiAxle();
        }
        return true;
    }
    
    /**
     * Whether the given User is allowed to delete this Key. This takes into
     * account both the user's ownership (or lack thereof) of the Key and Api
     * as well as the current status of the Key.
     * 
     * @param User $user
     * @return boolean
     */
    public function canBeDeletedBy($user)
    {
        if ( ! ($user instanceof \User)) {
            return false;
        }
        
        // Check all the normal ownership / admin details first.
        if ( ! $user->canRevokeKey($this)) {
            return false;
        }
        
        if ($this->isOwnedBy($user)) {
            
            // Allow a User to delete their own Key regardless of status.
            return true;
            
        } else {
            
            /* Only allow someone else to delete a User's Key if the Key has
             * already been "terminated" (for lack of a better word).  */
            switch ($this->status) {
                case \Key::STATUS_DENIED:
                case \Key::STATUS_REVOKED:
                    return true;

                default:
                    return false;
            }
        }
    }
    
    public static function getActiveKeysDataProvider()
    {
        return new \CActiveDataProvider('Key', array(
            'criteria' => array(
                'condition' => 'status = :status',
                'params' => array(
                    ':status' => \Key::STATUS_APPROVED,
                ),
            ),
        ));
    }
    
    public static function getPendingKeysDataProvider()
    {
        return new \CActiveDataProvider('Key', array(
            'criteria' => array(
                'condition' => 'status = :status',
                'params' => array(
                    ':status' => \Key::STATUS_PENDING,
                ),
            ),
        ));
    }
    
    /**
     * Get the list of Keys that the given User can see.
     * 
     * @param \User $user The User in question.
     * @return Key[] The list of pending Keys visible to that User.
     */
    public static function getPendingKeysVisibleTo($user)
    {
        $allPendingKeys = \Key::model()->findAllByAttributes(array(
            'status' => \Key::STATUS_PENDING,
        ));
        $pendingKeysToShow = array();
        foreach ($allPendingKeys as $pendingKey) {
            if ($user->canSeeKey($pendingKey)) {
                $pendingKeysToShow[] = $pendingKey;
            }
        }
        return $pendingKeysToShow;
    }
    
    public function getStyledStatusHtml()
    {
        $cssClass = null;
        $cssStyle = null;
        switch ($this->status) {
            case self::STATUS_APPROVED:
                $displayText = ucfirst($this->status);
                break;

            case self::STATUS_DENIED:
                $cssClass = 'text-error';
                $displayText = ucfirst($this->status);
                break;

            case self::STATUS_PENDING:
                $cssStyle = 'font-style: italic;';
                $displayText = ucfirst($this->status);
                break;

            case self::STATUS_REVOKED:
                $cssClass = 'text-error';
                $cssStyle = 'font-weight: bold;';
                $displayText = ucfirst($this->status);
                break;

            default:
                $displayText = 'UNKNOWN STATUS: ' . $this->status;
                break;
        }
        
        return sprintf(
            '<span%s%s>%s</span>',
            ($cssClass ? ' class="' . $cssClass . '"' : ''),
            ($cssStyle ? ' style="' . $cssStyle . '"' : ''),
            CHtml::encode($displayText)
        );
    }
    
    /**
     * Try to delete this key from ApiAxle, returning an indicator of whether we
     * were successful.
     * 
     * @return boolean Whether it was successfully removed from ApiAxle. If not,
     *     check the key's errors.
     */
    protected function deleteFromApiAxle()
    {
        try{
            $axleKey = new AxleKey(\Yii::app()->params['apiaxle']);
            $axleKey->delete($this->value);
            return true;
        } catch (\Exception $e) {

            // If the key was not found, consider the deletion successful.
            $notFoundMessage = sprintf(
                'API returned error: Key \'%s\' not found.',
                $this->value
            );
            if (($e->getCode() == 201) && ($notFoundMessage === $e->getMessage())) {
                return true;
            }

            // Otherwise, consider it not successful.
            $this->addError('value', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get usage data for this Key.
     * 
     * @param string $granularity The time interval (e.g. - 'second', 'minute',
     *     'hour', 'day') by which the data should be grouped.
     * @param boolean $includeCurrentInterval (Optional:) Whether to include the
     *     current time interval, even though we only have incomplete data for
     *     it. Defaults to true.
     * @return array A hash with timestamps (in $granularity intervals) as keys,
     *     and arrays of http_response_code(or error_name) => num_hits as
     *     values.  
     *     EXAMPLE: array(
     *                1416340920 => array(200 => 2),
     *                1416340980 => array(200 => 4),
     *                1416341520 => array(200 => 1),
     *              )
     */
    public function getUsage(
        $granularity = 'minute',
        $includeCurrentInterval = true
    ) {
        // Get the ApiAxle Key object for this Key model.
        $axleKey = new AxleKey(Yii::app()->params['apiaxle'], $this->value);
        
        // Get the starting timestamp for the data we care about.
        $timeStart = \UsageStats::getTimeStart(
            $granularity,
            $includeCurrentInterval
        );
        
        // Retrieve the stats from ApiAxle.
        $axleStats = $axleKey->getStats($timeStart, false, $granularity, 'false');
        
        // Reformat the data for easier use.
        $dataByCategory = array();
        foreach ($axleStats as $category => $categoryStats) {
            $tempCategoryData = array();
            foreach ($categoryStats as $responseCode => $timeData) {
                if (count($timeData) <= 0) {
                    continue;
                }
                $tempResponseCodeData = array();
                foreach ($timeData as $timestamp => $numHits) {
                    $tempResponseCodeData[$timestamp] = $numHits;
                }
                if (count($tempResponseCodeData) > 0) {
                    $tempCategoryData[$responseCode] = $tempResponseCodeData;
                }
            }
            $dataByCategory[$category] = $tempCategoryData;
        }
        
        // Sum the cached and uncached hits, then sum that with the errors.
        $successfulUsage = UsageStats::combineUsageCategoryArrays(
            $dataByCategory['uncached'],
            $dataByCategory['cached']
        );
        $usage = UsageStats::combineUsageCategoryArrays(
            $successfulUsage,
            $dataByCategory['error']
        );
        
        // Return the resulting data.
        return $usage;
    }
    
    public static function getValidStatusValues()
    {
        return array(
            self::STATUS_APPROVED,
            self::STATUS_DENIED,
            self::STATUS_PENDING,
            self::STATUS_REVOKED,
        );
    }
           
    /**
     * Indicate whether this Key belongs to the given User. Note that this is a
     * User model, not a Yii CWebUser. If no user is given, then false is
     * returned.
     * 
     * @param User $user|null The User (model) in question.
     * @return boolean Whether this Key belongs to that User.
     */
    public function isOwnedBy($user)
    {
        if (($user === null) || ($user->user_id === null)) {
            return false;
        } else {
            return ($user->user_id === $this->user_id);
        }
    }
    
    /**
     * Indicate whether this Key is to an API that belongs to the given User.
     * Note that this is a User model, not a Yii CWebUser. If no user is given,
     * then false is returned.
     * 
     * @param User $user|null The User (model) in question.
     * @return boolean Whether this Key is to an API that belongs to that User.
     */
    public function isToApiOwnedBy($user)
    {
        if (($user === null) || ($user->user_id === null)) {
            return false;
        } elseif (($this->api === null) || ($this->api->owner_id === null)) {
            return false;
        } else {
            return ($user->user_id === $this->api->owner_id);
        }
    }
    
    /**
     * Indicate whether this Key should be visible to the given User. Note that
     * this is a User model, not a Yii CWebUser.
     * 
     * @param User $user The User (model) whose permissions need to be checked.
     * @return boolean Whether the Key should be visible to that User.
     */
    public function isVisibleToUser($user)
    {
        // If the user is a guest...
        if ( ! ($user instanceof \User)) {
            
            // They can't see any Keys.
            return false;
        }
        
        // If the user is an Admin, then they can see the key.
        if ($user->role === \User::ROLE_ADMIN) {
            return true;
        }
        
        // If the user is an API Owner...
        if ($user->role === \User::ROLE_OWNER) {
            
            // They can see this key if it belongs to them or if it is to one of
            // of their APIs.
            return $this->isOwnedBy($user) || $this->isToApiOwnedBy($user);
        }
        
        // If the user is a Developer...
        if ($user->role === \User::ROLE_USER) {
            
            // They can see this key if it belongs to them.
            return $this->isOwnedBy($user);
        }
        
        // If we reach this point, we have come across a situation that we are
        // not yet set up to handle.
        throw new \Exception(
            'Unable to determine whether a User should be allowed to see a '
            . 'particular Key because we do not know how to handle a User role '
            . 'of ' . var_export($user->role, true) . '.',
            1420733488
        );
    }
    
    /**
     * Try to send a notification email to the Owner of the Api that this
     * (pending) Key is for. If no owner email address is available, send it to
     * the admins.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyApiOwnerOfPendingRequest(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params->toArray();
        }
        
        // If we are in an environment where we should send email
        // notifications...
        if ($appParams['mail'] !== false) {
            
            // Figure out what email address to send the notification to.
            $sendToEmail = null;
            if ($this->api->owner && $this->api->owner->email) {
                
                // If the API has an owner and we know their email address, use
                // that.
                $sendToEmail = $this->api->owner->email;
                
            } elseif (isset($appParams['adminEmail'])) {
                
                // Otherwise, try to notify the admins.
                $sendToEmail = $appParams['adminEmail'];
            }

            // If we have an email address to send the notification to...
            if ($sendToEmail) {
                
                // Try to send a notification email.
                if ($mailer === null) {
                    $mailer = Utils::getMailer();
                }
                $mailer->setView('key-request');
                $mailer->setTo($sendToEmail);
                $mailer->setSubject(sprintf(
                    'New key request for %s API',
                    $this->api->display_name
                ));
                if (isset($appParams['mail']['bcc'])) {
                    $mailer->setBcc($appParams['mail']['bcc']);
                }
                $mailer->setData(array(
                    'owner' => $this->api->owner,
                    'api' => $this->api,
                    'key' => $this,
                    'requestingUser' => $this->user,
                ));

                // If unable to send the email, allow the process to
                // continue but communicate the email failure somehow.
                if ( ! $mailer->send()) {
                    \Yii::log(
                        'Unable to send pending key approval request email: '
                        . $mailer->ErrorInfo,
                        CLogger::LEVEL_WARNING
                    );
                }
            }
        }
    }
    
    /**
     * Try to send a notification email to the User that requested a Key that
     * the request was denied.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyUserOfDeniedKey(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params->toArray();
        }
        
        // If we are in an environment where we should send email
        // notifications...
        if ($appParams['mail'] !== false) {
            
            // If we can successfully retrieve the requesting-user's email
            // address...
            if ($this->user && $this->user->email) {

                // Try to send them a notification email.
                if ($mailer === null) {
                    $mailer = Utils::getMailer();
                }
                $mailer->setView('key-request-denied');
                $mailer->setTo($this->user->email);
                $mailer->setSubject(sprintf(
                    'Key request for %s API was denied',
                    $this->api->display_name
                ));
                if (isset($appParams['mail']['bcc'])) {
                    $mailer->setBcc($appParams['mail']['bcc']);
                }
                $mailer->setData(array(
                    'key' => $this,
                ));

                // If unable to send the email, allow the process to
                // continue but communicate the email failure somehow.
                if ( ! $mailer->send()) {
                    \Yii::log(
                        'Unable to send key-request-denied notification email '
                        . 'to user: ' . $mailer->ErrorInfo,
                        CLogger::LEVEL_WARNING
                    );
                }
            }
        }
    }
    
    /**
     * Try to send a notification email to the Owner of an Api that a Key to
     * their Api has been revoked.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyApiOwnerOfRevokedKey(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params->toArray();
        }
        
        // If we are in an environment where we should NOT send email
        // notifications, then don't.
        if ($appParams['mail'] === false) {
            return;
        }
        
        if ($this->api->owner && $this->api->owner->email) {

            // Try to send them a notification email.
            if ($mailer === null) {
                $mailer = Utils::getMailer();
            }
            $mailer->setView('key-revoked-api-owner');
            $mailer->setTo($this->api->owner->email);
            $mailer->setSubject(sprintf(
                'Key revoked for %s API',
                $this->api->display_name
            ));
            if (isset($appParams['mail']['bcc'])) {
                $mailer->setBcc($appParams['mail']['bcc']);
            }
            $mailer->setData(array(
                'apiOwner' => $this->api->owner,
                'api' => $this->api,
                'key' => $this,
                'keyOwner' => $this->user,
            ));

            // If unable to send the email, allow the process to
            // continue but communicate the email failure somehow.
            if ( ! $mailer->send()) {
                \Yii::log(
                    'Unable to send key-revoked notification email to API '
                    . 'owner: ' . $mailer->ErrorInfo,
                    CLogger::LEVEL_WARNING
                );
            }
        }
    }
    
    /**
     * Try to send a notification email to the User that one of their pending
     * Keys has been approved.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyUserOfApprovedKey(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params->toArray();
        }
        
        // If we are in an environment where we should NOT send email
        // notifications, then don't.
        if ($appParams['mail'] === false) {
            return;
        }
        
        if ($this->user && $this->user->email) {

            // Try to send them a notification email.
            if ($mailer === null) {
                $mailer = Utils::getMailer();
            }
            $mailer->setView('key-approved');
            $mailer->setTo($this->user->email);
            $mailer->setSubject(sprintf(
                'Key approved for %s API',
                $this->api->display_name
            ));
            if (isset($appParams['mail']['bcc'])) {
                $mailer->setBcc($appParams['mail']['bcc']);
            }
            $mailer->setData(array(
                'key' => $this,
                'api' => $this->api,
                'user' => $this->user,
            ));
            
            /**
             * @todo Figure out whether we want to Cc: the API Owner on this
             *       email. I think not, to avoid exposing their email address
             *       to people without their consent.
             */
            //$cc = array();
            //if ($this->api->owner && $this->api->owner->email) {
            //    $cc[] = $this->api->owner->email;
            //}
            //$mailer->setCc($cc);

            // If unable to send the email, allow the process to
            // continue but communicate the email failure somehow.
            if ( ! $mailer->send()) {
                \Yii::log(
                    'Unable to send key-approved notification email to user: '
                    . $mailer->ErrorInfo,
                    CLogger::LEVEL_WARNING
                );
            }
        }
    }
    
    /**
     * Try to send a notification email to the User that one of their Keys has
     * been revoked.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyUserOfRevokedKey(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params->toArray();
        }
        
        // If we are in an environment where we should NOT send email
        // notifications, then don't.
        if ($appParams['mail'] === false) {
            return;
        }
        
        if ($this->user && $this->user->email) {

            // Try to send them a notification email.
            if ($mailer === null) {
                $mailer = Utils::getMailer();
            }
            $mailer->setView('key-revoked-user');
            $mailer->setTo($this->user->email);
            $mailer->setSubject(sprintf(
                'Key revoked for %s API',
                $this->api->display_name
            ));
            if (isset($appParams['mail']['bcc'])) {
                $mailer->setBcc($appParams['mail']['bcc']);
            }
            $mailer->setData(array(
                'key' => $this,
                'api' => $this->api,
                'user' => $this->user,
            ));

            // If unable to send the email, allow the process to
            // continue but communicate the email failure somehow.
            if ( ! $mailer->send()) {
                \Yii::log(
                    'Unable to send key-revoked notification email to user: '
                    . $mailer->ErrorInfo,
                    CLogger::LEVEL_WARNING
                );
            }
        }
    }
    
    /**
     * Prevent requests for new Keys that are for an Api that the requesting
     * User already has either an active key or a pending key to.
     * 
     * @param string $attribute The name of the attribute to be validated.
     */
    public function onlyAllowOneKeyPerApi($attribute)
    {
        if ($this->user->hasActiveKeyToApi($this->api)) {
            
            // Prevent the user from requesting another key to an API that they
            // already have an ACTIVE key for.
            $this->addError(
                $attribute,
                'You already have an active key to that API.'
            );
        } elseif ($this->user->hasPendingKeyForApi($this->api)) {

            // Prevent the user from requesting another key to an API that they
            // already have a PENDING key request for.
            $this->addError(
                $attribute,
                'You already have a pending key request for that API.'
            );
        }
    }
    
    public function requiresApproval()
    {
        return ($this->api->approval_type !== \Api::APPROVAL_TYPE_AUTO);
    }
    
    public static function resetKey($key_id) {
        /**
         * Updates a Key instance with a new value and secret.
         * 
         * Returns an array with two values ...
         *  - a boolean as to whether the reset worked
         *  - the Key instance or a string as an error message
         */
        
        /* @var $key \Key */
        $key = Key::model()->findByPk($key_id);  
        if (is_null($key)) { return array(false, 'Bad key_id');}     
        
        //$seed = microtime() . $key->user_id;         
        $key->value = Utils::getRandStr();//hash('md5', $seed); // length 32 
        $key->secret = Utils::getRandStr(128);//hash('sha512', $seed); // length 128
        
        /* Also re-sync the Key's rate limits in case those settings have
         * changed on the Api and this Key somehow failed to be updated.  */
        $key->queries_day = $key->api->queries_day;
        $key->queries_second = $key->api->queries_second;

        // Try to save the changes to the Key. If successful...
        if ($key->save()) {
            
            // If we are in an environment where we should send email
            // notifications...
            if (Yii::app()->params['mail'] !== FALSE) {
                
                // Send notification to owner of key that it was reset.
                $mail = Utils::getMailer();
                $mail->setView('key-reset');
                $mail->setTo($key->user->email);
                $mail->setSubject('API key reset for '.$key->api->display_name.' API');
                if (isset(Yii::app()->params['mail']['bcc'])) {
                    $mail->setBcc(Yii::app()->params['mail']['bcc']);
                }
                $mail->setData(array(
                    'key' => $key,
                    'api' => $key->api,
                ));
                $mail->send();
            }
            
            // Indicate success, returning the Key's updated data as well.
            return array(true, $key);
        }
        // Otherwise (i.e. - if saving the Key failed)...
        else {
            
            // Indicate failure, returning the error message(s).
            return array(false,print_r($key->getErrors(),true));
        }
        
    }
    
    /**
     * Attempt to revoke a Key, receiving back an indicator of whether it was
     * successful.
     * 
     * @param \User $revokingUser The User trying to revoke this Key.
     * @return boolean True if the Key was successfully revoked. If not, check
     *     the Key's list of errors to find out why.
     * @throws \Exception
     */
    public function revoke($revokingUser)
    {
        if ( ! $revokingUser instanceof \User) {
            // This should not happen in the normal flow of things... thus
            // the exception.
            throw new \Exception(
                'No User provided when trying to revoke a Key.',
                1466000163
            );
        } elseif ( ! $revokingUser->canRevokeKey($this)) {
            $this->addError('processed_by', sprintf(
                'That user (%s) is not authorized to revoke this key.',
                $revokingUser->getDisplayName()
            ));
            return false;
        }

        if ($this->status !== self::STATUS_APPROVED) {
            $this->addError('status', 'Only approved keys can be revoked.');
            return false;
        }
        
        $this->processed_by = $revokingUser->user_id;
        $this->status = self::STATUS_REVOKED;
        /* NOTE: Leave the key value intact (for identifying the revoked key,
         *       both to ApiAxle and to the end user). Do get rid of the secret,
         *       though.  */
        $this->secret = null;
        
        if ($this->save()) {
            $this->sendKeyDeletionNotification();
            
            // Indicate success.
            return true;
        } else {
            return false;
        }
    }
    
    public static function revokeKey($key_id, $revokingUser)
    {
        /**
         * Revokes a Key instance.
         * 
         * Returns an array with two values ...
         *  - a boolean as to whether the revokation worked
         *  - the Key instance or a string as an error message
         */
        /* @var $key \Key */
        $key = Key::model()->findByPk($key_id);  
        if (is_null($key)) {
            return array(false, 'Bad key_id');
        }
        
        if ($key->revoke($revokingUser)) {
            
            return array(true, null);
            
        } else {
            
            // Return the error messages.
            return array(false, print_r($key->getErrors(), true));
        }
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Key the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    /**
     * If the Key has a processed_by value but no processed_on value, use now
     * as the processed_on value.
     * 
     * @param string $attribute The name of the attribute to be validated.
     * @param array $params The options specified in the validation rule.
     */
    public function recordDateWhenProcessed($attribute, $params)
    {
        if ( ! empty($this->processed_by)) {
            if (empty($this->processed_on)) {
                $this->processed_on = new CDbExpression('NOW()');
            }
        }
    }
    
    /**
     * Try to send a notification email to the Owner of the Api that this
     * pending Key is for. If no owner email address is available, send it to
     * the admins.
     * 
     * @param YiiMailer $mailer See sendKeyDeletionNotification for details.
     * @param array $appParams See sendKeyDeletionNotification for details.
     */
    protected function sendPendingKeyDeletionNotification(
        YiiMailer $mailer,
        array $appParams
    ) {
        // Figure out what email address to send the notification to.
        $sendToEmail = null;
        if ($this->api->owner && $this->api->owner->email) {
            $sendToEmail = $this->api->owner->email;
        } elseif (isset($appParams['adminEmail'])) {
            $sendToEmail = $appParams['adminEmail'];
        }
        
        if (empty($sendToEmail)) {
            return;
        }
        
        // Try to send a notification email.
        $mailer->setView('pending-key-deleted');
        $mailer->setTo($sendToEmail);
        $mailer->setSubject(sprintf(
            'Key request (for %s API) deleted',
            $this->api->display_name
        ));
        if (isset($appParams['mail']['bcc'])) {
            $mailer->setBcc($appParams['mail']['bcc']);
        }
        $mailer->setData(array(
            'owner' => $this->api->owner,
            'api' => $this->api,
            'pendingKey' => $this,
            'requestingUser' => $this->user,
        ));

        // If unable to send the email, allow the process to
        // continue but communicate the email failure somehow.
        if ( ! $mailer->send()) {
            \Yii::log(
                'Unable to send pending-key deletion email: '
                . $mailer->ErrorInfo,
                CLogger::LEVEL_WARNING
            );
        }
    }
    
    /**
     * Try to notify the owner of this key that it has been deleted.
     * 
     * @param YiiMailer $mailer See sendKeyDeletionNotification for details.
     * @param array $appParams See sendKeyDeletionNotification for details.
     */
    protected function sendNonPendingKeyDeletionNotification(
        YiiMailer $mailer,
        array $appParams
    ) {
        if ($this->user && $this->user->email) {

            // Send notification to owner of key that it was revoked.
            $mailer->setView('key-deleted');
            $mailer->setTo($this->user->email);
            $mailer->setSubject(sprintf(
                'API key deleted for %s API',
                $this->api->display_name
            ));
            if (isset($appParams['mail']['bcc'])) {
                $mailer->setBcc($appParams['mail']['bcc']);
            }
            $mailer->setData(array(
                'key' => $this,
                'api' => $this->api,
            ));
            
            // If unable to send the email, allow the process to
            // continue but communicate the email failure somehow.
            if ( ! $mailer->send()) {
                \Yii::log(
                    'Unable to send key deletion email: '
                    . $mailer->ErrorInfo,
                    CLogger::LEVEL_WARNING
                );
            }
        }
    }
    
    /**
     * Try to send a notification email to the appropriate person about this
     * Key having been deleted.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function sendKeyDeletionNotification(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params->toArray();
        }
        
        // If we are in an environment where we should NOT send email
        // notifications, then don't.
        if ($appParams['mail'] === false) {
            return;
        }
        
        if ($mailer === null) {
            $mailer = Utils::getMailer();
        }
        
        if ($this->status === self::STATUS_PENDING) {
            $this->sendPendingKeyDeletionNotification(
                $mailer,
                $appParams
            );
        } else {
            $this->sendNonPendingKeyDeletionNotification(
                $mailer,
                $appParams
            );
        }
    }
}
