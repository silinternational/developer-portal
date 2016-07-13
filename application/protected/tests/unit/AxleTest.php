<?php

use ApiAxle\Api\Api as AxleApi;
use ApiAxle\Api\Key as AxleKey;
use ApiAxle\Shared\ApiException;

/**
 * @group ApiAxle
 */
class AxleTest extends DeveloperPortalTestCase
{
    protected $config;
    
    public $fixtures = array(
        'users' => 'User',
        'keys' => 'Key',
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
            
            // Set up our ApiAxle Api class.
            $api = new AxleApi(self::getConfig());
            
            // Delete all the APIs that start with the string we use to identify
            // test APIs.
            $apiList = $api->getList(0,1000);
            foreach($apiList as $item){
                if(strpos($item->getName(),'test-') !== false){
                    $api->delete($item->getName());
                }
            }
            
            // Set up our ApiAxle Key class.
            $key = new AxleKey(self::getConfig());
            
            // Get the list of Keys from ApiAxle.
            $keyList = $key->getList(0, 1000);
            
            // For each key that ApiAxle returned...
            foreach ($keyList as $item) {
                
                // If it starts with the string we use to identify test Keys,
                // delete it.
                if (strpos($item->getKey(), 'test-') !== false) {
                    $key->delete($item->getKey());
                }
                
                // QUESTION: Why was this here?
                // 
                //if(preg_match('/[0-9a-z]{32}/',$item->getKey())){
                //    $key->delete($item->getKey());
                //}
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
            'visibility' => \Api::VISIBILITY_PUBLIC,
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
        
        $axleApi = new AxleApi($this->config);
        $apiList = $axleApi->getList(0,1000);
        $inList = false;
        foreach($apiList as $a){
            if($a->getName() == $apiData['code']){
                $inList = true;
                break;
            }
        }
        $this->assertTrue($inList,'Api was created locally but not found on ApiAxle');
    }
    
    public function testAxleCreateResetAndRevokeKey()
    {
        // Arrange:
        $normalUser = $this->users('userWithRoleOfUser');
        $adminUser = $this->users('userWithRoleOfAdmin');
        $api = new \Api();
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
        $key = new \Key();
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
        $resetKeyResult = \Key::resetKey($key->key_id);
        
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
        $revokeKeyResult = \Key::revokeKey($key->key_id, $adminUser);
        
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
            'visibility' => \Api::VISIBILITY_PUBLIC,
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
            'visibility' => \Api::VISIBILITY_PUBLIC,
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
