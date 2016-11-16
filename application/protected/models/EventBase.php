<?php

/**
 * This is the model class for table "event".
 *
 * The followings are the available columns in table 'event':
 * @property integer $event_id
 * @property integer $api_id
 * @property integer $key_id
 * @property integer $acting_user_id
 * @property string $description
 * @property string $created
 * @property integer $affected_user_id
 *
 * The followings are the available model relations:
 * @property Api $api
 * @property Key $key
 * @property User $actingUser
 * @property User $affectedUser
 */
class EventBase extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'event';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('description, created', 'required'),
			array('api_id, key_id, acting_user_id, affected_user_id', 'numerical', 'integerOnly'=>true),
			array('description', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('event_id, api_id, key_id, acting_user_id, description, created, affected_user_id', 'safe', 'on'=>'search'),
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
			'key' => array(self::BELONGS_TO, 'Key', 'key_id'),
			'actingUser' => array(self::BELONGS_TO, 'User', 'acting_user_id'),
			'affectedUser' => array(self::BELONGS_TO, 'User', 'affected_user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'event_id' => 'Event',
			'api_id' => 'Api',
			'key_id' => 'Key',
			'acting_user_id' => 'Acting User',
			'description' => 'Description',
			'created' => 'Created',
			'affected_user_id' => 'Affected User',
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

		$criteria->compare('event_id',$this->event_id);
		$criteria->compare('api_id',$this->api_id);
		$criteria->compare('key_id',$this->key_id);
		$criteria->compare('acting_user_id',$this->acting_user_id);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('affected_user_id',$this->affected_user_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EventBase the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
