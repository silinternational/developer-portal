<?php

class KeyRequestTest extends DeveloperPortalTestCase
{
    public $fixtures = array(
        'apis' => 'Api',
        'keys' => 'Key',
        'keyRequests' => 'KeyRequest',
        'users' => 'User',
    );
    
    public function testConfirmStatusesDiffer()
    {
        // Make sure the status constants have different values. (There aren't
        // any separate user-friendly versions of the constants to check).
        $this->confirmConstantsDiffer('KeyRequest', 'STATUS_');
    }
    
    public function testGetStyledStatusHtml_approved()
    {
        // Arrange:
        $keyRequest = new KeyRequest;
        $keyRequest->status = \KeyRequest::STATUS_APPROVED;
        
        // Act:
        $result = $keyRequest->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for an approved KeyRequest's status."
        );
    }
    
    public function testGetStyledStatusHtml_denied()
    {
        // Arrange:
        $keyRequest = new KeyRequest;
        $keyRequest->status = \KeyRequest::STATUS_DENIED;
        
        // Act:
        $result = $keyRequest->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for a denied KeyRequest's status."
        );
    }
    
    public function testGetStyledStatusHtml_pending()
    {
        // Arrange:
        $keyRequest = new KeyRequest;
        $keyRequest->status = \KeyRequest::STATUS_PENDING;
        
        // Act:
        $result = $keyRequest->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for a pending KeyRequest's status."
        );
    }
    
    public function testGetStyledStatusHtml_revoked()
    {
        // Arrange:
        $keyRequest = new KeyRequest;
        $keyRequest->status = \KeyRequest::STATUS_REVOKED;
        
        // Act:
        $result = $keyRequest->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            "Failed to return any content for a revoked KeyRequest's status."
        );
    }
    
    public function testGetStyledStatusHtml_unknown()
    {
        // Arrange:
        $keyRequest = new KeyRequest;
        $keyRequest->status = 'fake-status';
        
        // Act:
        $result = $keyRequest->getStyledStatusHtml();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            'Failed to return any content for a KeyRequest with an unknown '
            . 'status.'
        );
    }
    
    public function testGetStyledStatusHtml_resultDiffer()
    {
        // Arrange:
        $keyRequest = new KeyRequest;
        $results = array();
        
        // Act:
        $keyRequest->status = \KeyRequest::STATUS_APPROVED;
        $results[] = $keyRequest->getStyledStatusHtml();
        $keyRequest->status = \KeyRequest::STATUS_DENIED;
        $results[] = $keyRequest->getStyledStatusHtml();
        $keyRequest->status = \KeyRequest::STATUS_PENDING;
        $results[] = $keyRequest->getStyledStatusHtml();
        $keyRequest->status = \KeyRequest::STATUS_REVOKED;
        $results[] = $keyRequest->getStyledStatusHtml();
        $keyRequest->status = 'fake-status';
        $results[] = $keyRequest->getStyledStatusHtml();
        
        // Assert:
        $this->assertTrue(
            self::ArrayValuesAreUnique($results),
            'Failed to return unique HTML for each type of KeyRequest status.'
        );
    }
    
    public function testGetValidStatusValues_isCompleteList()
    {
        // Arrange:
        $allStatusConstantsByKey = self::getConstantsWithPrefix(
            'KeyRequest',
            'STATUS_'
        );
        $allStatusValues = array_values($allStatusConstantsByKey);
        
        // Act:
        $actual = \KeyRequest::getValidStatusValues();
        
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
        $this->assertClassHasRelation(new KeyRequest(), 'api', 'Api');
    }
    
	public function testHasKeyRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new KeyRequest(), 'key', 'Key');
    }
    
	public function testHasProcessedByRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new KeyRequest(), 'processedBy', 'User');
    }
    
	public function testHasUserRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new KeyRequest(), 'user', 'User');
    }
    
    public function testIsForApiOwnedBy_no()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('userThatDoesNotOwnAnyApis');
        
        // Act:
        $result = $keyRequest->isForApiOwnedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a Key Request IS for an API owned by a '
            . 'particular user (who is NOT the owner).'
        );
    }
    
    public function testIsForApiOwnedBy_nullUser()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        
        // Act:
        $result = $keyRequest->isForApiOwnedBy(null);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a Key Request is for an API owned by a'
            . ' null user.'
        );
    }
    
    public function testIsForApiOwnedBy_yes()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $keyRequest->isForApiOwnedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a Key Request IS for an API owned by the '
            . 'given user.'
        );
    }
    
    public function testIsForApiOwnedBy_apiWithoutOwner()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('keyRequestForApiWithoutOwner');
        $user = $this->users('user18');
        
        // Act:
        $result = $keyRequest->isForApiOwnedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a Key Request is for an API owned by a '
            . 'particular user even though the API has no owner.'
        );
    }
    
    public function testIsOwnedBy_no()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('approvedKeyRequestUser4');
        $user = $this->users('userWithNoKeyRequests');
        
        // Act:
        $result = $keyRequest->isOwnedBy($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a KeyRequest IS owned by a particular '
            . 'User (who is NOT the owner).'
        );
    }
    
    public function testIsOwnedBy_nullUser()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('approvedKeyRequestUser4');
        
        // Act:
        $result = $keyRequest->isOwnedBy(null);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a key request is owned by a null User.'
        );
    }
    
    public function testIsOwnedBy_yes()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('approvedKeyRequestUser4');
        $user = $this->users('userWithApprovedKeyRequest');
        
        // Act:
        $result = $keyRequest->isOwnedBy($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a key request IS owned by a particular user.'
        );
    }
    
    public function testNotifyApiOwnerOfPendingRequest_sendIsCalled()
    {
        /**************************** Arrange: ********************************/
        
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
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
        $keyRequest->notifyApiOwnerOfPendingRequest(
            $mockMailer,
            $appParams
        );
        
        /***************************** Assert: ********************************/
        
        // NOTE: If the YiiMailer->send() method was not called, the test will
        //       fail.
    }
    
    public function testNotifyUserOfDeniedKeyRequest_sendIsCalled()
    {
        /**************************** Arrange: ********************************/
        
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
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
        $keyRequest->notifyUserOfDeniedKeyRequest(
            $mockMailer,
            $appParams
        );
        
        /***************************** Assert: ********************************/
        
        // NOTE: If the YiiMailer->send() method was not called, the test will
        //       fail.
    }
    
    public function testSendKeyRequestDeletionNotification_sendIsCalled()
    {
        /**************************** Arrange: ********************************/
        
        $keyRequest = $this->keyRequests('deniedKeyRequestUser5');
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
        $keyRequest->sendKeyRequestDeletionNotification(
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
        $newKeyRequest = new KeyRequest;
        $newKeyRequest->api_id = $existingKey->api_id;
        $newKeyRequest->user_id = $existingKey->user_id;
        $newKeyRequest->purpose = 'Unit testing';
        $newKeyRequest->domain = 'local';
        $newKeyRequest->status = \KeyRequest::STATUS_PENDING;
        
        // Act:
        $result = $newKeyRequest->validate();
        
        // Assert:
        $this->assertNotEmpty($newKeyRequest->errors);
        $this->assertFalse(
            $result,
            'Incorrectly allowed a new KeyRequest for an Api that the User '
            . 'already has an active Key to.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_hasPendingKeyRequest()
    {
        // Arrange:
        /* @var $existingKeyRequest KeyRequest */
        $existingKeyRequest = $this->keyRequests('pendingKeyRequestUser6');
        $newKeyRequest = new KeyRequest;
        $newKeyRequest->api_id = $existingKeyRequest->api_id;
        $newKeyRequest->user_id = $existingKeyRequest->user_id;
        $newKeyRequest->purpose = 'Unit testing';
        $newKeyRequest->domain = 'local';
        $newKeyRequest->status = \KeyRequest::STATUS_PENDING;
        
        // Act:
        $result = $newKeyRequest->validate();
        
        // Assert:
        $this->assertNotEmpty($newKeyRequest->errors);
        $this->assertFalse(
            $result,
            'Incorrectly allowed a new KeyRequest for an Api that the User '
            . 'already has a pending KeyRequest for.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_hasDeniedKeyRequest()
    {
        // Arrange:
        /* @var $existingKeyRequest KeyRequest */
        $existingKeyRequest = $this->keyRequests('deniedKeyRequestUser5');
        $newKeyRequest = new KeyRequest;
        $newKeyRequest->api_id = $existingKeyRequest->api_id;
        $newKeyRequest->user_id = $existingKeyRequest->user_id;
        $newKeyRequest->purpose = 'Unit testing';
        $newKeyRequest->domain = 'local';
        $newKeyRequest->status = \KeyRequest::STATUS_PENDING;
        
        // Act:
        $result = $newKeyRequest->validate();
        
        // Assert:
        $this->assertEquals(array(), $newKeyRequest->errors);
        $this->assertTrue(
            $result,
            'Failed to allow a new KeyRequest for an Api that the User only '
            . 'has a denied KeyRequest for.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_hasRevokedKeyRequest()
    {
        // Arrange:
        /* @var $existingKeyRequest KeyRequest */
        $existingKeyRequest = $this->keyRequests('revokedKeyRequestUser7');
        $newKeyRequest = new KeyRequest;
        $newKeyRequest->api_id = $existingKeyRequest->api_id;
        $newKeyRequest->user_id = $existingKeyRequest->user_id;
        $newKeyRequest->purpose = 'Unit testing';
        $newKeyRequest->domain = 'local';
        $newKeyRequest->status = \KeyRequest::STATUS_PENDING;
        
        // Act:
        $result = $newKeyRequest->validate();
        
        // Assert:
        $this->assertEquals(array(), $newKeyRequest->errors);
        $this->assertTrue(
            $result,
            'Failed to allow a new KeyRequest for an Api that the User only '
            . 'has a revoked KeyRequest for.'
        );
    }
    
    public function testOnlyAllowOneKeyPerApi_noKeyOrKeyRequest()
    {
        // Arrange:
        $api = $this->apis('apiWithZeroKeys');
        $user = $this->users('userWithNoKeyRequests');
        $newKeyRequest = new KeyRequest;
        $newKeyRequest->api_id = $api->api_id;
        $newKeyRequest->user_id = $user->user_id;
        $newKeyRequest->purpose = 'Unit testing';
        $newKeyRequest->domain = 'local';
        $newKeyRequest->status = \KeyRequest::STATUS_PENDING;
        
        // Act:
        $result = $newKeyRequest->validate();
        
        // Assert:
        $this->assertEquals(array(), $newKeyRequest->errors);
        $this->assertTrue(
            $result,
            'Failed to allow a new KeyRequest for an Api that the User has '
            . 'neither any Key to nor any pending KeyRequest for.'
        );
    }
    
    // TODO: Set up more unit tests.
}
