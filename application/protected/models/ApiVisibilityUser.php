<?php

class ApiVisibilityUser extends ApiVisibilityUserBase
{
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
