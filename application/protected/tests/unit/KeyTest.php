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
    
    public function testCreateKeyBadApi() 
    {
        $this->deleteKeys();
        $results = Key::createKey(999, 1, 1);
        $this->assertFalse($results[0], 'Accepted bad request');
        $this->assertStringStartsWith('No Api found with api_id ', $results[1],
                'Unexpected error message');
    }
    
    public function testCreateKeyBadUser() 
    {        
        $this->deleteKeys();
        $results = Key::createKey(1,999, 1);
        $this->assertFalse($results[0], 'Accepted bad request');
        $this->assertStringStartsWith('No User found with user_id ',
                $results[1], 'Unexpected error message');
    }
    
    public function testCreateKey() 
    {
        $this->deleteKeys();
        $results = Key::createKey(1,1,1);
        $this->assertTrue($results[0], 'Rejected good request with: '.!is_null($results[1]) && is_string($results[1]) ? $results[1] : 'error');
        $this->assertEquals($results[1]->api_id, 1, 'Created with wrong api_id');
        $this->assertEquals($results[1]->user_id, 1, 'Created with wrong user_id');
        $this->assertEquals($results[1]->key_request_id, 1, 
                    'Key has wrong key_request_id');
    }
    
	public function testHasApiRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Key(), 'api', 'Api');
    }
    
	public function testHasKeyRequestRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Key(), 'keyRequest', 'KeyRequest');
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
        $user = $this->users('userWithNoKeyRequests');
        
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
        $user = $this->users('userWithNoKeyRequests');
        
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
        $this->assertNotEquals($key->value, $results[1]->value,
                'Key value failed to change when reset');
        $this->assertNotEquals($key->secret, $results[1]->secret,
                'Key secret failed to change when reset');
        $this->assertNotEquals($key->updated, $results[1]->updated,
                'Key updated datetime failed to change when reset');
        
        // Make sure the attributes of the key that should NOT have changed
        // DIDN'T change.
        $this->assertEquals($key->key_id, $results[1]->key_id,
                'Key has wrong key_id');
        $this->assertEquals($key->user_id, $results[1]->user_id,
                'Key has wrong user_id');
        $this->assertEquals($key->api_id, $results[1]->api_id,
                'Key has wrong api_id');
        $this->assertEquals($key->queries_second, $results[1]->queries_second,
                'Key has wrong queries_second');
        $this->assertEquals($key->queries_day, $results[1]->queries_day,
                'Key has wrong queries_day');
        $this->assertEquals($key->created, $results[1]->created,
                'Key has wrong created datetime');
        $this->assertEquals($key->key_request_id, $results[1]->key_request_id,
                'Key has wrong key_request_id');
    }
    
    public function testRevokeKey_ensureRelatedKeyRequestStatusSetToRevoked()
    {
        // Arrange:
        $key = $this->keys('approvedKey');
        $keyRequestId = $key->key_request_id;
        $this->assertNotNull(
            $keyRequestId,
            'Given key (from fixture) does not specify a key_request_id, so '
            . 'this test will not be able to run.'
        );
        
        // Act:
        \Key::revokeKey($key->key_id);
        $keyRequest = \KeyRequest::model()->findByPk($keyRequestId);
        $this->assertNotNull(
            $keyRequest,
            'Failed to find the key request associated with the revoked key, '
            . 'so this test cannot run.'
        );
        
        // Assert:
        $this->assertEquals(
            \KeyRequest::STATUS_REVOKED,
            $keyRequest->status,
            'Failed to set status of key request to revoked when revoking the '
            . 'related key.'
        );
    }
}

