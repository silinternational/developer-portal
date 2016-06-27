<?php

class Event extends EventBase
{
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        // Overwrite any attribute labels that need manual tweaking.
        return \CMap::mergeArray(parent::attributeLabels(), array(
            'api_id' => 'API',
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Event the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array(
                /* Always automatically calculate the 'created' value for new
                 * Events, rather than letting the user specify it.  */
                'created',
                'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false, // setOnEmpty means "only set when empty"
                'on' => 'insert',
            ),
        ), parent::rules());
    }
}
