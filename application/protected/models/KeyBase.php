<?php

/**
 * This is the model class for table "key".
 *
 * The followings are the available columns in table 'key':
 * @property integer $key_id
 * @property string $value
 * @property string $secret
 * @property integer $user_id
 * @property integer $api_id
 * @property integer $queries_second
 * @property integer $queries_day
 * @property string $created
 * @property string $updated
 * @property integer $key_request_id
 *
 * The followings are the available model relations:
 * @property KeyRequest $keyRequest
 * @property Api $api
 * @property User $user
 */
class KeyBase extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'key';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('value, secret, user_id, api_id, queries_second, queries_day', 'required'),
			array('user_id, api_id, queries_second, queries_day, key_request_id', 'numerical', 'integerOnly'=>true),
			array('value', 'length', 'max'=>32),
			array('secret', 'length', 'max'=>128),
			array('created, updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('key_id, value, secret, user_id, api_id, queries_second, queries_day, created, updated, key_request_id', 'safe', 'on'=>'search'),
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
			'keyRequest' => array(self::BELONGS_TO, 'KeyRequest', 'key_request_id'),
			'api' => array(self::BELONGS_TO, 'Api', 'api_id'),
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'key_id' => 'Key',
			'value' => 'Value',
			'secret' => 'Secret',
			'user_id' => 'User',
			'api_id' => 'Api',
			'queries_second' => 'Queries Second',
			'queries_day' => 'Queries Day',
			'created' => 'Created',
			'updated' => 'Updated',
			'key_request_id' => 'Key Request',
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

		$criteria->compare('key_id',$this->key_id);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('secret',$this->secret,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('api_id',$this->api_id);
		$criteria->compare('queries_second',$this->queries_second);
		$criteria->compare('queries_day',$this->queries_day);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('updated',$this->updated,true);
		$criteria->compare('key_request_id',$this->key_request_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return KeyBase the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
