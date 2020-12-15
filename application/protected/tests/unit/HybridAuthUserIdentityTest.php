<?php
namespace Sil\DevPortal\tests\unit;

use Hybridauth\Hybridauth;
use Hybridauth\User\Profile;
use Sil\DevPortal\components\UserAuthenticationData;
use Sil\DevPortal\tests\TestCase;

class HybridAuthUserIdentityTest extends TestCase
{
    public function testGetUserAuthData_hasEmailVerified()
    {
        $this->markTestSkipped("hybrid auth has been refactored and cannot be mocked");
        // Arrange:
        /* @var $hybridUserProfile \Hybrid_User_Profile */
        $hybridUserProfile = \Phake::mock(Profile::class);
        $hybridUserProfile->emailVerified = 'verified-email@example.org';
        $hybridProviderAdapter = \Phake::mock(Profile::class);
        \Phake::when($hybridProviderAdapter)->getUserProfile->thenReturn(
            $hybridUserProfile
        );
        $hybridAuth = \Phake::mock(Hybridauth::class);
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
            UserAuthenticationData::class,
            $result
        );
    }
    
    public function testGetUserAuthData_lacksEmailVerified()
    {
        $this->markTestSkipped("hybrid auth has been refactored and cannot be mocked");
        // Arrange:
        /* @var $hybridUserProfile Profile */
        $hybridUserProfile = \Phake::mock(Profile::class);
        $hybridUserProfile->emailVerified = null;
        $hybridProviderAdapter = \Phake::mock(Profile::class);
        \Phake::when($hybridProviderAdapter)->getUserProfile->thenReturn(
            $hybridUserProfile
        );
        $hybridAuth = \Phake::mock(Hybridauth::class);
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
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('verified');
        $this->expectExceptionCode(1444924124);
        
        // Act:
        $hybridAuthUserIdentity->getUserAuthData();
    }
}
