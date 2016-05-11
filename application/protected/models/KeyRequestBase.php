<?php

/**
 * This is the model class for table "key_request".
 *
 * The followings are the available columns in table 'key_request':
 * @property integer $key_request_id
 * @property integer $user_id
 * @property integer $api_id
 * @property string $status
 * @property string $created
 * @property string $updated
 * @property integer $processed_by
 * @property string $purpose
 * @property string $domain
 *
 * The followings are the available model relations:
 * @property Key[] $keys
 * @property User $processedBy
 * @property Api $api
 * @property User $user
 */
class KeyRequestBase extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'key_request';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, api_id, status, purpose, domain', 'required'),
			array('user_id, api_id, processed_by', 'numerical', 'integerOnly'=>true),
			array('status', 'length', 'max'=>32),
			array('purpose, domain', 'length', 'max'=>255),
			array('created, updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('key_request_id, user_id, api_id, status, created, updated, processed_by, purpose, domain', 'safe', 'on'=>'search'),
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
			'keys' => array(self::HAS_MANY, 'Key', 'key_request_id'),
			'processedBy' => array(self::BELONGS_TO, 'User', 'processed_by'),
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
			'key_request_id' => 'Key Request',
			'user_id' => 'User',
			'api_id' => 'Api',
			'status' => 'Status',
			'created' => 'Created',
			'updated' => 'Updated',
			'processed_by' => 'Processed By',
			'purpose' => 'Purpose',
			'domain' => 'Domain',
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

		$criteria->compare('key_request_id',$this->key_request_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('api_id',$this->api_id);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('updated',$this->updated,true);
		$criteria->compare('processed_by',$this->processed_by);
		$criteria->compare('purpose',$this->purpose,true);
		$criteria->compare('domain',$this->domain,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return KeyRequestBase the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
