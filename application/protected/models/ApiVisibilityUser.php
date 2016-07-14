<?php

class ApiVisibilityUser extends ApiVisibilityUserBase
{
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    protected function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        \Event::log(sprintf(
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
        
        \Event::log(sprintf(
            'The ability for %s%s to see the "%s" API (api_id %s) was %s%s.',
            (isset($this->invited_user_id) ? $this->invitedUser->getDisplayName() : $this->invited_user_email),
            (isset($this->invited_user_id) ? ' (User ID ' . $this->invited_user_id . ')' : ''),
            (isset($this->api) ? $this->api->display_name : ''),
            $this->api_id,
            ($this->isNewRecord ? 'added' : 'updated'),
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ), $this->api_id, null, $this->invited_user_id);
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
    
    public function replaceEmailWithUserIdIfPossible()
    {
        if ( ! empty($this->invited_user_email)) {
            
            /* @var $invitedUser \User */
            $invitedUser = \User::model()->findByAttributes(array(
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
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'update',
            ),
            array(
                'created, updated',
                'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'insert',
            ),
        ), parent::rules());
    }
}
