<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\components\UserAuthenticationData;
use Sil\DevPortal\components\UserIdentity;

class UserIdentityTest extends \CDbTestCase
{
    public $fixtures = array(
        'users' => 'User',
    );
    
    public function testAuthenticate_activeUser()
    {
        // Arrange:
        $userAuthData = \Phake::mock('\Sil\DevPortal\components\UserAuthenticationData');
        $user = \Phake::mock('\User');
        \Phake::when($user)->isDisabled->thenReturn(false);
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->authenticate->thenCallParent();
        \Phake::when($userIdentity)->getUserAuthData->thenReturn($userAuthData);
        \Phake::when($userIdentity)->findUserRecord->thenReturn($user);
        \Phake::when($userIdentity)->getIsAuthenticated->thenCallParent();
        
        // Act:
        $userIdentity->authenticate();
        
        // Assert:
        \Phake::verify($userIdentity)->getUserAuthData;
        \Phake::verify($userIdentity)->findUserRecord;
        \Phake::verify($userIdentity, \Phake::never())->createUserRecord;
        \Phake::verify($userIdentity)->updateUserRecord;
        \Phake::verify($userIdentity)->loadIdentity;
        \Phake::verify($userIdentity)->getIsAuthenticated;
    }
    
    public function testAuthenticate_callsExpectedMethodsForInactiveUser()
    {
        // Arrange:
        $userAuthData = \Phake::mock('\Sil\DevPortal\components\UserAuthenticationData');
        $disabledUser = \Phake::mock('\User');
        \Phake::when($disabledUser)->isDisabled->thenReturn(true);
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->authenticate->thenCallParent();
        \Phake::when($userIdentity)->getUserAuthData->thenReturn($userAuthData);
        \Phake::when($userIdentity)->findUserRecord->thenReturn($disabledUser);
        \Phake::when($userIdentity)->getIsAuthenticated->thenCallParent();
        
        // Act:
        $result = $userIdentity->authenticate();
        
        // Assert:
        \Phake::verify($userIdentity)->getUserAuthData;
        \Phake::verify($userIdentity)->findUserRecord;
        \Phake::verify($userIdentity, \Phake::never())->createUserRecord;
        \Phake::verify($userIdentity)->updateUserRecord;
        \Phake::verify($userIdentity, \Phake::never())->loadIdentity;
        \Phake::verify($userIdentity)->getIsAuthenticated;
        $this->assertFalse(
            $result,
            'Failed to return false for an inactive user.'
        );
    }
    
    public function testAuthenticate_callsExpectedMethodsForNewUser()
    {
        // Arrange:
        $userAuthData = \Phake::mock('\Sil\DevPortal\components\UserAuthenticationData');
        $newUser = \Phake::mock('\User');
        \Phake::when($newUser)->isDisabled->thenReturn(false);
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->authenticate->thenCallParent();
        \Phake::when($userIdentity)->getUserAuthData->thenReturn($userAuthData);
        \Phake::when($userIdentity)->findUserRecord->thenReturn(null);
        \Phake::when($userIdentity)->createUserRecord->thenReturn($newUser);
        
        // Act:
        $userIdentity->authenticate();
        
        // Assert:
        \Phake::verify($userIdentity)->getUserAuthData;
        \Phake::verify($userIdentity)->findUserRecord;
        \Phake::verify($userIdentity)->createUserRecord;
        \Phake::verify($userIdentity, \Phake::never())->updateUserRecord;
        \Phake::verify($userIdentity)->loadIdentity;
        \Phake::verify($userIdentity)->getIsAuthenticated;
    }
    
