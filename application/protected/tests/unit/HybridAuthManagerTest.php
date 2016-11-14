<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\components\HybridAuthManager;

class HybridAuthManagerTest extends \CTestCase
{
    public function testGetBaseUrl_matchesGivenValue()
    {
        // Arrange:
        $expectedBaseUrl = 'http://local/sample/url';
        /* @var $hybridAuthManager HybridAuthManager */
        $hybridAuthManager = \Phake::partialMock(
            'Sil\DevPortal\components\HybridAuthManager',
            $expectedBaseUrl
        );
        
        // Act:
        $actualBaseUrl = \Phake::makeVisible($hybridAuthManager)->getBaseUrl();
        
        // Assert:
        $this->assertEquals(
            $expectedBaseUrl,
            $actualBaseUrl,
            'Failed to return the base URL given to the constructor.'
        );
    }
    
    public function testGetBaseUrl_stringReturnedEvenIfNoValueGivenToConstructor()
    {
        // Arrange:
        /* @var $hybridAuthManager HybridAuthManager */
        $hybridAuthManager = \Phake::mock(
            'Sil\DevPortal\components\HybridAuthManager'
        );
        \Phake::when($hybridAuthManager)->getBaseUrl->thenCallParent();
        
        // Act:
        $baseUrl = \Phake::makeVisible($hybridAuthManager)->getBaseUrl();
        
        // Assert:
        $this->assertTrue(
            is_string($baseUrl),
            'Failed to return a string when no base URL was given to the '
            . 'constructor.'
        );
        $this->assertStringStartsWith(
            'http',
            $baseUrl,
            'Failed to return a URL string when no base URL was given to the '
            . 'constructor.'
        );
    }
    
    public function testGetEnabledProvidersList_returnsArray()
    {
        // Arrange:
        $hybridAuthManager = new HybridAuthManager();
        
        // Act:
        $enabledProviders = $hybridAuthManager->getEnabledProvidersList();
        
        // Assert:
        $this->assertTrue(
            is_array($enabledProviders),
            'Failed to return an array.'
        );
    }
    
    public function testIsHybridAuthEnabled_no()
    {
        // Arrange:
        /* @var $hybridAuthManager HybridAuthManager */
        $hybridAuthManager = \Phake::mock(
            'Sil\DevPortal\components\HybridAuthManager'
        );
        \Phake::when($hybridAuthManager)->isHybridAuthEnabled->thenCallParent();
        \Phake::when($hybridAuthManager)->getEnabledProvidersList->thenCallParent();
        \Phake::when($hybridAuthManager)->getProvidersConfig->thenReturn(array(
            'SampleProvider' => array(
                'enabled' => false,
            ),
        ));
        
        // Act:
        $result = $hybridAuthManager->isHybridAuthEnabled();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that HybridAuth is enabled even though the '
            . 'only defined provider is not enabled.'
        );
    }
    
    public function testIsHybridAuthEnabled_noProviders()
    {
        // Arrange:
        /* @var $hybridAuthManager HybridAuthManager */
        $hybridAuthManager = \Phake::mock(
            'Sil\DevPortal\components\HybridAuthManager'
        );
        \Phake::when($hybridAuthManager)->isHybridAuthEnabled->thenCallParent();
        \Phake::when($hybridAuthManager)->getEnabledProvidersList->thenCallParent();
        \Phake::when($hybridAuthManager)->getProvidersConfig->thenReturn(array());
        
        // Act:
        $result = $hybridAuthManager->isHybridAuthEnabled();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that HybridAuth is enabled even though there '
            . 'are no providers configured.'
        );
    }
    
    public function testIsHybridAuthEnabled_yes()
    {
        // Arrange:
        /* @var $hybridAuthManager HybridAuthManager */
        $hybridAuthManager = \Phake::mock(
            'Sil\DevPortal\components\HybridAuthManager'
        );
        \Phake::when($hybridAuthManager)->isHybridAuthEnabled->thenCallParent();
        \Phake::when($hybridAuthManager)->getEnabledProvidersList->thenCallParent();
        \Phake::when($hybridAuthManager)->getProvidersConfig->thenReturn(array(
            'SampleDisabledProvider' => array(
                'enabled' => true,
            ),
            'SampleEnabledProvider' => array(
                'enabled' => true,
            ),
        ));
        
        // Act:
        $result = $hybridAuthManager->isHybridAuthEnabled();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that HybridAuth is enabled even though there is '
            . 'an enabled provider.'
        );
    }
}
