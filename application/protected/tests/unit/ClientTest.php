<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\components\ApiAxle\KeyInfo;
use Sil\DevPortal\components\ApiAxle\KeyringInfo;
use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\Key;

/**
 * @method Api apis(string $fixtureName)
 * @method Key keys(string $fixtureName)
 */
class ClientTest extends \CDbTestCase
{
    public $fixtures = array(
        'apis' => Api::class,
        'keys' => Key::class,
    );
    
    public function testCreateKeyring()
    {
        // Arrange:
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        
        // Act:
        $result = $apiAxle->createKeyring(md5(uniqid()));
        
        // Assert:
        $this->assertInstanceOf(KeyringInfo::class, $result);
    }
    
    public function testDeleteKeyring()
    {
        // Arrange:
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        $keyringName = md5(uniqid());
        $apiAxle->createKeyring($keyringName);
        
        // Act:
        $result = $apiAxle->deleteKeyring($keyringName);
        
        // Assert:
        $this->assertTrue($result);
    }
    
    public function testGetApiStats()
    {
        // Arrange:
        $api = $this->apis('api1');
        $api->save(); // Make sure the API exists in ApiAxle.
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        $timeStart = time() - 3600;
        $granularity = \UsageStats::INTERVAL_SECOND;
        
        // Act:
        $result = $apiAxle->getApiStats($api->code, $timeStart, $granularity);
        
        // Assert:
        $this->assertArrayHasKey('uncached', $result);
        $this->assertArrayHasKey('cached', $result);
        $this->assertArrayHasKey('error', $result);
    }
    
    public function testGetKeyInfo()
    {
        // Arrange:
        $key = $this->keys('pendingKeyToPublicApiThatAutoApprovesKeys');
        $key->api->save(); // Make sure the API exists in ApiAxle.
        $key->approve(); // Make sure the key exists in ApiAxle.
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        
        // Act:
        $result = $apiAxle->getKeyInfo($key->value);
        
        // Assert:
        $this->assertInstanceOf(KeyInfo::class, $result);
    }
    
    public function testGetKeyStats()
    {
        // Arrange:
        $key = $this->keys('pendingKeyToPublicApiThatAutoApprovesKeys');
        $key->api->save(); // Make sure the API exists in ApiAxle.
        $key->approve(); // Make sure the key exists in ApiAxle.
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        $timeStart = time() - 3600;
        $granularity = \UsageStats::INTERVAL_SECOND;
        
        // Act:
        $result = $apiAxle->getKeyStats($key->value, $timeStart, $granularity);
        
        // Assert:
        $this->assertArrayHasKey('uncached', $result);
        $this->assertArrayHasKey('cached', $result);
        $this->assertArrayHasKey('error', $result);
    }
    
    public function testUpdateKey()
    {
        // Arrange:
        $key = $this->keys('pendingKeyToPublicApiThatAutoApprovesKeys');
        $key->api->save(); // Make sure the API exists in ApiAxle.
        $key->approve(); // Make sure the key exists in ApiAxle.
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        
        // Act:
        $result = $apiAxle->updateKey($key->value, ['qps' => 10]);
        
        // Assert:
        $this->assertInstanceOf(KeyInfo::class, $result);
    }
}
