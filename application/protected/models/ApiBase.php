<?php

/**
 * This is the model class for table "api".
 *
 * The followings are the available columns in table 'api':
 * @property integer $api_id
 * @property string $code
 * @property string $display_name
 * @property string $brief_description
 * @property string $endpoint
 * @property integer $queries_second
 * @property integer $queries_day
 * @property string $access_type
 * @property string $access_options
 * @property string $documentation
 * @property string $created
 * @property string $updated
 * @property string $approval_type
 * @property string $protocol
 * @property integer $strict_ssl
 * @property integer $endpoint_timeout
 * @property string $default_path
 * @property integer $owner_id
 * @property string $support
 *
 * The followings are the available model relations:
 * @property User $owner
 * @property Key[] $keys
 * @property KeyRequest[] $keyRequests
 */
class ApiBase extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'api';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('code, display_name, endpoint, queries_second, queries_day, access_type, approval_type, endpoint_timeout', 'required'),
			array('queries_second, queries_day, strict_ssl, endpoint_timeout, owner_id', 'numerical', 'integerOnly'=>true),
			array('code, access_type', 'length', 'max'=>32),
			array('display_name', 'length', 'max'=>64),
			array('brief_description, endpoint, access_options, default_path, support', 'length', 'max'=>255),
			array('approval_type, protocol', 'length', 'max'=>16),
			array('documentation, created, updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('api_id, code, display_name, brief_description, endpoint, queries_second, queries_day, access_type, access_options, documentation, created, updated, approval_type, protocol, strict_ssl, endpoint_timeout, default_path, owner_id, support', 'safe', 'on'=>'search'),
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
			'owner' => array(self::BELONGS_TO, 'User', 'owner_id'),
			'keys' => array(self::HAS_MANY, 'Key', 'api_id'),
			'keyRequests' => array(self::HAS_MANY, 'KeyRequest', 'api_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'api_id' => 'Api',
			'code' => 'Code',
			'display_name' => 'Display Name',
			'brief_description' => 'Brief Description',
			'endpoint' => 'Endpoint',
			'queries_second' => 'Queries Second',
			'queries_day' => 'Queries Day',
			'access_type' => 'Access Type',
			'access_options' => 'Access Options',
			'documentation' => 'Documentation',
			'created' => 'Created',
			'updated' => 'Updated',
			'approval_type' => 'Approval Type',
			'protocol' => 'Protocol',
			'strict_ssl' => 'Strict Ssl',
			'endpoint_timeout' => 'Endpoint Timeout',
			'default_path' => 'Default Path',
			'owner_id' => 'Owner',
			'support' => 'Support',
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

		$criteria->compare('api_id',$this->api_id);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('display_name',$this->display_name,true);
		$criteria->compare('brief_description',$this->brief_description,true);
		$criteria->compare('endpoint',$this->endpoint,true);
		$criteria->compare('queries_second',$this->queries_second);
		$criteria->compare('queries_day',$this->queries_day);
		$criteria->compare('access_type',$this->access_type,true);
		$criteria->compare('access_options',$this->access_options,true);
		$criteria->compare('documentation',$this->documentation,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('updated',$this->updated,true);
		$criteria->compare('approval_type',$this->approval_type,true);
		$criteria->compare('protocol',$this->protocol,true);
		$criteria->compare('strict_ssl',$this->strict_ssl);
		$criteria->compare('endpoint_timeout',$this->endpoint_timeout);
		$criteria->compare('default_path',$this->default_path,true);
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('support',$this->support,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ApiBase the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
