<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\components\AuthManager;

class AuthManagerTest extends \CTestCase
{
    public function testCanUseMultipleAuthTypes_0()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->canUseMultipleAuthTypes->thenCallParent();
        \Phake::when($authManager)->getKnownAuthTypeNames->thenReturn(
            array('hybrid', 'saml')
        );
        \Phake::when($authManager)->isAuthTypeEnabled('saml')->thenReturn(false);
        \Phake::when($authManager)->isAuthTypeEnabled('hybrid')->thenReturn(false);
        
        // Act:
        $result = $authManager->canUseMultipleAuthTypes();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to return false when no auth types are available.'
        );
    }
    
    public function testCanUseMultipleAuthTypes_1()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->canUseMultipleAuthTypes->thenCallParent();
        \Phake::when($authManager)->getKnownAuthTypeNames->thenReturn(
            array('hybrid', 'saml')
        );
        \Phake::when($authManager)->isAuthTypeEnabled('saml')->thenReturn(false);
        \Phake::when($authManager)->isAuthTypeEnabled('hybrid')->thenReturn(true);
        
        // Act:
        $result = $authManager->canUseMultipleAuthTypes();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to return false when only one auth types is available.'
        );
    }
    
    public function testCanUseMultipleAuthTypes_2()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->canUseMultipleAuthTypes->thenCallParent();
        \Phake::when($authManager)->getKnownAuthTypeNames->thenReturn(
            array('hybrid', 'saml')
        );
        \Phake::when($authManager)->isAuthTypeEnabled('saml')->thenReturn(true);
        \Phake::when($authManager)->isAuthTypeEnabled('hybrid')->thenReturn(true);
        
        // Act:
        $result = $authManager->canUseMultipleAuthTypes();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to return true when two auth types are available.'
        );
    }
    
    public function testGetApplicationEnv()
    {
        // Arrange:
        $authManager = new AuthManager();
        
        // Pre-assert:
        $this->assertTrue(
            defined('APPLICATION_ENV'),
            'This test requires that the APPLICATION_ENV constant is set.'
        );
        
        // Act:
        $result = $authManager->getApplicationEnv();
        
        // Assert:
        $this->assertSame($result, APPLICATION_ENV);
    }
    
    public function testGetDefaultAuthType_0()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->getDefaultAuthType->thenCallParent();
        \Phake::when($authManager)->getKnownAuthTypeNames->thenReturn(
            array('hybrid', 'saml')
        );
        \Phake::when($authManager)->isAuthTypeEnabled('saml')->thenReturn(false);
        \Phake::when($authManager)->isAuthTypeEnabled('hybrid')->thenReturn(false);
        
        // Act:
        $result = $authManager->getDefaultAuthType();
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when there are no known enabled auth types.'
        );
    }
    
    public function testGetDefaultAuthType_1()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->getDefaultAuthType->thenCallParent();
        \Phake::when($authManager)->getKnownAuthTypeNames->thenReturn(
            array('hybrid', 'saml')
        );
        \Phake::when($authManager)->isAuthTypeEnabled('saml')->thenReturn(false);
        \Phake::when($authManager)->isAuthTypeEnabled('hybrid')->thenReturn(true);
        
        // Act:
        $result = $authManager->getDefaultAuthType();
        
        // Assert:
        $this->assertTrue(
            is_string($result),
            'Failed to return a string when there is exactly one known enabled '
            . 'auth type.'
        );
    }
    
    public function testGetDefaultAuthType_2()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->getDefaultAuthType->thenCallParent();
        \Phake::when($authManager)->getKnownAuthTypeNames->thenReturn(
            array('hybrid', 'saml')
        );
        \Phake::when($authManager)->isAuthTypeEnabled('saml')->thenReturn(true);
        \Phake::when($authManager)->isAuthTypeEnabled('hybrid')->thenReturn(true);
        
        // Act:
        $result = $authManager->getDefaultAuthType();
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when there are multiple known enabled auth '
            . 'types.'
        );
    }
    
    public function testGetDefaultProviderSlugFor()
    {
        // Arrange:
        $testCases = [
            ['authType' => null, 'expected' => null],
            ['authType' => 'saml', 'expected' => 'insite'],
            ['authType' => 'someunknownvalue', 'expected' => null],
        ];
        $authManager = new AuthManager();
        foreach ($testCases as $testCase) {
            
            // Act:
            $actual = $authManager->getDefaultProviderSlugFor($testCase['authType']);

            // Assert:
            $this->assertSame($testCase['expected'], $actual);
        }
    }
    
    public function testGetIdentityForAuthType_disabledAuthType()
    {
        // Arrange:
        $disabledAuthType = 'fake';
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->getIdentityForAuthType->thenCallParent();
        \Phake::when($authManager)->isAuthTypeEnabled($disabledAuthType)->thenReturn(false);
        
        // Pre-assert:
        $this->setExpectedException('\InvalidArgumentException');
        
        // Act:
        $authManager->getIdentityForAuthType($disabledAuthType);
    }
    
    public function testIsAuthTypeEnabled_hybrid()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->isAuthTypeEnabled->thenCallParent();
        
        // Act:
        $authManager->isAuthTypeEnabled('hybrid');
        
        // Assert:
        \Phake::verify($authManager)->isHybridAuthEnabled;
    }
    
    public function testIsAuthTypeEnabled_unknown()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->isAuthTypeEnabled->thenCallParent();
        
        // Act:
        $result = $authManager->isAuthTypeEnabled('fake');
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to return false for an unknown auth type.'
        );
    }
    
    public function testIsAuthTypeEnabled_saml()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->isAuthTypeEnabled->thenCallParent();
        
        // Act:
        $authManager->isAuthTypeEnabled('saml');
        
        // Assert:
        \Phake::verify($authManager)->isSamlAuthEnabled;
    }
    
    public function testIsTestAuthEnabled()
    {
        // Arrange:
        $nonTestAuthApplicationEnvValues = ['production', 'prod', null, false, ''];
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->isTestAuthEnabled->thenCallParent();
        foreach ($nonTestAuthApplicationEnvValues as $applicationEnvValue) {
            \Phake::when($authManager)->getApplicationEnv->thenReturn(
                $applicationEnvValue
            );
            
            // Act:
            $actual = \Phake::makeVisible($authManager)->isTestAuthEnabled();

            // Assert:
            $this->assertFalse($actual, sprintf(
                'When the APPLICATION_ENV was %s, TestAuth was INCORRECTLY '
                . 'allowed.',
                var_export($applicationEnvValue, true)
            ));
        }
    }
    
    public function testLogout_webUserLogoutAndClearStatesAreCalled()
    {
        // Arrange:
        /* @var $authManager AuthManager */
        $authManager = \Phake::mock('\Sil\DevPortal\components\AuthManager');
        \Phake::when($authManager)->logout->thenCallParent();
        /* @var $webUser \WebUser */
        $webUser = \Phake::mock('\WebUser');
        
        // Act:
        $authManager->logout($webUser);
        
        // Assert:
        \Phake::verify($webUser)->logout;
        \Phake::verify($webUser)->clearStates;
    }
    
    public function testSlugify()
    {
        // Arrange:
        $testString = 'MixedCaseString';
        $expected = 'mixedcasestring';
        
        // Act:
        $actual = AuthManager::slugify($testString);
        
        // Assert:
        $this->assertSame($expected, $actual);
    }
}
