<?php

class KeyTest extends DeveloperPortalTestCase
{
    public $fixtures = array(
        'apis'        => 'Api',
        'users'       => 'User',
        'keys'        => 'Key',
    );  
    
    public function deleteKeys()
    {
        $keys = Key::model()->findAll();
        foreach ($keys as $key) {
            $key->delete();  
        }            
    }
  
    public function setUp()
    {
        global $ENABLE_AXLE;
        if(!isset($ENABLE_AXLE) || $ENABLE_AXLE == true){
            $ENABLE_AXLE = false;
        }
        Yii::app()->user->id = 1;
        parent::setUp();       
    }
    
    public function testFixtureDataValidity()
    {
        foreach ($this->keys as $fixtureName => $fixtureData) {
            /* @var $key \Key */
            $key = $this->keys($fixtureName);
            $this->assertTrue($key->delete(), sprintf(
                'Could not delete key fixture %s: %s',
                $fixtureName,
                print_r($key->getErrors(), true)
            ));
            $keyOnInsert = new \Key();
            $keyOnInsert->setAttributes($fixtureData, false);
            $this->assertTrue($keyOnInsert->save(), sprintf(
                'Key fixture "%s" (ID %s) does not have valid data: %s',
                $fixtureName,
                $keyOnInsert->key_id,
                var_export($keyOnInsert->getErrors(), true)
            ));
        }
    }
    
    public function testApprove_alreadyApprovedKey()
    {
        // Arrange:
        $key = $this->keys('approvedKey');
        $approvingUser = $key->api->owner;
        
        // Act:
        $result = $key->approve($approvingUser);

        // Assert:
        $this->assertFalse($result, 'Incorrectly approved an already-approved key.');
    }
    
    public function testApprove_deniedKey()
    {
        // Arrange:
        $key = $this->keys('deniedKeyUser5');
        $approvingUser = $key->api->owner;
        
        // Act:
        $result = $key->approve($approvingUser);

        // Assert:
        $this->assertFalse($result, 'Failed to reject approval of a denied key.');
    }
    
    public function testApprove_revokedKey()
    {
        // Arrange:
        $key = $this->keys('revokedKeyUser7');
        $approvingUser = $key->api->owner;
        
        // Act:
        $result = $key->approve($approvingUser);

        // Assert:
        $this->assertFalse($result, 'Failed to reject approval of a revoked key.');
    }
    
