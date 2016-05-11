<?php
namespace Sil\DevPortal\tests\unit;

class HybridAuthUserIdentityTest extends \CTestCase
{
    public function testGetUserAuthData_hasEmailVerified()
    {
        // Arrange:
        /* @var $hybridUserProfile \Hybrid_User_Profile */
        $hybridUserProfile = \Phake::mock('\Hybrid_User_Profile');
        $hybridUserProfile->emailVerified = 'verified-email@example.org';
        $hybridProviderAdapter = \Phake::mock('\Hybrid_Provider_Adapter');
        \Phake::when($hybridProviderAdapter)->getUserProfile->thenReturn(
            $hybridUserProfile
        );
        $hybridAuth = \Phake::mock('\Hybrid_Auth');
        \Phake::whenStatic($hybridAuth)->authenticate->thenReturn(
            $hybridProviderAdapter
        );
        /* @var $hybridAuthUserIdentity \Sil\DevPortal\components\HybridAuthUserIdentity */
        $hybridAuthUserIdentity = \Phake::mock(
            'Sil\DevPortal\components\HybridAuthUserIdentity'
        );
        \Phake::when($hybridAuthUserIdentity)->getAuthProvider->thenCallParent();
        \Phake::when($hybridAuthUserIdentity)->getUserAuthData->thenCallParent();
        \Phake::when($hybridAuthUserIdentity)->getHybridAuthInstance->thenReturn(
            $hybridAuth
        );
        
        // Act:
        $result = $hybridAuthUserIdentity->getUserAuthData();
        
        // Assert:
        $this->assertInstanceOf(
            'Sil\DevPortal\components\UserAuthenticationData',
            $result
        );
    }
    
    public function testGetUserAuthData_lacksEmailVerified()
    {
        // Arrange:
        /* @var $hybridUserProfile \Hybrid_User_Profile */
        $hybridUserProfile = \Phake::mock('\Hybrid_User_Profile');
        $hybridUserProfile->emailVerified = null;
        $hybridProviderAdapter = \Phake::mock('\Hybrid_Provider_Adapter');
        \Phake::when($hybridProviderAdapter)->getUserProfile->thenReturn(
            $hybridUserProfile
        );
        $hybridAuth = \Phake::mock('\Hybrid_Auth');
        \Phake::whenStatic($hybridAuth)->authenticate->thenReturn(
            $hybridProviderAdapter
        );
        /* @var $hybridAuthUserIdentity \Sil\DevPortal\components\HybridAuthUserIdentity */
        $hybridAuthUserIdentity = \Phake::mock(
            'Sil\DevPortal\components\HybridAuthUserIdentity'
        );
        \Phake::when($hybridAuthUserIdentity)->getAuthProvider->thenCallParent();
        \Phake::when($hybridAuthUserIdentity)->getUserAuthData->thenCallParent();
        \Phake::when($hybridAuthUserIdentity)->getHybridAuthInstance->thenReturn(
            $hybridAuth
        );
        
        // Pre-assert:
        $this->setExpectedException('Exception', 'verified', 1444924124);
        
        // Act:
        $hybridAuthUserIdentity->getUserAuthData();
    }
}
