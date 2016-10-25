<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\models\Api;

/**
 * @method Api apis(string $fixtureName)
 */
class ClientTest extends \CDbTestCase
{
    public $fixtures = array(
        'apis' => Api::class,
    );
    
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
}
