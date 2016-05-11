<?php

class Faq extends FaqBase
{
    public function rules() {
        $rules = parent::rules();
        $newRules = array_merge($rules, array(
            array('updated', 'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false, 'on' => 'update'),
            array('created,updated', 'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => true, 'on' => 'insert'),
            array('question, answer', 'safe', 'on' => 'search'),
        ));
        
        return $newRules;
    }
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;

        $criteria->compare('question', $this->question, true);
        $criteria->compare('answer', $this->answer, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Faq the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
}