    public function testCreateUserRecord_invalidNewUserData()
    {
        // Arrange:
        $userAuthData = new UserAuthenticationData(
            'IdP One',
            uniqid(),
            '', // Empty string as (intentionally invalid) email address.
            'Test',
            'Account',
            'Test Account'
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->createUserRecord->thenCallParent();
        
        // Pre-assert:
        $this->setExpectedException('\Exception', '', 1444679705);
        
        // Act:
        \Phake::makeVisible($userIdentity)->createUserRecord($userAuthData);
        
        // Assert:
        $this->fail(
            'An exception should have been thrown before this point from '
            . 'trying to create a user with invalid initial data.'
        );
    }
    
    public function testCreateUserRecord_newEmailAddress()
    {
        // Arrange:
        $newEmailAddress = sprintf(
            'test_new_%s@example.org',
            microtime(true)
        );
        $userAuthData = new UserAuthenticationData(
            'IdP One',
            uniqid(),
            $newEmailAddress,
            'Test',
            'Account',
            'Test Account'
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->createUserRecord->thenCallParent();
        
        // Pre-assert:
        $this->assertFalse(
            \User::isEmailAddressInUse($newEmailAddress),
            'This test requires an email address that is not yet in use in the '
            . 'test database.'
        );
        
        // Act:
        $result = \Phake::makeVisible($userIdentity)->createUserRecord($userAuthData);
        
        // Assert:
        $this->assertInstanceOf(
            '\User',
            $result,
            'Failed to return new User instance.'
        );
        $this->assertTrue(
            \User::isEmailAddressInUse($newEmailAddress),
            'Failed to find the new email address in the database after '
            . 'creating the user record.'
        );
    }
    
    public function testCreateUserRecord_usedEmailAddress()
    {
        // Arrange:
        $dataFromExistingUser = $this->users['user1'];
        $userAuthData = new UserAuthenticationData(
            'IdP One',
            uniqid(),
            $dataFromExistingUser['email'],
            $dataFromExistingUser['first_name'],
            $dataFromExistingUser['last_name'],
            $dataFromExistingUser['display_name']
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->createUserRecord->thenCallParent();
        
        // Pre-assert:
        $this->assertTrue(
            \User::isEmailAddressInUse($dataFromExistingUser['email']),
            'This test requires an email address that is already in use in the '
            . 'test database.'
        );
        $this->setExpectedException('\Exception', '', 1444679782);
        
        // Act:
        \Phake::makeVisible($userIdentity)->createUserRecord($userAuthData);
        
        // Assert: (n/a)
    }
    
    public function testFindUserRecord_existingUser()
    {
        // Arrange:
        $existingUser = $this->users('userFromIdpOne');
        $userAuthData = \Phake::mock('\Sil\DevPortal\components\UserAuthenticationData');
        \Phake::when($userAuthData)->getAuthProvider->thenReturn(
            $existingUser->auth_provider
        );
        \Phake::when($userAuthData)->getAuthProviderUserIdentifier->thenReturn(
            $existingUser->auth_provider_user_identifier
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->findUserRecord->thenCallParent();
        
        // Act:
        $returnedUser = \Phake::makeVisible($userIdentity)->findUserRecord(
            $userAuthData
        );
        
        // Assert:
        \Phake::verify($userIdentity, \Phake::never())->warnIfEmailIsInUseByDiffAuthProvider;
        $this->assertEquals(
            $existingUser,
            $returnedUser,
            'Failed to return the (correct) existing User.'
        );
    }
    
    public function testFindUserRecord_noSuchUser()
    {
        // Arrange:
        $userAuthData = \Phake::mock('\Sil\DevPortal\components\UserAuthenticationData');
        \Phake::when($userAuthData)->getAuthProvider->thenReturn(
            'IdP One'
        );
        \Phake::when($userAuthData)->getAuthProviderUserIdentifier->thenReturn(
            'fake-identifier-1461943872'
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->findUserRecord->thenCallParent();
        
        // Act:
        $result = \Phake::makeVisible($userIdentity)->findUserRecord(
            $userAuthData
        );
        
        // Assert:
        \Phake::verify($userIdentity)->warnIfEmailIsInUseByDiffAuthProvider;
        $this->assertNull(
            $result,
            'Failed to return null for a user not in the database.'
        );
    }
    
    public function testGetId_hasValue()
    {
        // Arrange:
        $expectedId = uniqid();
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->getId->thenCallParent();
        \Phake::when($userIdentity)->setId->thenCallParent();
        $userIdentity->setId($expectedId);
        
        // Act:
        $resultId = $userIdentity->getId();
        
        // Assert:
        $this->assertEquals(
            $expectedId,
            $resultId,
            'Failed to return the ID just given to this UserIdentity.'
        );
    }
    
    public function testGetId_noValue()
    {
        // Arrange:
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->getId->thenCallParent();
        
        // Act:
        $result = $userIdentity->getId();
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when no ID had been set.'
        );
    }
    
    public function testGetName_hasValue()
    {
        // Arrange:
        $expectedName = 'Some Name';
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->getName->thenCallParent();
        \Phake::when($userIdentity)->setName->thenCallParent();
        $userIdentity->setName($expectedName);
        
        // Act:
        $resultName = $userIdentity->getName();
        
        // Assert:
        $this->assertEquals(
            $expectedName,
            $resultName,
            'Failed to return the name just given to this UserIdentity.'
        );
    }
    
    public function testGetName_noValue()
    {
        // Arrange:
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->getName->thenCallParent();
        
        // Act:
        $result = $userIdentity->getName();
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when no name had been set.'
        );
    }
    
    public function testLoadIdentity()
    {
        // Arrange:
        $authType = 'someAuthType';
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->loadIdentity->thenCallParent();
        \Phake::when($userIdentity)->getAuthType->thenReturn($authType);
        $user = $this->users('userFromIdpOne');
        $accessGroups = array('test access_group', 'other-test_ACCESS-group');
        $uuid = uniqid(); // Dummy value for uuid.
        
        // Act:
        $userIdentity->loadIdentity($user, $accessGroups, $uuid);
        
        // Assert:
        foreach ($accessGroups as $accessGroup) {
            $this->assertTrue(
                $user->isInAccessGroup($accessGroup),
                'Failed to record that user is in one of the given access '
                . 'groups.'
            );
        }
        \Phake::verify($userIdentity)->setState('user', $user);
        \Phake::verify($userIdentity)->setState('uuid', $uuid);
        \Phake::verify($userIdentity)->setState('authType', $authType);
        \Phake::verify($userIdentity)->setId($user->user_id);
        \Phake::verify($userIdentity)->setName($user->display_name);
    }
    
    public function testSetId()
    {
        // Arrange:
        $expectedId = uniqid();
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->getId->thenCallParent();
        \Phake::when($userIdentity)->setId->thenCallParent();
        
        // Act:
        $userIdentity->setId($expectedId);
        
        // Assert:
        $this->assertEquals(
            $expectedId,
            $userIdentity->getId(),
            'Retrieved value did not match value given to setId().'
        );
    }
    
    public function testSetName()
    {
        // Arrange:
        $expectedName = 'Some Dummy Name';
        /* @var $userIdentity UserIdentity */
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->getName->thenCallParent();
        \Phake::when($userIdentity)->setName->thenCallParent();
        
        // Act:
        $userIdentity->setName($expectedName);
        
        // Assert:
        $this->assertEquals(
            $expectedName,
            $userIdentity->getName(),
            'Retrieved value did not match value given to setName().'
        );
    }
    
    public function testUpdateUserRecord_noSuchUser()
    {
        // Arrange:
        $tempUniqueId = uniqid();
        $userAuthData = new UserAuthenticationData(
            'IdP One',
            'fake-identifier-1461943899',
            $tempUniqueId . '@jaars.net',
            'Some Test',
            'Name',
            'Some Test Name'
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->updateUserRecord->thenCallParent();
        
        // Pre-assert:
        $this->setExpectedException('\Exception', '', 1445976913);
        
        // Act:
        \Phake::makeVisible($userIdentity)->updateUserRecord($userAuthData);
        
        // Assert:
        $this->fail(
            'An exception should have been thrown before this point from '
            . 'trying to update a non-existent user.'
        );
    }
    
    public function testUpdateUserRecord_success()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userFromIdpOne');
        $newFirstName = uniqid(); // Random value.
        $newLastName = uniqid(); // Random value.
        $newDisplayName = uniqid(); // Random value.
        $userAuthData = new UserAuthenticationData(
            $user->auth_provider,
            $user->auth_provider_user_identifier,
            $user->email,
            $newFirstName,
            $newLastName,
            $newDisplayName
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->updateUserRecord->thenCallParent();
        \Phake::when($userIdentity)->findUserRecord($userAuthData)->thenReturn($user);
        
        // Pre-assert:
        $this->assertNotNull(
            $user,
            'This test requires an actual User from the fixture data.'
        );
        $this->assertNotEquals(
            $newFirstName,
            $user->first_name,
            'This test requires new first name that differs from the existing '
            . 'first name value of the User.'
        );
        $this->assertNotEquals(
            $newLastName,
            $user->last_name,
            'This test requires new last name that differs from the existing '
            . 'first name value of the User.'
        );
        $this->assertNotEquals(
            $newDisplayName,
            $user->display_name,
            'This test requires new display name that differs from the '
            . 'existing first name value of the User.'
        );
        
        // Act:
        \Phake::makeVisible($userIdentity)->updateUserRecord($userAuthData);
        $user->refresh(); // Update our local instance to match the database.
        
        // Assert:
        $this->assertEquals(
            $newFirstName,
            $user->first_name,
            'Failed to update the first name.'
        );
        $this->assertEquals(
            $newLastName,
            $user->last_name,
            'Failed to update the last name.'
        );
        $this->assertEquals(
            $newDisplayName,
            $user->display_name,
            'Failed to update the display name.'
        );
    }
    
    public function testWarnIfEmailIsInUseByDiffAuthProvider_no()
    {
        // Arrange:
        $unusedEmailAddress = sprintf(
            'test_unused_%s@example.org',
            microtime(true)
        );
        $userAuthData = \Phake::mock('\Sil\DevPortal\components\UserAuthenticationData');
        \Phake::when($userAuthData)->getEmailAddress->thenReturn(
            $unusedEmailAddress
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->warnIfEmailIsInUseByDiffAuthProvider->thenCallParent();
        
        // Act:
        \Phake::makeVisible($userIdentity)->warnIfEmailIsInUseByDiffAuthProvider(
            $userAuthData
        );
        
        // Assert:
        \Phake::verify($userAuthData, \Phake::never())->getAuthProvider;
    }
    
    public function testWarnIfEmailIsInUseByDiffAuthProvider_yes()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userFromIdpOne');
        $userAuthData = new UserAuthenticationData(
            'Google',
            uniqid(),
            $user->email,
            $user->first_name,
            $user->last_name,
            $user->display_name
        );
        $userIdentity = \Phake::mock('Sil\DevPortal\components\UserIdentity');
        \Phake::when($userIdentity)->warnIfEmailIsInUseByDiffAuthProvider->thenCallParent();
        
        // Pre-assert:
        $this->setExpectedException(
            'Sil\DevPortal\components\WrongAuthProviderException'
        );
        
        // Act:
        \Phake::makeVisible($userIdentity)->warnIfEmailIsInUseByDiffAuthProvider(
            $userAuthData
        );
        
        // Assert: (n/a)
    }
}