    public function testApprove_requiresApproval_noUserGiven()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyToPublicApiThatRequiresApproval');
        $approvingUser = null;
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_PENDING,
            $key->status,
            'This test requires a pending key.'
        );
        $this->setExpectedException('\Exception', 'No User provided', 1465926569);
        
        // Act:
        $key->approve($approvingUser);
    }
    
    public function testApprove_requiresApproval_unauthorizedUser()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyToPublicApiThatRequiresApproval');
        $approvingUser = $this->users('userThatDoesNotOwnAnyApis');
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_PENDING,
            $key->status,
            'This test requires a pending key.'
        );
        $this->assertNotSame(
            $key->api->owner_id,
            $approvingUser->user_id,
            'This test requires the User that does NOT own the API that the Key is for.'
        );
        
        // Act:
        $result = $key->approve($approvingUser);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly allowed an unauthorized user (with role of owner) to approve a Key to an Api that requires '
            . 'approval.'
        );
        $this->assertNotEmpty(
            $key->errors,
            'Failed to set error message when an unauthorized User tried to approve a Key.'
        );
        $key->refresh();
        $this->assertNotSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'Incorrectly set the Key as approved.'
        );
    }
    
    public function testApprove_autoApproval()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyToPublicApiThatAutoApprovesKeys');
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_PENDING,
            $key->status,
            'This test requires a pending key.'
        );
        
        // Act:
        $result = $key->approve();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to approve a Key to an Api that auto-approves Keys.'
        );
        $key->refresh();
        $this->assertSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'Failed to set the Key as approved.'
        );
    }
    
    public function testApprove_requiresApproval_authorizedUser()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyToPublicApiThatRequiresApproval');
        $approvingUser = $this->users('userWithRoleOfOwner');
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_PENDING,
            $key->status,
            'This test requires a pending key.'
        );
        $this->assertSame(
            $key->api->owner_id,
            $approvingUser->user_id,
            'This test requires the User that owns the API that the Key is for.'
        );
        
        // Act:
        $result = $key->approve($approvingUser);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to allow the owner of an Api (which requires approval) to approve a Key for it.'
        );
        $key->refresh();
        $this->assertSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'Failed to set the Key as approved.'
        );
        $this->assertSame(
            $approvingUser->user_id,
            $key->processed_by,
            'Failed to record that the Key was processed by that approving User.'
        );
    }
    
    public function testCanBeDeletedBy_nullUser()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('deniedKeyUser5');
        $user = null;
        
        // Act:
        $result = $key->canBeDeletedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a null user could delete a denied Key.'
        );
    }
    
    public function testCanBeDeletedBy_ownApprovedKey()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('approvedKey');
        /* @var $user \User */
        $user = $this->users('userWithApprovedKey');
        
        // Pre-assert:
        $this->assertTrue(
            $key->isOwnedBy($user),
            'This test requires a Key owned by the given User.'
        );
        $this->assertEquals(
            \Key::STATUS_APPROVED,
            $key->status,
            'This test requires an approved Key.'
        );
        
        // Act:
        $result = $key->canBeDeletedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User could delete their own approved Key.'
        );
    }
    
    public function testCanBeDeletedBy_ownDeniedKey()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('deniedKeyUser5');
        /* @var $user \User */
        $user = $this->users('userWithDeniedKey');
        
        // Pre-assert:
        $this->assertTrue(
            $key->isOwnedBy($user),
            'This test requires a Key owned by the given User.'
        );
        $this->assertEquals(
            \Key::STATUS_DENIED,
            $key->status,
            'This test requires a denied Key.'
        );
        
        // Act:
        $result = $key->canBeDeletedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User could delete their own denied Key.'
        );
    }
    
    public function testCanBeDeletedBy_ownPendingKey()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyUser6');
        /* @var $user \User */
        $user = $this->users('userWithPendingKey');
        
        // Pre-assert:
        $this->assertTrue(
            $key->isOwnedBy($user),
            'This test requires a Key owned by the given User.'
        );
        $this->assertEquals(
            \Key::STATUS_PENDING,
            $key->status,
            'This test requires a pending Key.'
        );
        
        // Act:
        $result = $key->canBeDeletedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User could delete their own pending Key.'
        );
    }
    
    public function testCanBeDeletedBy_ownRevokedKey()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('revokedKeyUser7');
        /* @var $user \User */
        $user = $this->users('userWithRevokedKey');
        
        // Pre-assert:
        $this->assertTrue(
            $key->isOwnedBy($user),
            'This test requires a Key owned by the given User.'
        );
        $this->assertEquals(
            \Key::STATUS_REVOKED,
            $key->status,
            'This test requires a revoked Key.'
        );
        
        // Act:
        $result = $key->canBeDeletedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User could delete their own revoked Key.'
        );
    }
    
    public function testCanBeDeletedBy()
    {
        $this->markTestIncomplete('Tests not yet written.');
    }
    
    public function testConfirmStatusesDiffer()
    {
        // Make sure the status constants have different values. (There aren't
        // any separate user-friendly versions of the constants to check).
        $this->confirmConstantsDiffer('Key', 'STATUS_');
    }
    
    public function testGetActiveKeysDataProvider_onlyIncludesActiveKeys()
    {
        // Arrange: (n/a)
        
        // Act:
        $activeKeysDataProvider = \Key::getActiveKeysDataProvider();
        
        // Assert:
        foreach ($activeKeysDataProvider->getData() as $key) {
            /* @var $key \Key */
            $this->assertSame($key->status, \Key::STATUS_APPROVED);
        }
    }
    
    public function testGetActiveKeysDataProvider_includesAllActiveKeys()
    {
        // Arrange:
        $keysFixtureData = $this->keys;
        
        // Act:
        $activeKeysDataProvider = \Key::getActiveKeysDataProvider();
        
        // Assert:
        $activeKeysFromDP = $activeKeysDataProvider->getData();
        $activeKeyIdsFromDP = array();
        foreach ($activeKeysFromDP as $activeKeyFromDP) {
            $activeKeyIdsFromDP[] = $activeKeyFromDP->key_id;
        }
        foreach ($keysFixtureData as $fixtureName => $fixtureData) {
            if ($fixtureData['status'] === \Key::STATUS_APPROVED) {
                $key = $this->keys($fixtureName);
                $this->assertContains($key->key_id, $activeKeyIdsFromDP);
            }
        }
    }
    
    public function testGetChangesForLog()
    {
        $this->markTestIncomplete('Test(s) not yet written.');
    }
    
    public function testGetPendingKeysDataProvider_onlyIncludesPendingKeys()
    {
        // Arrange: (n/a)
        
        // Act:
        $pendingKeysDataProvider = \Key::getPendingKeysDataProvider();
        
        // Assert:
        foreach ($pendingKeysDataProvider->getData() as $key) {
            /* @var $key \Key */
            $this->assertSame($key->status, \Key::STATUS_PENDING);
        }
    }
    
    public function testGetPendingKeysDataProvider_includesAllPendingKeys()
    {
        // Arrange:
        $keysFixtureData = $this->keys;
        
        // Act:
        $pendingKeysDataProvider = \Key::getPendingKeysDataProvider();
        
        // Assert:
        $pendingKeysFromDP = $pendingKeysDataProvider->getData();
        $pendingKeyIdsFromDP = array();
        foreach ($pendingKeysFromDP as $pendingKeyFromDP) {
            $pendingKeyIdsFromDP[] = $pendingKeyFromDP->key_id;
        }
        foreach ($keysFixtureData as $fixtureName => $fixtureData) {
            if ($fixtureData['status'] === \Key::STATUS_PENDING) {
                $key = $this->keys($fixtureName);
                $this->assertContains($key->key_id, $pendingKeyIdsFromDP);
            }
        }
    }
    
    public function testGetStyledStatusHtml_approved()
    {
        // Arrange:
        $key = new Key();
        $key->status = \Key::STATUS_APPROVED;
        
        // Act:
        $result = $key->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for an approved Key's status."
        );
    }
    
    public function testGetStyledStatusHtml_denied()
    {
        // Arrange:
        $key = new Key();
        $key->status = \Key::STATUS_DENIED;
        
        // Act:
        $result = $key->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for a denied Key's status."
        );
    }
    
    public function testGetStyledStatusHtml_pending()
    {
        // Arrange:
        $key = new Key();
        $key->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $key->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for a pending Key's status."
        );
    }
    
    public function testGetStyledStatusHtml_revoked()
    {
        // Arrange:
        $key = new Key();
        $key->status = \Key::STATUS_REVOKED;
        
        // Act:
        $result = $key->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for a revoked Key's status."
        );
    }
    
    public function testGetStyledStatusHtml_unknown()
    {
        // Arrange:
        $key = new Key();
        $key->status = 'fake-status';
        
        // Act:
        $result = $key->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            'Failed to return any content for a Key with an unknown '
            . 'status.'
        );
    }
    
    public function testGetStyledStatusHtml_resultDiffer()
    {
        // Arrange:
        $key = new Key();
        $results = array();
        
        // Act:
        $key->status = \Key::STATUS_APPROVED;
        $results[] = $key->getStyledStatusHtml();
        $key->status = \Key::STATUS_DENIED;
        $results[] = $key->getStyledStatusHtml();
        $key->status = \Key::STATUS_PENDING;
        $results[] = $key->getStyledStatusHtml();
        $key->status = \Key::STATUS_REVOKED;
        $results[] = $key->getStyledStatusHtml();
        $key->status = 'fake-status';
        $results[] = $key->getStyledStatusHtml();
        
        // Assert:
        $this->assertTrue(
            self::ArrayValuesAreUnique($results),
            'Failed to return unique HTML for each type of Key status.'
        );
    }
    
    public function testGetValidStatusValues_isCompleteList()
    {
        // Arrange:
        $allStatusConstantsByKey = self::getConstantsWithPrefix(
            'Key',
            'STATUS_'
        );
        $allStatusValues = array_values($allStatusConstantsByKey);
        
        // Act:
        $actual = \Key::getValidStatusValues();
        
        // Assert:
        $this->assertEquals(
            $allStatusValues,
            $actual,
            'Failed to retrieve the correct list of valid status values.'
        );
    }
    
	public function testHasApiRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Key(), 'api', 'Api');
    }
    
	public function testHasProcessedByRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Key(), 'processedBy', 'User');
    }
    
	public function testHasUserRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Key(), 'user', 'User');
    }
    
    public function testIsToApiOwnedBy_no()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userThatDoesNotOwnAnyApis');
        
        // Act:
        $result = $key->isToApiOwnedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a key is to an API owned by a '
            . 'particular user.'
        );
    }
    
    public function testIsToApiOwnedBy_nullUser()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        
        // Act:
        $result = $key->isToApiOwnedBy(null);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a key is to an API owned by a null user.'
        );
    }
    
    public function testIsToApiOwnedBy_yes()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $key->isToApiOwnedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a key IS to an API owned by a particular '
            . 'user.'
        );
    }
    
    public function testIsToApiOwnedBy_apiWithoutOwner()
    {
        // Arrange:
        $key = $this->keys('keyToApiWithoutOwner');
        $user = $this->users('user18');
        
        // Act:
        $result = $key->isToApiOwnedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a key is to an API owned by a '
            . 'particular user even though the API has no owner.'
        );
    }
    
    public function testIsOwnedBy_no()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userWithNoPendingKeys');
        
        // Act:
        $result = $key->isOwnedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to report that a key is NOT owned by a particular user.'
        );
    }
    
    public function testIsOwnedBy_nullUser()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        
        // Act:
        $result = $key->isOwnedBy(null);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a key is owned by a null User.'
        );
    }
    
    public function testIsOwnedBy_yes()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userWithKeyToApiOwnedByUser18');
        
        // Act:
        $result = $key->isOwnedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a key IS owned by a particular user.'
        );
    }
    
    public function testIsVisibleToUser_admin()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $key->isVisibleToUser($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a key should be visible to an Admin user.'
        );
    }
    
    public function testIsVisibleToUser_developerWithKey()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userWithKeyToApiOwnedByUser18');
        
        // Act:
        $result = $key->isVisibleToUser($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a key should be visible to the user that it '
            . 'belongs to.'
        );
    }
    
    public function testIsVisibleToUser_developerWithoutKey()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userWithNoPendingKeys');
        
        // Act:
        $result = $key->isVisibleToUser($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to report that a key should NOT be visible to a developer '
            . 'that it does NOT belong to.'
        );
    }
    
    public function testIsVisibleToUser_ownerOfTheApi()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $key->isVisibleToUser($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a particular key should be visible to the '
            . 'owner of the corresponding API.'
        );
    }
    
    public function testIsVisibleToUser_ownerButNotOfTheApi()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $result = $key->isVisibleToUser($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to report that a particular key should NOT be visible to a '
            . 'user (with role of owner) who does NOT own the API that the Key '
            . 'is to.'
        );
    }
    
    public function testIsVisibleToUser_nullUser()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        
        // Act:
        $result = $key->isVisibleToUser(null);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a key is visible to a null user.'
        );
    }
    
    public function testLog()
    {
        $this->markTestIncomplete('Test(s) not yet written.');
    }
    
    public function testNotifyApiOwnerOfPendingRequest_sendIsCalled()
    {
        /**************************** Arrange: ********************************/
        
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $appParams = array(
            'mail' => array(),
        );
        
        // Create a mock for the YiiMailer class, only mocking the send()
        // method.
        $mockMailer = $this->getMock('YiiMailer', array('send'));

        // Set up the expectation for the send() method to be called only once.
        $mockMailer->expects($this->once())
                   ->method('send');
        
        /****************************** Act: **********************************/
        $key->notifyApiOwnerOfPendingRequest(
            $mockMailer,
            $appParams
        );
        
        /***************************** Assert: ********************************/
        
        // NOTE: If the YiiMailer->send() method was not called, the test will
        //       fail.
    }
    
    public function testNotifyApiOwnerOfRevokedKey()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testNotifyUserOfApprovedKey()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testNotifyUserOfDeletedKey()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testNotifyUserOfDeniedKey()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testNotifyUserOfRevokedKey()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testNotifyUserOfDeniedKey_sendIsCalled()
    {
        /**************************** Arrange: ********************************/
        
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $appParams = array(
            'mail' => array(),
        );
        
        // Create a mock for the YiiMailer class, only mocking the send()
        // method.
        $mockMailer = $this->getMock('YiiMailer', array('send'));

        // Set up the expectation for the send() method to be called only once.
        $mockMailer->expects($this->once())
                   ->method('send');
        
        /****************************** Act: **********************************/
        $key->notifyUserOfDeniedKey(
            $mockMailer,
            $appParams
        );
        
        /***************************** Assert: ********************************/
        
        // NOTE: If the YiiMailer->send() method was not called, the test will
        //       fail.
    }
    
    public function testOnlyAllowOneKeyPerApi_hasActiveKey()
    {
        // Arrange:
        /* @var $existingKey Key */
        $existingKey = $this->keys('approvedKey');
        $newKey = new Key();
        $newKey->api_id = $existingKey->api_id;
        $newKey->user_id = $existingKey->user_id;
        $newKey->purpose = 'Unit testing';
        $newKey->domain = 'local';
        $newKey->queries_second = 10;
        $newKey->queries_day = 10000;
        $newKey->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $newKey->validate();
        
        // Assert:
        $this->assertNotEmpty($newKey->errors);
        $this->assertFalse(
            $result,
            'Incorrectly allowed a new Key for an Api that the User '
            . 'already has an active Key to.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_hasPendingKey()
    {
        // Arrange:
        /* @var $existingKey \Key */
        $existingKey = $this->keys('pendingKeyUser6');
        $newKey = new Key();
        $newKey->api_id = $existingKey->api_id;
        $newKey->user_id = $existingKey->user_id;
        $newKey->purpose = 'Unit testing';
        $newKey->domain = 'local';
        $newKey->queries_second = 10;
        $newKey->queries_day = 10000;
        $newKey->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $newKey->validate();
        
        // Assert:
        $this->assertNotEmpty($newKey->errors);
        $this->assertFalse(
            $result,
            'Incorrectly allowed a new Key for an Api that the User '
            . 'already has a pending Key for.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_hasDeniedKey()
    {
        // Arrange:
        /* @var $existingKey \Key */
        $existingKey = $this->keys('deniedKeyUser5');
        $newKey = new Key();
        $newKey->api_id = $existingKey->api_id;
        $newKey->user_id = $existingKey->user_id;
        $newKey->purpose = 'Unit testing';
        $newKey->domain = 'local';
        $newKey->queries_second = 10;
        $newKey->queries_day = 10000;
        $newKey->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $newKey->validate();
        
        // Assert:
        $this->assertEquals(
            array(),
            $newKey->errors,
            'Unexpected errors: ' . print_r($newKey->errors, true)
        );
        $this->assertTrue(
            $result,
            'Failed to allow a new Key for an Api that the User only '
            . 'has a denied Key for.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_hasRevokedKey()
    {
        // Arrange:
        /* @var $existingKey \Key */
        $existingKey = $this->keys('revokedKeyUser7');
        $newKey = new Key();
        $newKey->api_id = $existingKey->api_id;
        $newKey->user_id = $existingKey->user_id;
        $newKey->purpose = 'Unit testing';
        $newKey->domain = 'local';
        $newKey->queries_second = 10;
        $newKey->queries_day = 10000;
        $newKey->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $newKey->validate();
        
        // Assert:
        $this->assertEquals(
            array(),
            $newKey->errors,
            'Unexpected errors: ' . print_r($newKey->errors, true)
        );
        $this->assertTrue(
            $result,
            'Failed to allow a new Key for an Api that the User only '
            . 'has a revoked Key for.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_noKeyOrKey()
    {
        // Arrange:
        $api = $this->apis('apiWithZeroKeys');
        $user = $this->users('userWithNoPendingKeys');
        $newKey = new Key();
        $newKey->api_id = $api->api_id;
        $newKey->user_id = $user->user_id;
        $newKey->purpose = 'Unit testing';
        $newKey->domain = 'local';
        $newKey->queries_second = 10;
        $newKey->queries_day = 10000;
        $newKey->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $newKey->validate();
        
        // Assert:
        $this->assertEquals(
            array(),
            $newKey->errors,
            'Unexpected errors: ' . print_r($newKey->errors, true)
        );
        $this->assertTrue(
            $result,
            'Failed to allow a new Key for an Api that the User has '
            . 'neither any Key to nor any pending Key for.'
        );
    }
    
    public function testResetKeyBadKey() 
    {        
        $this->deleteKeys();
        $results = Key::resetKey(1);
        $this->assertFalse($results[0], 'Accepted bad request');
        $this->assertEquals($results[1], 'Bad key_id', 'Accepted bad key_id');
    }
    
    public function testRecordDateWhenProcessed_processedByIsSet()
    {
        // Arrange:
        $user = $this->users('user18');
        /* @var $key \Key */
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $key->processed_by = $user->user_id;
        
        // Pre-assert:
        $this->assertNull(
            $key->processed_on,
            'This test requires a Key with a processed_on value of null.'
        );
        $this->assertNotNull(
            $key->processed_by,
            'Failed to set processed_by as part of the setup for this test.'
        );
        
        // Act:
        $key->recordDateWhenProcessed('processed_on', null);
        
        // Assert:
        $this->assertNotNull(
            $key->processed_on,
            'Failed to set the processed_on value even though the processed_by value was set.'
        );
    }
    
    public function testRecordDateWhenProcessed_processedByNotSet()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        
        // Pre-assert:
        $this->assertNull(
            $key->processed_on,
            'This test requires a Key with a processed_on value of null.'
        );
        $this->assertNull(
            $key->processed_by,
            'This test requires a Key with a processed_by value of null.'
        );
        
        // Act:
        $key->recordDateWhenProcessed('processed_on', null);
        
        // Assert:
        $this->assertNull(
            $key->processed_on,
            'Incorrectly set the processed_on value even though the processed_by value was null.'
        );
    }
    
    public function testRecordDateWhenProcessed_processedOnNotChangedIfAlreadySet()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('keyToApiOwnedByUser18');
        $originalProcessedOnValue = $key->processed_on;
        
        // Pre-assert:
        $this->assertNotNull(
            $key->processed_on,
            'This test requires a Key with a non-null processed_on value.'
        );
        $this->assertNotNull(
            $key->processed_by,
            'This test requires a Key with a non-null processed_by value.'
        );
        
        // Act:
        $key->recordDateWhenProcessed('processed_on', null);
        
        // Assert:
        $this->assertEquals(
            $originalProcessedOnValue,
            $key->processed_on,
            'Incorrectly changed the processed_on value even though it was already set.'
        );
    }
    
    public function testRequiresApproval_no()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyToPublicApiThatRequiresApproval');
        
        // Act:
        $result = $key->requiresApproval();
        
        // Assert:
        $this->assertTrue($result);
    }
    
    public function testRequiresApproval_yes()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyToPublicApiThatAutoApprovesKeys');
        
        // Act:
        $result = $key->requiresApproval();
        
        // Assert:
        $this->assertFalse($result);
    }
    
    public function testResetKey() 
    {
        // Get the Key we added through our fixture.
        $key = $this->keys('key1');
        
        // Reset that Key.
        $results = Key::resetKey($key->key_id);
        
        // Make sure it succeeded.
        $this->assertTrue($results[0], 'Rejected good request');
        
        // Make sure the attributes of the key that SHOULD have changed DID.
        $this->assertNotEquals(
            $key->value,
            $results[1]->value,
            'Key value failed to change when reset'
        );
        $this->assertNotEquals(
            $key->secret,
            $results[1]->secret,
            'Key secret failed to change when reset'
        );
        $this->assertNotEquals(
            $key->updated,
            $results[1]->updated,
            'Key updated datetime failed to change when reset'
        );

        // Make sure the attributes of the key that should NOT have changed
        // DIDN'T change.
        $this->assertEquals(
            $key->key_id,
            $results[1]->key_id,
            'Key has wrong key_id'
        );
        $this->assertEquals(
            $key->user_id,
            $results[1]->user_id,
            'Key has wrong user_id'
        );
        $this->assertEquals(
            $key->api_id,
            $results[1]->api_id,
            'Key has wrong api_id'
        );
        $this->assertEquals(
            $key->created,
            $results[1]->created,
            'Key has wrong created datetime'
        );
        $this->assertEquals(
            $key->key_id,
            $results[1]->key_id,
            'Key has wrong key_id'
        );
    }
    
    public function testRevoke_ensureStatusSetToRevoked()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('approvedKey');
        
        // Act:
        $key->revoke($key->user);
        
        // Assert:
        $key->refresh();
        $this->assertEquals(
            \Key::STATUS_REVOKED,
            $key->status,
            'Failed to set status of the key to revoked when revoking it.'
        );
    }
    
    public function testRevoke_alreadyRevokedKey()
    {
        // Arrange:
        $key = $this->keys('revokedKeyUser7');
        $revokingUser = $key->api->owner;
        
        // Act:
        $result = $key->revoke($revokingUser);

        // Assert:
        $this->assertFalse($result, 'Incorrectly revoked an already-revoked key.');
    }
    
    public function testRevoke_deniedKey()
    {
        // Arrange:
        $key = $this->keys('deniedKeyUser5');
        $revokingUser = $key->api->owner;
        
        // Act:
        $result = $key->revoke($revokingUser);

        // Assert:
        $this->assertFalse($result, 'Failed to reject revocation of a denied key.');
    }
    
    public function testRevoke_pendingKey()
    {
        // Arrange:
        $key = $this->keys('pendingKeyUser6');
        $revokingUser = $key->api->owner;
        
        // Act:
        $result = $key->revoke($revokingUser);

        // Assert:
        $this->assertFalse($result, 'Failed to reject revocation of a pending key.');
    }
    
    public function testRevoke_noUserGiven()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('approvedKey');
        $revokingUser = null;
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'This test requires an approved key.'
        );
        $this->setExpectedException('\Exception', 'No User provided', 1466000163);
        
        // Act:
        $key->revoke($revokingUser);
    }
    
    public function testRevoke_unauthorizedUser()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('approvedKey');
        $revokingUser = $this->users('ownerThatDoesNotOwnAnyApisOrKeys');
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'This test requires an approved key.'
        );
        $this->assertNotSame(
            $key->api->owner_id,
            $revokingUser->user_id,
            'This test requires the User that does NOT own the API that the Key is for.'
        );
        $this->assertNotSame(
            $key->user_id,
            $revokingUser->user_id,
            'This test requires the User that does NOT own the Key being revoked.'
        );
        
        // Act:
        $result = $key->revoke($revokingUser);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly allowed an unauthorized user (with role of owner) to revoke a Key that they do not own to an '
            . 'Api that they do not own.'
        );
        $this->assertNotEmpty(
            $key->errors,
            'Failed to set error message when an unauthorized User tried to revoke a Key.'
        );
        $key->refresh();
        $this->assertNotSame(
            \Key::STATUS_REVOKED,
            $key->status,
            'Incorrectly set the Key as revoked.'
        );
    }
    
    public function testRevoke_byKeyOwner()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('approvedKey');
        $revokingUser = $key->user; // The owner of the Key.
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'This test requires an approved key.'
        );
        
        // Act:
        $result = $key->revoke($revokingUser);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to let a User revoke one of their own Keys.'
        );
        $key->refresh();
        $this->assertSame(
            \Key::STATUS_REVOKED,
            $key->status,
            'Failed to set the Key as revoked.'
        );
        $this->assertNotEmpty(
            $key->value,
            'Incorrectly removed the Key\'s value when revoking it.'
        );
        $this->assertEmpty(
            $key->secret,
            'Failed to remove the Key\'s secret when revoking it.'
        );
    }
    
    public function testRevoke_byApiOwner()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('approvedKey');
        $revokingUser = $key->api->owner; // The owner of the Api.
        
        // Pre-assert:
        $this->assertSame(
            \Key::STATUS_APPROVED,
            $key->status,
            'This test requires an approved key.'
        );
        $this->assertSame(
            $key->api->owner_id,
            $revokingUser->user_id,
            'This test requires the User that owns the API that the Key is for.'
        );
        
        // Act:
        $result = $key->revoke($revokingUser);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to allow the owner of an Api to revoke a Key for it.'
        );
        $key->refresh();
        $this->assertSame(
            \Key::STATUS_REVOKED,
            $key->status,
            'Failed to set the Key as revoked.'
        );
        $this->assertSame(
            $revokingUser->user_id,
            $key->processed_by,
            'Failed to record that the Key was processed by that revoking User.'
        );
        $this->assertNotEmpty(
            $key->value,
            'Incorrectly removed the Key\'s value when revoking it.'
        );
        $this->assertEmpty(
            $key->secret,
            'Failed to remove the Key\'s secret when revoking it.'
        );
    }
    
    public function testSendKeyDeletionNotification_sendIsCalled()
    {
        /**************************** Arrange: ********************************/
        
        $key = $this->keys('deniedKeyUser5');
        $appParams = array(
            'mail' => array(),
            'adminEmail' => 'email@domain.com',
        );
        
        // Create a mock for the YiiMailer class, only mocking the send()
        // method.
        $mockMailer = $this->getMock('YiiMailer', array('send'));

        // Set up the expectation for the send() method to be called only once.
        $mockMailer->expects($this->once())
                   ->method('send');
        
        /****************************** Act: **********************************/
        $key->sendKeyDeletionNotification(
            $mockMailer,
            $appParams
        );
        
        /***************************** Assert: ********************************/
        
        // NOTE: If the YiiMailer->send() method was not called, the test will
        //       fail.
    }
    
    public function testStatusHasValidValue_no_emptyString()
    {
        // Arrange:
        /* @var $key Key */
        $key = $this->keys('key1');
        $key->status = '';
        
        // Act:
        $result = $key->validate(array('status'));
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject a status of an empty string.'
        );
    }
    
    public function testStatusHasValidValue_no_null()
    {
        // Arrange:
        /* @var $key Key */
        $key = $this->keys('key1');
        $key->status = null;
        
        // Act:
        $result = $key->validate(array('status'));
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject a status of null.'
        );
    }
    
    public function testStatusHasValidValue_no_otherString()
    {
        // Arrange:
        /* @var $key Key */
        $key = $this->keys('key1');
        $key->status = 'asdf';
        
        // Act:
        $result = $key->validate(array('status'));
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject a made-up status.'
        );
    }
    
    public function testStatusHasValidValue_yes()
    {
        // Arrange:
        /* @var $key Key */
        $key = $this->keys('key1');
        $key->status = \Key::STATUS_PENDING;
        
        // Act:
        $result = $key->validate(array('status'));
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to accept a valid status value.'
        );
    }
    
    // TODO: Set up more unit tests.
}
