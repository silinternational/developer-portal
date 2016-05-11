<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $user_id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $display_name
 * @property integer $status
 * @property string $created
 * @property string $updated
 * @property string $role
 * @property string $auth_provider
 * @property string $auth_provider_user_identifier
 *
 * The followings are the available model relations:
 * @property Api[] $apis
 * @property Key[] $keys
 * @property KeyRequest[] $keyRequests
 * @property KeyRequest[] $keyRequests1
 */
class UserBase extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, first_name, last_name, status, role, auth_provider', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('email', 'length', 'max'=>128),
			array('first_name, last_name, auth_provider', 'length', 'max'=>32),
			array('display_name', 'length', 'max'=>64),
			array('role', 'length', 'max'=>16),
			array('auth_provider_user_identifier', 'length', 'max'=>255),
			array('created, updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('user_id, email, first_name, last_name, display_name, status, created, updated, role, auth_provider, auth_provider_user_identifier', 'safe', 'on'=>'search'),
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
			'apis' => array(self::HAS_MANY, 'Api', 'owner_id'),
			'keys' => array(self::HAS_MANY, 'Key', 'user_id'),
			'keyRequests' => array(self::HAS_MANY, 'KeyRequest', 'processed_by'),
			'keyRequests1' => array(self::HAS_MANY, 'KeyRequest', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'email' => 'Email',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'display_name' => 'Display Name',
			'status' => 'Status',
			'created' => 'Created',
			'updated' => 'Updated',
			'role' => 'Role',
			'auth_provider' => 'Auth Provider',
			'auth_provider_user_identifier' => 'Auth Provider User Identifier',
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('display_name',$this->display_name,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('updated',$this->updated,true);
		$criteria->compare('role',$this->role,true);
		$criteria->compare('auth_provider',$this->auth_provider,true);
		$criteria->compare('auth_provider_user_identifier',$this->auth_provider_user_identifier,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserBase the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
