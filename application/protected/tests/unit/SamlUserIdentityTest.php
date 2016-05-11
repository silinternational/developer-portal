<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\components\SamlUserIdentity;
use Sil\DevPortal\components\UserAuthenticationData;
use Sil\DevPortal\components\UserIdentity;

class SamlUserIdentityTest extends \CDbTestCase
{
    public $fixtures = array(
        'users' => 'User',
    );
    
    public function testExtractAccessGroups_exampleFromFunctionDocumentation()
    {
        // Arrange:
        $rawAccessGroups = array(
            'a=GROUP_name,b=stuff',
            'a=OTHER-GROUP,b=random',
        );
        $expectedResult = array(
            'GROUP_NAME',
            'OTHER-GROUP',
        );
        /* @var $samlUserIdentity SamlUserIdentity */
        $samlUserIdentity = \Phake::mock(
            '\Sil\DevPortal\components\SamlUserIdentity'
        );
        \Phake::when($samlUserIdentity)->extractAccessGroups->thenCallParent();
        
        // Act:
        $actualResults = $samlUserIdentity->extractAccessGroups($rawAccessGroups);
        
        // Assert:
        $this->assertEquals($expectedResult, $actualResults);
    }
    
    public function testFindUserRecord_fallbackToMatchByEmailForTrustedAuthProvider()
    {
        // Arrange:
        /* @var $expectedUser \User */
        $expectedUser = $this->users('userFromTrustedAuthProviderLackingIdentifier');
        $userAuthData = new UserAuthenticationData(
            $expectedUser->auth_provider,
            'fake-identifier-1461943235',
            $expectedUser->email,
            $expectedUser->first_name,
            $expectedUser->last_name,
            $expectedUser->display_name
        );
        $samlUserIdentity = \Phake::mock(
            '\Sil\DevPortal\components\SamlUserIdentity'
        );
        \Phake::when($samlUserIdentity)->canTrustEmailAsFallbackIdFor->thenCallParent();
        \Phake::when($samlUserIdentity)->findUserRecord->thenCallParent();
        
        // Act:
        $actualUser = \Phake::makeVisible($samlUserIdentity)->findUserRecord(
            $userAuthData
        );
        
        // Assert:
        $this->assertEquals(
            $expectedUser,
            $actualUser,
            'Failed to find expected user (from trusted auth. provider) by '
            . 'email when record in database lacked auth. provider identifier.'
        );
    }
    
    public function testFindUserRecord_doNotFallbackToMatchByEmailForOtherAuthProvider()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userFromOtherAuthProviderLackingIdentifier');
        $userAuthData = new UserAuthenticationData(
            $user->auth_provider,
            'fake-identifier-1461943317',
            $user->email,
            $user->first_name,
            $user->last_name,
            $user->display_name
        );
        $samlUserIdentity = \Phake::mock(
            '\Sil\DevPortal\components\SamlUserIdentity'
        );
        \Phake::when($samlUserIdentity)->canTrustEmailAsFallbackIdFor->thenCallParent();
        \Phake::when($samlUserIdentity)->findUserRecord->thenCallParent();
        
        // Act:
        $result = \Phake::makeVisible($samlUserIdentity)->findUserRecord(
            $userAuthData
        );
        
        // Assert:
        $this->assertNull(
            $result,
            'Incorrectly found user (NOT from the trusted auth. provider) by '
            . 'email when record in database lacked auth. provider identifier.'
        );
    }
    
    public function testGetAuthSourceIdpEntityId()
    {
        // Arrange:
        $samlUserIdentity = \Phake::partialMock(
            '\Sil\DevPortal\components\SamlUserIdentity'
        );
        
        // Act:
        $result = \Phake::makeVisible($samlUserIdentity)->getAuthSourceIdpEntityId();
        
        // Assert:
        $this->assertTrue(
            is_string($result),
            'Failed to return a string.'
        );
        $this->assertGreaterThan(
            0,
            strlen($result),
            'Failed to return a non-empty string.'
        );
    }
    
    public function testGetLogoutUrl()
    {
        // Arrange:
        $samlUserIdentity = new SamlUserIdentity();
        
        // Act:
        $logoutUrl = $samlUserIdentity->getLogoutUrl();
        
        // Assert:
        $this->assertTrue(
            is_string($logoutUrl),
            'Failed to return a string.'
        );
        $this->assertStringStartsWith(
            'http',
            $logoutUrl,
            'Failed to return a string that looks like a URL.'
        );
    }
    
    public function testGetNameOfAuthProvider_knownValue()
    {
        // Arrange:
        $samlUserIdentity = \Phake::mock(
            '\Sil\DevPortal\components\SamlUserIdentity'
        );
        $idp = 'dummy.value.one'; // Match value in config/test.php file.
        $expectedResult = 'IdP One';
        \Phake::when($samlUserIdentity)->getAuthSourceIdpEntityId->thenReturn($idp);
        \Phake::when($samlUserIdentity)->getNameOfAuthProvider->thenCallParent();
        
        // Act:
        $actualResult = \Phake::makeVisible($samlUserIdentity)->getNameOfAuthProvider();
        
        // Assert:
        $this->assertSame(
            $expectedResult,
            $actualResult,
            'Failed to return correct name for a known SAML auth provider (see '
            . 'config/test.php).'
        );
    }
    
    public function testGetNameOfAuthProvider_unknownValue()
    {
        // Arrange:
        $samlUserIdentity = \Phake::mock(
            '\Sil\DevPortal\components\SamlUserIdentity'
        );
        $idpEntityId = 'abc.123';
        $expectedResult = $idpEntityId;
        \Phake::when($samlUserIdentity)->getAuthSourceIdpEntityId->thenReturn($idpEntityId);
        \Phake::when($samlUserIdentity)->getNameOfAuthProvider->thenCallParent();
        
        // Act:
        $actualResult = \Phake::makeVisible($samlUserIdentity)->getNameOfAuthProvider();
        
        // Assert:
        $this->assertSame(
            $expectedResult,
            $actualResult,
            'Failed to return given string when it was not a known value.'
        );
    }
}
