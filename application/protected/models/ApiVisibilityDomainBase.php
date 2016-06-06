<?php

/**
 * This is the model class for table "api_visibility_domain".
 *
 * The followings are the available columns in table 'api_visibility_domain':
 * @property integer $api_visibility_domain_id
 * @property integer $api_id
 * @property string $domain
 * @property integer $invited_by_user_id
 * @property string $created
 * @property string $updated
 *
 * The followings are the available model relations:
 * @property Api $api
 * @property User $invitedByUser
 */
class ApiVisibilityDomainBase extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'api_visibility_domain';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('api_id, domain, invited_by_user_id, created, updated', 'required'),
			array('api_id, invited_by_user_id', 'numerical', 'integerOnly'=>true),
			array('domain', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('api_visibility_domain_id, api_id, domain, invited_by_user_id, created, updated', 'safe', 'on'=>'search'),
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
			'api' => array(self::BELONGS_TO, 'Api', 'api_id'),
			'invitedByUser' => array(self::BELONGS_TO, 'User', 'invited_by_user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'api_visibility_domain_id' => 'Api Visibility Domain',
			'api_id' => 'Api',
			'domain' => 'Domain',
			'invited_by_user_id' => 'Invited By User',
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

		$criteria->compare('api_visibility_domain_id',$this->api_visibility_domain_id);
		$criteria->compare('api_id',$this->api_id);
		$criteria->compare('domain',$this->domain,true);
		$criteria->compare('invited_by_user_id',$this->invited_by_user_id);
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
	 * @return ApiVisibilityDomainBase the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
