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
 * @property string $documentation
 * @property string $created
 * @property string $updated
 * @property string $approval_type
 * @property string $protocol
 * @property integer $strict_ssl
 * @property integer $endpoint_timeout
 * @property string $default_path
 * @property integer $owner_id
 * @property string $technical_support
 * @property string $visibility
 * @property string $customer_support
 * @property string $terms
 * @property string $logo_url
 * @property string $require_signature
 * @property string $how_to_get
 * @property string $embedded_docs_url
 * @property string $additional_headers
 *
 * The followings are the available model relations:
 * @property User $owner
 * @property ApiVisibilityDomain[] $apiVisibilityDomains
 * @property ApiVisibilityUser[] $apiVisibilityUsers
 * @property Event[] $events
 * @property Key[] $keys
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
			array('code, display_name, endpoint, queries_second, queries_day, created, updated, endpoint_timeout', 'required'),
			array('queries_second, queries_day, strict_ssl, endpoint_timeout, owner_id', 'numerical', 'integerOnly'=>true),
			array('code', 'length', 'max'=>32),
			array('display_name', 'length', 'max'=>64),
			array('brief_description, endpoint, default_path, technical_support, customer_support, logo_url, embedded_docs_url, additional_headers', 'length', 'max'=>255),
			array('approval_type, protocol', 'length', 'max'=>5),
			array('visibility', 'length', 'max'=>10),
			array('require_signature', 'length', 'max'=>3),
			array('documentation, terms, how_to_get', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('api_id, code, display_name, brief_description, endpoint, queries_second, queries_day, documentation, created, updated, approval_type, protocol, strict_ssl, endpoint_timeout, default_path, owner_id, technical_support, visibility, customer_support, terms, logo_url, require_signature, how_to_get, embedded_docs_url, additional_headers', 'safe', 'on'=>'search'),
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
			'apiVisibilityDomains' => array(self::HAS_MANY, 'ApiVisibilityDomain', 'api_id'),
			'apiVisibilityUsers' => array(self::HAS_MANY, 'ApiVisibilityUser', 'api_id'),
			'events' => array(self::HAS_MANY, 'Event', 'api_id'),
			'keys' => array(self::HAS_MANY, 'Key', 'api_id'),
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
			'documentation' => 'Documentation',
			'created' => 'Created',
			'updated' => 'Updated',
			'approval_type' => 'Approval Type',
			'protocol' => 'Protocol',
			'strict_ssl' => 'Strict Ssl',
			'endpoint_timeout' => 'Endpoint Timeout',
			'default_path' => 'Default Path',
			'owner_id' => 'Owner',
			'technical_support' => 'Technical Support',
			'visibility' => 'Visibility',
			'customer_support' => 'Customer Support',
			'terms' => 'Terms',
			'logo_url' => 'Logo Url',
			'require_signature' => 'Require Signature',
			'how_to_get' => 'How To Get',
			'embedded_docs_url' => 'Embedded Docs Url',
			'additional_headers' => 'Additional Headers',
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
		$criteria->compare('documentation',$this->documentation,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('updated',$this->updated,true);
		$criteria->compare('approval_type',$this->approval_type,true);
		$criteria->compare('protocol',$this->protocol,true);
		$criteria->compare('strict_ssl',$this->strict_ssl);
		$criteria->compare('endpoint_timeout',$this->endpoint_timeout);
		$criteria->compare('default_path',$this->default_path,true);
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('technical_support',$this->technical_support,true);
		$criteria->compare('visibility',$this->visibility,true);
		$criteria->compare('customer_support',$this->customer_support,true);
		$criteria->compare('terms',$this->terms,true);
		$criteria->compare('logo_url',$this->logo_url,true);
		$criteria->compare('require_signature',$this->require_signature,true);
		$criteria->compare('how_to_get',$this->how_to_get,true);
		$criteria->compare('embedded_docs_url',$this->embedded_docs_url,true);
		$criteria->compare('additional_headers',$this->additional_headers,true);

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
