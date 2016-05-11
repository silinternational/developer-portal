<?php

class KeyRequest extends KeyRequestBase
{
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_PENDING = 'pending';
    const STATUS_REVOKED = 'revoked';
    
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
	 * @return array relational rules.
	 */
	public function relations()
	{
        // Get the relations defined by the base (aka. parent) class.
        $baseClassRelations = parent::relations();
        
        // Remove the 'keys' relation (because there will/should never be
        // multiple keys).
        unset($baseClassRelations['keys']);
        
        // Add a 'key' relation (because there will/should only be one).
        $baseClassRelations['key'] = array(
            self::HAS_ONE, 'Key', 'key_request_id'
        );
        
        // Return the resulting relations definitions.
        return $baseClassRelations;
	}

    public function rules() {
        $rules = parent::rules();
        $newRules = array_merge($rules, array(
            array('updated', 'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false, 'on' => 'update'),
            array('created,updated', 'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false, 'on' => 'insert'),
            array('status', 'in',
                'range' => self::getValidStatusValues(),
                'message' => 'That is not a valid status value.'),
            array('api_id', 'onlyAllowOneKeyPerApi',
                'on' => 'insert'),
        ));
        
        return $newRules;
    }
    
    public function afterSave()
    {
        // Run the parent class's version of this method.
        parent::afterSave();
        
        if ($this->status === self::STATUS_PENDING) {
            
            // If this KeyRequest is now pending approval, tell the Owner of the
            // API (if known/possible).
            $this->notifyApiOwnerOfPendingRequest();
            
        } elseif ($this->status === self::STATUS_DENIED) {
            
            // OR, if this KeyRequest was denied, tell the user that was
            // requesting the key.
            $this->notifyUserOfDeniedKeyRequest();
            
        }
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
     * Indicate whether this KeyRequest is for an API that belongs to the given
     * User. Note that this is a User model, not a Yii CWebUser. If no user is
     * given, then false is returned.
     * 
     * @param User $user|null The User (model) in question.
     * @return boolean Whether this KeyRequest is for an API that belongs to
     *     that User.
     */
    public function isForApiOwnedBy($user)
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
     * Indicate whether this KeyRequest belongs to the given User. Note that
     * this is a User model, not a Yii CWebUser. If no user is given, then false
     * is returned.
     * 
     * @param User $user|null The User (model) in question.
     * @return boolean Whether this KeyRequest belongs to that User.
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
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return KeyRequest the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    /**
     * Try to send a notification email to the Owner of the Api that this
     * KeyRequest is for. If no owner email address is available, send it to the
     * admins.
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
            $appParams = \Yii::app()->params;
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
                
            } elseif (isset(\Yii::app()->params['adminEmail'])) {
                
                // Otherwise, try to notify the admins.
                $sendToEmail = \Yii::app()->params['adminEmail'];
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
                    'keyRequest' => $this,
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
     * the KeyRequest was denied.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyUserOfDeniedKeyRequest(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params;
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
                    'keyRequest' => $this,
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
     * Prevent new KeyRequests that are for an Api that the requesting User
     * already has either an active key or a pending key request to.
     * 
     * @param string $attribute The name of the attribute to be validated.
     * @param array $params Options specified in the validation rule.
     */
    public function onlyAllowOneKeyPerApi($attribute, $params)
    {
        // Prevent the user from requesting another key to an API that they
        // already have an ACTIVE key for.
        if ($this->user->hasActiveKeyToApi($this->api)) {
            $this->addError(
                $attribute,
                'You already have an active key to that API.'
            );
        } else {

            // Prevent the user from requesting another key to an API that they
            // already have a PENDING key request for.
            if ($this->user->hasPendingKeyRequestForApi($this->api)) {
                $this->addError(
                    $attribute,
                    'You already have a pending key request for that API.'
                );
            }
        }
    }
    
    /**
     * Try to send a notification email to the Owner of the Api that this
     * KeyRequest is for. If no owner email address is available, send it to the
     * admins.
     * 
     * @param YiiMailer $mailer (Optional:) The YiiMailer instance for sending
     *     the email. Unless performing tests, it is best leave this out so that
     *     our normal process for creating this will be followed.
     * @param array $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function sendKeyRequestDeletionNotification(
        YiiMailer $mailer = null,
        array $appParams = null
    ) {
        // If not given the Yii app params, retrieve them.
        if ($appParams === null) {
            $appParams = \Yii::app()->params;
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
                $mailer->setView('key-request-deleted');
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
                    'keyRequest' => $this,
                    'requestingUser' => $this->user,
                ));

                // If unable to send the email, allow the process to
                // continue but communicate the email failure somehow.
                if ( ! $mailer->send()) {
                    \Yii::log(
                        'Unable to send key request deletion email: '
                        . $mailer->ErrorInfo,
                        CLogger::LEVEL_WARNING
                    );
                }
            }
        }
    }
    
}
