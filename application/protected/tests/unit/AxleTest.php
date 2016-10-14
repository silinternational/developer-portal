<?php

use ApiAxle\Api\Api as AxleApi;
use ApiAxle\Shared\ApiException;
use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\Key;

/**
 * @group ApiAxle
 */
class AxleTest extends DeveloperPortalTestCase
{
    protected $config;
    
    public $fixtures = array(
        'users' => '\Sil\DevPortal\models\User',
        'keys' => '\Sil\DevPortal\models\Key',
    );  
    
    public function setUp()
    {
        global $ENABLE_AXLE;
        if(!isset($ENABLE_AXLE) || !$ENABLE_AXLE){
            $ENABLE_AXLE = true;
        }
        Yii::app()->user->id = 1;
        parent::setUp();
        $this->config = $this->getConfig();
    }
    
    public static function getConfig()
    {
        return Yii::app()->params['apiaxle'];
    }
    
    public static function tearDownAfterClass()
    {
        try {
            $apiAxle = new ApiAxleClient(self::getConfig());
            
            // Delete all the APIs that start with the string we use to identify
            // test APIs.
            $apiInfoList = $apiAxle->listApis(0, 1000);
            foreach ($apiInfoList as $apiInfo) {
                if (strpos($apiInfo->getName(), 'test-') !== false) {
                    $apiAxle->deleteApi($apiInfo->getName());
                }
            }
            
            // Get the list of keys from ApiAxle.
            $keyInfoList = $apiAxle->listKeys(0, 1000);
            
            // For each key that ApiAxle returned...
            foreach ($keyInfoList as $keyInfo) {
                
                // If it starts with the string we use to identify test keys,
                // delete it.
                if (strpos($keyInfo->getKeyValue(), 'test-') !== false) {
                    $apiAxle->deleteKey($keyInfo->getKeyValue());
                }
            }
        } catch(ApiException $ae){
            echo $ae;
        } catch(\Exception $e){
            echo $e;
        }
    }
    
    public function testAxleCreateApi()
    {
        $apiData = array(
            'code' => 'test-'.str_replace(array(' ','.'),'',microtime()),
            'display_name' => __FUNCTION__,
            'endpoint' => 'localhost',
            'default_path' => '/path/' . __FUNCTION__,
            'queries_second' => 3,
            'queries_day' => 1000,
            'visibility' => Api::VISIBILITY_PUBLIC,
            'protocol' => 'http',
            'strict_ssl' => 1,
            'approval_type' => 'auto',
            'endpoint_timeout' => 2,
        );
        
        $api = new Api();
        $api->setAttributes($apiData);
        $result = $api->save();
        $this->assertTrue($result, 'Failed to create API: ' . PHP_EOL .
            self::getModelErrorsForConsole($api->getErrors()));
        
        $apiAxle = new ApiAxleClient($this->config);
        $apiInfoList = $apiAxle->listApis(0, 1000);
        $inList = false;
        foreach ($apiInfoList as $apiInfo) {
            if ($apiInfo->getName() == $apiData['code']) {
                $inList = true;
                break;
            }
        }
        $this->assertTrue($inList, 'Api was created locally but not found on ApiAxle');
    }
    
