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
        'users'       => 'User',
        'keyRequests' => 'KeyRequest',
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
            'access_type' => 'public',
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
    
    public function testAxleResetKey()
    {
        // Create a new Api.
        $apiData = array(
            'code' => 'test-' . str_replace(array(' ', '.'), '', microtime()),
            'display_name' => __FUNCTION__,
            'endpoint' => 'localhost',
            'default_path' => '/path/' . __FUNCTION__,
            'queries_second' => 3,
            'queries_day' => 1000,
            'access_type' => Api::ACCESS_TYPE_PUBLIC,
            'protocol' => Api::PROTOCOL_HTTP,
            'strict_ssl' => Api::STRICT_SSL_TRUE,
            'approval_type' => Api::APPROVAL_TYPE_AUTO,
            'endpoint_timeout' => 2,
        );
        $api = new Api();
        $api->setAttributes($apiData);
        $result = $api->save();
        $this->assertTrue($result,
                'Failed to create API: ' . print_r($api->getErrors(), true));
        
        // Create a Key, using the IDs of the User and KeyRequest from our
        // fixture data.
        $key = Key::createKey($api->api_id, $this->users('user1')->user_id,
                $this->keyRequests('keyRequest1')->key_request_id);
        $this->assertTrue($key[0],
                'Failed to create Key: ' . print_r($key[1], true));
        
        $axleApi = new AxleApi($this->config, $api->code);
        $apiKeys = $axleApi->getKeyList();
        $hasKey = false;
        foreach($apiKeys as $apiKey){
            if($apiKey->getKey() == $key[1]->value){
                $hasKey = true;
                break;
            }
        }
        $this->assertTrue($hasKey,'New key is not linked to Api. Key errors (if any): '.print_r($key[1],true));
        
        $resetKey = Key::resetKey($key[1]->key_id);
        $this->assertTrue($resetKey[0],'Unable to reset key: '.print_r($resetKey[1],true));
        
        $apiKeys = $axleApi->getKeyList();
        $hasKey = false;
        foreach($apiKeys as $apiKey){
            if($apiKey->getKey() == $resetKey[1]->value){
                $hasKey = true;
                break;
            }
        }
        $this->assertTrue($hasKey,'Reset key is not linked to Api. Key errors (if any): '.print_r($resetKey[1],true));
    }
    
    public function testRevokeKey()
    {
        $apiData = array(
            'code' => 'test-' . str_replace(array(' ', '.'), '', microtime()),
            'display_name' => __FUNCTION__,
            'endpoint' => 'localhost',
            'default_path' => '/path/' . __FUNCTION__,
            'queries_second' => 3,
            'queries_day' => 1000,
            'access_type' => Api::ACCESS_TYPE_PUBLIC,
            'protocol' => Api::PROTOCOL_HTTP,
            'strict_ssl' => Api::STRICT_SSL_TRUE,
            'approval_type' => Api::APPROVAL_TYPE_AUTO,
            'endpoint_timeout' => 2,
        );
        $api = new Api();
        $api->setAttributes($apiData);
        $result = $api->save();
        $this->assertTrue($result,
                'Failed to create API: ' . print_r($api->getErrors(), true));
        
        $key = Key::createKey($api->api_id, 1, 1);
        $this->assertTrue($key[0],'Failed to create Key: '.print_r($key[1],true));
        
        $axleApi = new AxleApi($this->config, $api->code);
        $apiKeys = $axleApi->getKeyList();
        $hasKey = false;
        foreach($apiKeys as $apiKey){
            if($apiKey->getKey() == $key[1]->value){
                $hasKey = true;
                break;
            }
        }
        $this->assertTrue($hasKey,'New key is not linked to Api. Key errors (if any): '.print_r($key[1],true));
        
        $revokeKey = Key::revokeKey($key[1]->key_id);
        $this->assertTrue($revokeKey[0],'Failed to revoke key: '.print_r($revokeKey[1]));
        
        $apiKeys = $axleApi->getKeyList();
        $hasKey = false;
        foreach($apiKeys as $apiKey){
            if($apiKey->getKey() == $key[1]->value){
                $hasKey = true;
                break;
            }
        }
        $this->assertFalse($hasKey,'New key was not deleted from Api. Key errors (if any): '.print_r($revokeKey[1],true));
        
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
            'access_type' => 'public',
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
            'access_type' => 'public',
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
