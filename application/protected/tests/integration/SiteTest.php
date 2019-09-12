<?php
namespace Sil\DevPortal\tests\integration;

use Sil\DevPortal\components\Http\ClientG5 as HttpClient;
use Sil\DevPortal\tests\TestCase;

class SiteTest extends TestCase
{
    public function testSystemCheck()
    {
        // Arrange:
        $systemCheckUrl = \Yii::app()->createAbsoluteUrl('/site/system-check/');
        $client = new HttpClient();

        // Act:
        $response = $client->request('GET', $systemCheckUrl);
        
        // Assert:
        $this->assertSame('OK', $response->getBody(), sprintf(
            "Unexpected response from system-check URL. Response headers:\n%s",
            $response->getHeaders()
        ));
    }
}
