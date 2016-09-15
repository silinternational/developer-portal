<?php
namespace Sil\DevPortal\models;

class ApiVisibilityUser extends \ApiVisibilityUserBase
{
    use \Sil\DevPortal\components\DependentKeysTrait;
    use \Sil\DevPortal\components\FixRelationsClassPathsTrait;
    use \Sil\DevPortal\components\FormatModelErrorsTrait;
    use \Sil\DevPortal\components\ModelFindByPkTrait;
    
    protected function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        Event::log(sprintf(
            'The ability for %s (User ID %s) to see the "%s" API (api_id %s) was deleted%s.',
            (isset($this->invitedUser) ? $this->invitedUser->getDisplayName() : 'a User'),
            $this->invited_user_id,
            (isset($this->api) ? $this->api->display_name : ''),
            $this->api_id,
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ), $this->api_id, null, $this->invited_user_id);
    }
    
    protected function afterSave()
    {
        parent::afterSave();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        
        Event::log(sprintf(
            'The ability for %s%s to see the "%s" API (api_id %s) was %s%s.',
            (isset($this->invited_user_id) ? $this->invitedUser->getDisplayName() : $this->invited_user_email),
            (isset($this->invited_user_id) ? ' (User ID ' . $this->invited_user_id . ')' : ''),
            (isset($this->api) ? $this->api->display_name : ''),
            $this->api_id,
            ($this->isNewRecord ? 'added' : 'updated'),
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ), $this->api_id, null, $this->invited_user_id);
        
        if ($this->isNewRecord) {
            $this->notifyInvitee();
        }
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        // Overwrite any attribute labels that need manual tweaking.
        return \CMap::mergeArray(parent::attributeLabels(), array(
            'api_visibility_user_id' => 'API Visibility User',
            'api_id' => 'API',
            'invited_user_email' => 'Email address',
        ));
    }
    
    protected function beforeSave()
    {
        $this->replaceEmailWithUserIdIfPossible();
        return parent::beforeSave();
    }
    
    /**
     * Get the list of Keys (active or pending) where the owner of the Key can
     * only see that Api because of this invitation.
     *
     * @return Key[] The list of keys.
     */
    public function getDependentKeys()
    {
        $api = $this->api;
        $user = $this->invitedUser;
        
        if (($api !== null) && $api->isPubliclyVisible()) {
            return array();
        }
        
        if ($user !== null) {
            if ($user->isAdmin() || $user->isOwnerOfApi($api)) {
                return array();
            }

            if ($user->isInvitedByDomainToSeeApi($api)) {
                return array();
            }
        }
        
        $keysOfThisUserToThisApi = Key::model()->findAllByAttributes(array(
            'api_id' => $this->api_id,
            'user_id' => $this->invited_user_id,
        ));
        
        $dependentKeys = array();
        foreach ($keysOfThisUserToThisApi as $key) {
            if ($key->isActiveOrPending()) {
                $dependentKeys[] = $key;
            }
        }
        
        return $dependentKeys;
    }
    
    /**
     * Get some text to display to identify the invited person. It will return
     * an email address and, if known, a name.
     *
     * @return string The text to display to identify the invited person.
     */
    public function getInviteeDisplayText()
    {
        $emailAddress = $this->getInviteeEmailAddress();
        if ($this->invitedUser !== null) {
            return sprintf(
                '%s (%s)',
                $emailAddress,
                $this->invitedUser->getDisplayName()
            );
        } else {
            return $emailAddress;
        }
    }
    
    /**
     * Get an email address for the person invited. It may come from the
     * invited_user_email field or, if we already have a user record for someone
     * with that email address, from the invitedUser->email relationship.
     * 
     * @return string|null The email address (if available), otherwise null.
     */
    public function getInviteeEmailAddress()
    {
        if ( ! empty($this->invited_user_email)) {
            return $this->invited_user_email;
        } elseif ( ! is_null($this->invitedUser)) {
            return $this->invitedUser->email;
        }
        return null;
    }
    
    public function replaceEmailWithUserIdIfPossible()
    {
        if ( ! empty($this->invited_user_email)) {
            
            /* @var $invitedUser User */
            $invitedUser = User::model()->findByAttributes(array(
                'email' => $this->invited_user_email,
            ));
            
            if ($invitedUser !== null) {
                $this->invited_user_id = $invitedUser->user_id;
                $this->invited_user_email = null;
            }
        }
    }
    
    /**
     * Validate that exactly one or the other of the named attributes has a
     * non-null value (but NOT both, and NOT neither).
     * 
     * @param string $attribute The name of the attribute to be validated.
     * @param array $params The options specified in the validation rule.
     */
    public function hasOneOrTheOther($attribute, $params)
    {
        if (empty($params['otherAttribute'])) {
            throw new \Exception(
                'You must specify the otherAttribute name to use the hasBothOrNeither validator.',
                1468439019
            );
        } elseif ( ! $this->hasAttribute($params['otherAttribute'])) {
            throw new \Exception(
                'The hasOneOrTheOther validator was given an otherAttribute of '
                . '"%s", but there is no such attribute.',
                1468439020
            );
        }
        
        $otherAttribute = $params['otherAttribute'];
        
        $attributeLabel = $this->getAttributeLabel($attribute);
        $otherAttributeLabel = $this->getAttributeLabel($otherAttribute);
        
        $hasAttributeValue = ( ! empty($this->$attribute));
        $hasOtherAttributeValue = ( ! empty($this->$otherAttribute));
        
        if ($hasAttributeValue && $hasOtherAttributeValue) {
            $this->addError($otherAttribute, sprintf(
                'Since you provided a %s, you must not also provide an %s.',
                $attributeLabel,
                $otherAttributeLabel
            ));
        } elseif (( ! $hasOtherAttributeValue) && ( ! $hasAttributeValue)) {
            $this->addError($attribute, sprintf(
                'You must provider either a %s or an %s.',
                $otherAttributeLabel,
                $attributeLabel
            ));
        }
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ApiVisibilityUser the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * Try to send a notification email to the person invited to see this Api.
     *
     * @param \YiiMailer|null $mailer (Optional:) The YiiMailer instance for
     *     sending the email. Unless performing tests, it is best leave this out
     *     so that our normal process for creating this will be followed.
     * @param array|null $appParams (Optional:) The Yii app's params. If not
     *     provided, they will be retrieved. This parameter is primarily to make
     *     testing easier.
     */
    public function notifyInvitee(
        \YiiMailer $mailer = null,
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
        
        $inviteeEmailAddress = $this->getInviteeEmailAddress();
        if ( ! empty($inviteeEmailAddress)) {

            // Try to send them a notification email.
            if ($mailer === null) {
                $mailer = \Utils::getMailer();
            }
            $mailer->setView('api-invited-user');
            $mailer->setTo($inviteeEmailAddress);
            $mailer->setSubject(sprintf(
                'Invitation to see the "%s" API',
                $this->api->display_name
            ));
            if (isset($appParams['mail']['bcc'])) {
                $mailer->setBcc($appParams['mail']['bcc']);
            }
            $mailer->setData(array(
                'api' => $this->api,
                'apiVisibilityUser' => $this,
                'inviteeEmailAddress' => $inviteeEmailAddress,
                'invitedByUser' => $this->invitedByUser,
            ));
            
            // If unable to send the email, allow the process to
            // continue but communicate the email failure somehow.
            if ( ! $mailer->send()) {
                \Yii::log(
                    'Unable to send api-invited-user notification email to user: '
                    . $mailer->ErrorInfo,
                    \CLogger::LEVEL_WARNING
                );
            }
        }
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array(
                'invited_user_email',
                'email',
                'allowEmpty' => false,
                'on' => 'insert',
            ),
            array(
                'invited_user_id',
                'hasOneOrTheOther',
                'otherAttribute' => 'invited_user_email',
            ),
            array(
                'updated',
                'default',
                'value' => new \CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'update',
            ),
            array(
                'created, updated',
                'default',
                'value' => new \CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'insert',
            ),
        ), parent::rules());
    }
}
