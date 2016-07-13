<?php

class ApiVisibilityUser extends ApiVisibilityUserBase
{
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    public function afterDelete()
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
    
    public function afterSave()
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
        ));
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
}
