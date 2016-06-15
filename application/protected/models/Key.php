<?php

use ApiAxle\Api\Api as AxleApi;
use ApiAxle\Api\Key as AxleKey;
use ApiAxle\Api\Keyring as AxleKeyring;

class Key extends KeyBase
{
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_PENDING = 'pending';
    const STATUS_REVOKED = 'revoked';
    
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
                'setOnEmpty' => true,
                'on' => 'insert',
            ),
            array('processed_on', 'recordDateWhenProcessed'),
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

            if ($this->getIsNewRecord()) {
                try {
                    /**
                     * Create new Key in apiaxle
                     */
                    $axleKey->create($this->value,$keyData);
                    /**
                     * Link key to keyring
                     */
                    $axleKeyring->linkKey($axleKey);
                    /**
                     * Link key to Api
                     */
                    $api = Api::model()->findByPk($this->api_id);
                    $axleApi = new AxleApi(Yii::app()->params['apiaxle'],$api->code);
                    $axleApi->linkKey($axleKey);
                    return true;
                } catch (\Exception $e) {
                    $this->addError('value',$e->getMessage());
                    return false;
                }
            } else {
                try{
                    /**
                     * Get current key to check for key value change
                     */
                    $current = Key::model()->findByPk($this->key_id);
                    if($current->value != $this->value){
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
            }
        } elseif ($this->status === \Key::STATUS_DENIED) {
            
            /**
             * @todo Figure out what to do in ApiAxle when a Key in our database
             *       is denied.
             */
            
            // Make sure the key does not still exist in ApiAxle.
            return $this->deleteFromApiAxle();
            
        } elseif ($this->status === \Key::STATUS_PENDING) {
            
            /**
             * @todo Figure out what to do in ApiAxle (if anything) when a Key
             *       in our database is pending.
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

//      // If we know the key request for this key...
//      if ($this->key_request_id !== null) {
//
//        // Delete the related key request as well.
//        $delKeyRequests = KeyRequest::model()->deleteByPk(
//            $this->key_request_id
//        );
//      }
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
            
            // If we are in an environment where we should send email
            // notifications...
            if (\Yii::app()->params['smtp'] !== false) {
            
                // If possible, include the API owner as Cc: on the email.
                $cc = array();
                if ($this->api->owner && $this->api->owner->email) {
                    $cc[] = $this->api->owner->email;
                }
                
                // Send an email notification.
                $mail = \Utils::getMailer();
                $mail->setView('key-created');
                $mail->setTo($this->user->email);
                $mail->setCc($cc);
                $mail->setSubject(sprintf(
                    'API key created for %s API',
                    $this->api->display_name
                ));
                if (isset(\Yii::app()->params['mail']['bcc'])) {
                    $mail->setBcc(\Yii::app()->params['mail']['bcc']);
                }
                $mail->setData(array(
                    'key' => $this,
                    'api' => $this->api,
                ));
                $mail->send();
            }
            
            // Indicate success.
            return true;
        } else {
            return false;
        }
    }
    
    public function beforeDelete()
    {
        parent::beforeDelete();
        
        global $ENABLE_AXLE;
        if(isset($ENABLE_AXLE) && !$ENABLE_AXLE){
            return true;
        }
        
        /**
         * @todo We will probably only need to delete the key from Axle if it
         *       it was an approved key. Make sure we're deleting keys from Axle
         *       when denied/revoked. Should we also just go ahead and re-try/confirm
         *       that the key has been deleted from Axle at this point?
         */
        
        return $this->deleteFromApiAxle();
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
        
        $key = Key::model()->findByPk($key_id);  
        if (is_null($key)) { return array(false, 'Bad key_id');}     
        
        //$seed = microtime() . $key->user_id;         
        $key->value = Utils::getRandStr();//hash('md5', $seed); // length 32 
        $key->secret = Utils::getRandStr(128);//hash('sha512', $seed); // length 128

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
            
            // If we are in an environment where we should send email
            // notifications...
            if (\Yii::app()->params['smtp'] !== false) {
                
                // Send notification to owner of key that it was revoked.
                $mail = Utils::getMailer();
                $mail->setView('key-deleted');
                $mail->setTo($key->user->email);
                $mail->setSubject('API key deleted for '.$key->api->display_name.' API');
                if (isset(Yii::app()->params['mail']['bcc'])) {
                    $mail->setBcc(Yii::app()->params['mail']['bcc']);
                }
                $mail->setData(array(
                    'key' => $key,
                    'api' => $key->api,
                ));
                $mail->send();
            }
            
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
}
