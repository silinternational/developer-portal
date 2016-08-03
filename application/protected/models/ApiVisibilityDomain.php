<?php

class ApiVisibilityDomain extends ApiVisibilityDomainBase
{
    use Sil\DevPortal\components\DependentKeysTrait;
    use Sil\DevPortal\components\FormatModelErrorsTrait;
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    public function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        \Event::log(sprintf(
            'The ability for "%s" Users to see the "%s" API (api_id %s) was deleted%s.',
            $this->domain,
            (isset($this->api) ? $this->api->display_name : ''),
            $this->api_id,
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ), $this->api_id);
    }
    
    public function afterSave()
    {
        parent::afterSave();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        
        \Event::log(sprintf(
            'The ability for "%s" Users to see the "%s" API (api_id %s) was %s%s.',
            $this->domain,
            (isset($this->api) ? $this->api->display_name : ''),
            $this->api_id,
            ($this->isNewRecord ? 'added' : 'updated'),
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ), $this->api_id);
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        // Overwrite any attribute labels that need manual tweaking.
        return \CMap::mergeArray(parent::attributeLabels(), array(
            'api_visibility_domain_id' => 'API Visibility Domain',
            'api_id' => 'API',
        ));
    }
    
    /**
     * Get the list of Keys (active or pending) where the owner of the Key can
     * only see that Api because of this invitation.
     *
     * @return \Key The list of keys.
     */
    public function getDependentKeys()
    {
        $api = $this->api;
        
        if ($api->isPubliclyVisible()) {
            return array();
        }
        
        $dependentKeys = array();
        foreach ($api->keys as $keyToApi) {
            
            if ( ! $keyToApi->isActiveOrPending()) {
                continue;
            }
            
            $user = $keyToApi->user;
            if ($user === null) {
                continue;
            }
            
            if ($user->isAdmin() || $user->isOwnerOfApi($api)) {
                continue;
            }
            
            if ($user->isIndividuallyInvitedToSeeApi($api)) {
                continue;
            }
            
            $isAllowedIncludingThisAvd = $user->isInvitedByDomainToSeeApi($api);
            $isAllowedExcludingThisAvd = $user->isInvitedByDomainToSeeApi(
                $api,
                $this->api_visibility_domain_id
            );
            if ($isAllowedIncludingThisAvd && ( ! $isAllowedExcludingThisAvd)) {
                $dependentKeys[] = $keyToApi;
            }
        }
        
        return $dependentKeys;
    }
    
    /**
     * Validate that the given domain name appears to be a valid domain name.
     * 
     * @param string $attribute The name of the attribute to be validated.
     */
    public function isApparentlyValidDomain($attribute)
    {
        $fakeEmail = 'test@' . $this->$attribute;
        if (filter_var($fakeEmail, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($attribute, sprintf(
                'The given domain name (%s) does not appear to be valid.',
                $this->$attribute
            ));
        }
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ApiVisibilityDomain the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array(
                'domain',
                'isApparentlyValidDomain',
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
