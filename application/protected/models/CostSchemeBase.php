<?php

/**
 * This is the model class for table "cost_scheme".
 *
 * The followings are the available columns in table 'cost_scheme':
 * @property integer $cost_scheme_id
 * @property string $yearly_commercial_price
 * @property string $yearly_commercial_plan_code
 * @property string $yearly_nonprofit_price
 * @property string $yearly_nonprofit_plan_code
 * @property string $monthly_commercial_price
 * @property string $monthly_commercial_plan_code
 * @property string $monthly_nonprofit_price
 * @property string $monthly_nonprofit_plan_code
 * @property string $currency
 * @property string $accounting_info
 * @property string $created
 * @property string $updated
 *
 * The followings are the available model relations:
 * @property Api[] $apis
 */
class CostSchemeBase extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'cost_scheme';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created, updated', 'required'),
            array('yearly_commercial_price, yearly_nonprofit_price, monthly_commercial_price, monthly_nonprofit_price', 'length', 'max'=>19),
            array('yearly_commercial_plan_code, yearly_nonprofit_plan_code, monthly_commercial_plan_code, monthly_nonprofit_plan_code, accounting_info', 'length', 'max'=>255),
            array('currency', 'length', 'max'=>3),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('cost_scheme_id, yearly_commercial_price, yearly_commercial_plan_code, yearly_nonprofit_price, yearly_nonprofit_plan_code, monthly_commercial_price, monthly_commercial_plan_code, monthly_nonprofit_price, monthly_nonprofit_plan_code, currency, accounting_info, created, updated', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'apis' => array(self::HAS_MANY, 'Api', 'cost_scheme_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'cost_scheme_id' => 'Cost Scheme',
            'yearly_commercial_price' => 'Yearly Commercial Price',
            'yearly_commercial_plan_code' => 'Yearly Commercial Plan Code',
            'yearly_nonprofit_price' => 'Yearly Nonprofit Price',
            'yearly_nonprofit_plan_code' => 'Yearly Nonprofit Plan Code',
            'monthly_commercial_price' => 'Monthly Commercial Price',
            'monthly_commercial_plan_code' => 'Monthly Commercial Plan Code',
            'monthly_nonprofit_price' => 'Monthly Nonprofit Price',
            'monthly_nonprofit_plan_code' => 'Monthly Nonprofit Plan Code',
            'currency' => 'Currency',
            'accounting_info' => 'Accounting Info',
            'created' => 'Created',
            'updated' => 'Updated',
        );
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
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('cost_scheme_id',$this->cost_scheme_id);
        $criteria->compare('yearly_commercial_price',$this->yearly_commercial_price,true);
        $criteria->compare('yearly_commercial_plan_code',$this->yearly_commercial_plan_code,true);
        $criteria->compare('yearly_nonprofit_price',$this->yearly_nonprofit_price,true);
        $criteria->compare('yearly_nonprofit_plan_code',$this->yearly_nonprofit_plan_code,true);
        $criteria->compare('monthly_commercial_price',$this->monthly_commercial_price,true);
        $criteria->compare('monthly_commercial_plan_code',$this->monthly_commercial_plan_code,true);
        $criteria->compare('monthly_nonprofit_price',$this->monthly_nonprofit_price,true);
        $criteria->compare('monthly_nonprofit_plan_code',$this->monthly_nonprofit_plan_code,true);
        $criteria->compare('currency',$this->currency,true);
        $criteria->compare('accounting_info',$this->accounting_info,true);
        $criteria->compare('created',$this->created,true);
        $criteria->compare('updated',$this->updated,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CostSchemeBase the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