    public function testAxleCreateResetAndRevokeKey()
    {
        // Arrange:
        $normalUser = $this->users('userWithRoleOfUser');
        $adminUser = $this->users('userWithRoleOfAdmin');
        $api = new Api();
        $api->setAttributes(array(
            'code' => 'test-' . str_replace('.', '', microtime(true)),
            'display_name' => __FUNCTION__,
            'endpoint' => 'localhost',
            'default_path' => '/path/' . __FUNCTION__,
            'queries_second' => 3,
            'queries_day' => 1000,
            'visibility' => Api::VISIBILITY_PUBLIC,
            'protocol' => Api::PROTOCOL_HTTP,
            'strict_ssl' => Api::STRICT_SSL_TRUE,
            'approval_type' => Api::APPROVAL_TYPE_AUTO,
            'endpoint_timeout' => 2,
        ));
        $this->assertTrue(
            $api->save(),
            'Failed to create API: ' . print_r($api->getErrors(), true)
        );
        $key = new Key();
        $key->setAttributes(array(
            'user_id' => $normalUser->user_id,
            'api_id' => $api->api_id,
            'queries_second' => $api->queries_second,
            'queries_day' => $api->queries_day,
            'created' => 1465414526,
            'updated' => 1465414526,
            'requested_on' => 1465414526,
            'status' => Key::STATUS_PENDING,
            'purpose' => 'Unit testing',
            'domain' => 'developer-portal.local',
        ));
        
        // Act (create):
        $approveKeyResult = $key->approve($normalUser);
        
        // Assert (create):
        $this->assertTrue(
            $approveKeyResult,
            'Failed to create/approve Key: ' . print_r($key->getErrors(), true)
        );
        $axleApi = new AxleApi($this->config, $api->code);
        $axleApiKeysAfterCreate = $axleApi->getKeyList();
        $hasKeyAfterCreate = false;
        foreach ($axleApiKeysAfterCreate as $axleApiKey) {
            if ($axleApiKey->getKey() == $key->value) {
                $hasKeyAfterCreate = true;
                break;
            }
        }
        $this->assertTrue(
            $hasKeyAfterCreate,
            'New key is not linked to AxleApi. Key errors (if any): '
            . print_r($key, true)
        );
        $initialKeyValue = $key->value;
        $initialKeySecret = $key->secret;
        
        // Act (reset):
        $resetKeyResult = Key::resetKey($key->key_id);
        
        // Assert (reset):
        $this->assertTrue(
            $resetKeyResult[0],
            'Unable to reset key: ' . print_r($resetKeyResult[1], true)
        );
        $axleApiKeysAfterReset = $axleApi->getKeyList();
        $hasKeyAfterReset = false;
        foreach ($axleApiKeysAfterReset as $axleApiKey) {
            if ($axleApiKey->getKey() == $resetKeyResult[1]->value) {
                $hasKeyAfterReset = true;
                break;
            }
        }
        $this->assertTrue(
            $hasKeyAfterReset,
            'Reset key is not linked to AxleApi. Key errors (if any): '
            . print_r($resetKeyResult[1], true)
        );
        $changedKeyValue = $resetKeyResult[1]->value;
        $changedKeySecret = $resetKeyResult[1]->secret;
        $this->assertNotEquals(
            $initialKeyValue,
            $changedKeyValue,
            'Resetting the key did not change its value.'
        );
        $this->assertNotEquals(
            $initialKeySecret,
            $changedKeySecret,
            'Resetting the key did not change its secret.'
        );
        
        // Act (revoke):
        $revokeKeyResult = Key::revokeKey($key->key_id, $adminUser);
        
        // Assert (revoke):
        $key->refresh();
        $this->assertTrue(
            $revokeKeyResult[0],
            'Unable to revoke key: ' . print_r($revokeKeyResult[1], true)
        );
        $axleApiKeysAfterRevoke = $axleApi->getKeyList();
        $hasKeyAfterRevoke = false;
        foreach ($axleApiKeysAfterRevoke as $axleApiKey) {
            if ($axleApiKey->getKey() == $key->value) {
                $hasKeyAfterRevoke = true;
                break;
            }
        }
        $this->assertFalse(
            $hasKeyAfterRevoke,
            'Revoked key was not deleted from AxleApi. Key errors (if any): '
            . print_r($revokeKeyResult[1], true)
        );
    }
    
    public function testDeleteApi()
    {
        $apiData = array(
            'code' => 'test-'.str_replace(array(' ','.'),'',microtime()),
            'display_name' => __FUNCTION__,
            'endpoint' => 'localhost',
            'default_path' => '/path/' . __FUNCTION__,
            'queries_second' => 3,
            'queries_day' => 1000,
            'visibility' => Api::VISIBILITY_PUBLIC,
            'protocol' => 'http',
            'strict_ssl' => 1,
            'approval_type' => 'auto',
            'endpoint_timeout' => 2,
        );
        $api = new Api();
        $api->setAttributes($apiData);
        $result = $api->save();
        $this->assertTrue($result,'Failed to create API: '.print_r($api->getErrors(),true));
        
        $axleApi = new AxleApi($this->config);
        $apiList = $axleApi->getList(0,1000);
        $hasApi = false;
        foreach($apiList as $a){
            if($a->getName() == $api->code){
                $hasApi = true;
                break;
            }
        }
        $this->assertTrue($hasApi,'New API not found on server.');
        
        $api->delete();
        $apiList = $axleApi->getList(0,1000);
        $hasApi = false;
        foreach($apiList as $a){
            if($a->getName() == $api->code){
                $hasApi = true;
                break;
            }
        }
        $this->assertFalse($hasApi,'New API still found after delete.');
    }
    
    public function disabletestAxleCreate100Apis()
    {
        $count = 0;
        $howMany = 500;
        $apiData = array(
            'display_name' => __FUNCTION__,
            'endpoint' => 'localhost',
            'default_path' => '/path/' . __FUNCTION__,
            'queries_second' => 3,
            'queries_day' => 1000,
            'visibility' => Api::VISIBILITY_PUBLIC,
            'protocol' => 'http',
            'strict_ssl' => 1,
            'approval_type' => 'auto',
            'endpoint_timeout' => 2,
        );
        
        while($count < $howMany){
            $apiData['code'] = 'test-'.$count++;
            $api = new Api();
            $api->setAttributes($apiData);
            $api->save();
        }
        
        $inList = 0;
        $axleApi = new AxleApi($this->config);
        $apiList = $axleApi->getList(0,1000);
        foreach($apiList as $a){
            if(preg_match('/test\-[0-9]{1,3}/',$a->getName())){
                $inList++;
            }
        }
        
        $this->assertEquals($howMany-1,$inList);
    }
}
