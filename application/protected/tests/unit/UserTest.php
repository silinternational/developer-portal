<?php

class UserTest extends DeveloperPortalTestCase
{
    public $fixtures = array(
        'apis' => 'Api',
        'keyRequests' => 'KeyRequest',
        'keys' => 'Key',
        'users' => 'User',
    );
    
    public function testApis()
    {
        // Arrange:
        $user = $this->users('userThatOwnsASingleApi');
        $api = $this->apis('apiWithOwner');
        $expected = array($api);
        
        // Act:
        $actual = $user->apis;
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return the correct set of APIs owned by a user.'
        );
        $this->assertEquals(
            1,
            count($actual),
            'Failed to return the correct number of APIs for a user.'
        );
    }

    public function testCanDeleteKeyRequest_nullKeyRequest()
    {
        // Arrange:
        $keyRequest = null;
        $user = $this->users('user1');
        
        // Act:
        $result = $user->canDeleteKeyRequest($keyRequest);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can delete a null Key Request.'
        );
    }

    public function testCanDeleteKeyRequest_ownKeyRequest()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKR1_apiWithTwoPendingKeyRequests');
        $user = $this->users('userWith1stPKRForApiWithTwoPendingKeyRequests');
        
        // Act:
        $result = $user->canDeleteKeyRequest($keyRequest);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can delete their own Key Request.'
        );
    }

    public function testCanDeleteKeyRequest_notOwnKeyRequest()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKR2_apiWithTwoPendingKeyRequests');
        $user = $this->users('userWith1stPKRForApiWithTwoPendingKeyRequests');
        
        // Act:
        $result = $user->canDeleteKeyRequest($keyRequest);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a normal user could delete a key '
            . 'request that was not theirs.'
        );
    }

    public function testCanDeleteKeyRequest_keyRequestForApiOwnedByUser()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canDeleteKeyRequest($keyRequest);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can delete a Key Request for an Api '
            . 'that they own.'
        );
    }

    public function testCanDeleteKeyRequest_keyRequestNotOwnedByUserToApiNotOwnedByUser()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKR1_apiWithTwoPendingKeyRequests');
        $user = $this->users('ownerThatDoesNotOwnAnyApisOrKeys');
        
        // Act:
        $result = $user->canDeleteKeyRequest($keyRequest);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a user with role "owner" could delete '
            . 'a Key Request that they do not own for an Api that they do not '
            . 'own.'
        );
    }

    public function testCanDeleteKeyRequest_adminUser()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKR1_apiWithTwoPendingKeyRequests');
        $user = $this->users('userWithRoleOfAdminButNoKeys');
        
        // Act:
        $result = $user->canDeleteKeyRequest($keyRequest);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that an admin User can delete any Key Request.'
        );
    }
    
    public function testCanResetKey_nullKey()
    {
        // Arrange:
        $key = null;
        $user = $this->users('user1');
        
        // Act:
        $result = $user->canResetKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can reset a null Key.'
        );
    }

    public function testCanResetKey_ownKey()
    {
        // Arrange:
        $key = $this->keys('firstKeyForApiWithTwoKeys');
        $user = $this->users('userWithFirstKeyForApiWithTwoKeys');
        
        // Act:
        $result = $user->canResetKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can reset their own Key.'
        );
    }

    public function testCanResetKey_notOwnKey()
    {
        // Arrange:
        $key = $this->keys('secondKeyForApiWithTwoKeys');
        $user = $this->users('userWithFirstKeyForApiWithTwoKeys');
        
        // Act:
        $result = $user->canResetKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a normal user could reset a Key that '
            . 'they do not own.'
        );
    }

    public function testCanResetKey_keyToApiOwnedByUser()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canResetKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can reset a Key to an Api that they '
            . 'own.'
        );
    }

    public function testCanResetKey_keyNotOwnedByUserToApiNotOwnedByUser()
    {
        // Arrange:
        $key = $this->keys('key1');
        $user = $this->users('ownerThatDoesNotOwnAnyApisOrKeys');
        
        // Act:
        $result = $user->canResetKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a user with role "owner" could reset '
            . 'a Key that they do not own to an Api that they do not own.'
        );
    }

    public function testCanResetKey_adminUser()
    {
        // Arrange:
        $key = $this->keys('key1');
        $user = $this->users('userWithRoleOfAdminButNoKeys');
        
        // Act:
        $result = $user->canResetKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that an admin User can reset any Key.'
        );
    }

    public function testCanRevokeKey_nullKey()
    {
        // Arrange:
        $key = null;
        $user = $this->users('user1');
        
        // Act:
        $result = $user->canRevokeKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can revoke a null Key.'
        );
    }

    public function testCanRevokeKey_ownKey()
    {
        // Arrange:
        $key = $this->keys('firstKeyForApiWithTwoKeys');
        $user = $this->users('userWithFirstKeyForApiWithTwoKeys');
        
        // Act:
        $result = $user->canRevokeKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can revoke their own Key.'
        );
    }

    public function testCanRevokeKey_notOwnKey()
    {
        // Arrange:
        $key = $this->keys('secondKeyForApiWithTwoKeys');
        $user = $this->users('userWithFirstKeyForApiWithTwoKeys');
        
        // Act:
        $result = $user->canRevokeKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a normal user could revoke a key that '
            . 'they do not own.'
        );
    }

    public function testCanRevokeKey_keyToApiOwnedByUser()
    {
        // Arrange:
        $key = $this->keys('keyToApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canRevokeKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can revoke a Key to an Api that they '
            . 'own.'
        );
    }

    public function testCanRevokeKey_keyNotOwnedByUserToApiNotOwnedByUser()
    {
        // Arrange:
        $key = $this->keys('key1');
        $user = $this->users('ownerThatDoesNotOwnAnyApisOrKeys');
        
        // Act:
        $result = $user->canRevokeKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a user with role "owner" could revoke '
            . 'a Key that they do not own to an Api that they do not own.'
        );
    }

    public function testCanRevokeKey_adminUser()
    {
        // Arrange:
        $key = $this->keys('key1');
        $user = $this->users('userWithRoleOfAdminButNoKeys');
        
        // Act:
        $result = $user->canRevokeKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that an admin User can revoke any Key.'
        );
    }
    
    public function testCanSeeKeyRequest_nullKeyRequest()
    {
        // Arrange:
        $keyRequest = null;
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->canSeeKeyRequest($keyRequest);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can see a null Key Request.'
        );
    }

    public function testCanSeeKeyRequest_adminUser()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestUser6');
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->canSeeKeyRequest($keyRequest);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Incorrectly reported that an admin User cannot see a particular '
            . 'KeyRequest.'
        );
    }

    public function testCanSeeKeyRequest_forOwnApi()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canSeeKeyRequest($keyRequest);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can see a KeyRequest for an API that '
            . 'the User owns.'
        );
    }

    public function testCanSeeKeyRequest_notForOwnApi()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $result = $user->canSeeKeyRequest($keyRequest);
        
        // Assert:
        $this->assertFalse(
            $result,
            "Incorrectly reported that a User can see someone else's "
            . "KeyRequest for an API that the User does NOT own."
        );
    }

    public function testCanSeeKeyRequest_ownKeyRequest()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('userWithPendingKeyRequestForApiOwnedByUser18');
        
        // Act:
        $result = $user->canSeeKeyRequest($keyRequest);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can see their own KeyRequest.'
        );
    }

    public function testCanSeeKeyRequest_notOwnKeyRequest()
    {
        // Arrange:
        $keyRequest = $this->keyRequests('pendingKeyRequestForApiOwnedByUser18');
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $result = $user->canSeeKeyRequest($keyRequest);
        
        // Assert:
        $this->assertFalse(
            $result,
            "Incorrectly reported that a (developer) User can see someone "
            . "else's KeyRequest."
        );
    }

    public function testCanSeeKeysForApi_nullApi()
    {
        // Arrange:
        $api = null;
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->canSeeKeysForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can see the Keys to a null Api.'
        );
    }

    public function testCanSeeKeysForApi_adminUser()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoKeys');
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->canSeeKeysForApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that an admin User can see Keys for any Api.'
        );
    }

    public function testCanSeeKeysForApi_ownApi()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canSeeKeysForApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can see the Keys to their own Api.'
        );
    }

    public function testCanSeeKeysForApi_notOwnApi()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $result = $user->canSeeKeysForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that an API Owner could see the Keys to an '
            . 'Api that they do not own.'
        );
    }

    public function testConfirmRolesDiffer()
    {
        // Make sure the role constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('User', 'ROLE_',
                User::getRoles());
    }
    
    public function testConfirmStatusesDiffer()
    {
        // Make sure the status constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('User', 'STATUS_',
                User::getStatuses());
    }
    
    public function testGetRoles_isCompleteList()
    {
        // Arrange:
        $constantPrefix = 'ROLE_';
        $rolesFound = self::getConstantsWithPrefix('User', $constantPrefix);
        $roleValuesFound = array_values($rolesFound);
        $this->assertTrue(
            sort($roleValuesFound),
            'Failed to sort the list of role values we found.'
        );
        
        // Act:
        $rolesReturned = \User::getRoles();
        $roleValuesReturned = array_keys($rolesReturned);
        $this->assertTrue(
            sort($roleValuesReturned),
            'Failed to sort the list of role values returned.'
        );
        
        // Assert:
        $this->assertEquals(
            $roleValuesFound,
            $roleValuesReturned,
            'The list returned by getRoles() does not match the list of User '
            . 'constants that start with "' . $constantPrefix . '".'
        );
    }
    
    public function testGetStatuses_isCompleteList()
    {
        // Arrange:
        $constantPrefix = 'STATUS_';
        $statusesFound = self::getConstantsWithPrefix('User', $constantPrefix);
        $statusValuesFound = array_values($statusesFound);
        $this->assertTrue(
            sort($statusValuesFound),
            'Failed to sort the list of status values we found.'
        );
        
        // Act:
        $statusesReturned = \User::getStatuses();
        $statusValuesReturned = array_keys($statusesReturned);
        $this->assertTrue(
            sort($statusValuesReturned),
            'Failed to sort the list of status values returned.'
        );
        
        // Assert:
        $this->assertEquals(
            $statusValuesFound,
            $statusValuesReturned,
            'The list returned by getStatuses() does not match the list of '
            . 'User constants that start with "' . $constantPrefix . '".'
        );
    }
    
    public function testGetKeyRequestsWithApiNames_none()
    {
        // Arrange:
        $user = $this->users('userWithNoKeyRequests');
        
        // Act:
        $keyRequests = $user->getKeyRequestsWithApiNames();
        
        // Assert:
        $this->assertEmpty(
            $keyRequests,
            'Failed to return an empty array for the key requests of a user '
            . 'that has no key requests.'
        );
    }
    
    public function testGetKeyRequestsWithApiNames_approved()
    {
        // Arrange:
        $user = $this->users('userWithApprovedKeyRequest');
        
        // Act:
        $keyRequests = $user->getKeyRequestsWithApiNames();
        $foundOne = false;
        foreach ($keyRequests as $keyRequest) {
            if ($keyRequest->status === \KeyRequest::STATUS_APPROVED) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return an approved key request for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetKeyRequestsWithApiNames_denied()
    {
        // Arrange:
        $user = $this->users('userWithDeniedKeyRequest');
        
        // Act:
        $keyRequests = $user->getKeyRequestsWithApiNames();
        $foundOne = false;
        foreach ($keyRequests as $keyRequest) {
            if ($keyRequest->status === \KeyRequest::STATUS_DENIED) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return a denied key request for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetKeyRequestsWithApiNames_pending()
    {
        // Arrange:
        $user = $this->users('userWithPendingKeyRequest');
        
        // Act:
        $keyRequests = $user->getKeyRequestsWithApiNames();
        $foundOne = false;
        foreach ($keyRequests as $keyRequest) {
            if ($keyRequest->status === \KeyRequest::STATUS_PENDING) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return a pending key request for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetKeyRequestsWithApiNames_revoked()
    {
        // Arrange:
        $user = $this->users('userWithRevokedKeyRequest');
        
        // Act:
        $keyRequests = $user->getKeyRequestsWithApiNames();
        $foundOne = false;
        foreach ($keyRequests as $keyRequest) {
            if ($keyRequest->status === \KeyRequest::STATUS_REVOKED) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return a revoked key request for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetUsageStatsForAllApis_notAdmin_no()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userWithRoleOfOwner');
        
        // (Pre-assert and) Act:
        $this->setExpectedException(
            'Exception',
            '',
            1426855754
        );
        $user->getUsageStatsForAllApis('day');
        
        // NOTE: It should throw an exception before this point.
    }
    
    public function testGetUsageStatsForAllApis_admin_returnsUsageStats()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $usageStats = $user->getUsageStatsForAllApis('day');
        
        // Assert:
        $this->assertInstanceOf(
            'UsageStats',
            $usageStats,
            'Failed to return UsageStats instance when called by an admin.'
        );
    }
    
    public function testGetUsageStatsTotals_notAdmin_no()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userWithRoleOfOwner');
        
        // (Pre-assert and) Act:
        $this->setExpectedException(
            'Exception',
            '',
            1426860333
        );
        $user->getUsageStatsTotals('day');
        
        // NOTE: It should throw an exception before this point.
    }
    
    public function testGetUsageStatsTotals_admin_returnsUsageStats()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $usageStats = $user->getUsageStatsTotals('day');
        
        // Assert:
        $this->assertInstanceOf(
            'UsageStats',
            $usageStats,
            'Failed to return UsageStats instance when called by an admin.'
        );
    }
    
	public function testHasAdminPrivilegesForApi_noApi()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $result = $user->hasAdminPrivilegesForApi(null);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly said that a User has admin privileges on a null Api.'
        );
    }
    
	public function testHasAdminPrivilegesForApi_adminUser()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->hasAdminPrivilegesForApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to recognize that an admin User has admin privileges on '
            . 'an Api.'
        );
    }
    
	public function testHasAdminPrivilegesForApi_ownerOfApi()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->hasAdminPrivilegesForApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to recognize the User who owns an Api has admin '
            . 'privileges on it.'
        );
    }
    
	public function testHasAdminPrivilegesForApi_notOwnerOfApi()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $result = $user->hasAdminPrivilegesForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly said that a User (with role of "owner") has admin '
            . 'privileges on an Api that the user does not own.'
        );
    }
    
	public function testHasAdminPrivilegesForApi_nonOwnerUser()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $result = $user->hasAdminPrivilegesForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly said that a normal User (who is NOT an owner) has '
            . 'admin privileges on an Api.'
        );
    }
    
	public function testHasApisRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $user = new User();
        $this->assertTrue(isset($user->apis),
            'No way found to retrieve APIs owned by a User instance');
    }
    
	public function testHasKeyRequestsRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $user = new User();
        $this->assertTrue(isset($user->keyRequests),
            'No way found to retrieve KeyRequests from a User instance');
    }
    
	public function testHasKeysRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $user = new User();
        $this->assertTrue(isset($user->keys),
            'No way found to retrieve Keys from a User instance');
    }
    
    public function testHasActiveKeyToApi_no_noApiGiven()
    {
        // Arrange:
        $api = null;
        $user = $this->users('userWithApprovedKeyRequest');
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has an active key to a null Api.'
        );
    }
    
    public function testHasActiveKeyToApi_yes_approvedKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithApprovedKeyRequest');
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to indicate that a User has an active key to an Api.'
        );
    }
    
    public function testHasActiveKeyToApi_no_noKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithNoKeyRequests');
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they have neither a Key nor a KeyRequest.'
        );
    }
    
    public function testHasActiveKeyToApi_no_deniedKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithDeniedKeyRequest');
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they only have a denied KeyRequest.'
        );
    }
    
    public function testHasActiveKeyToApi_no_pendingKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithPendingKeyRequest');
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they only have a pending KeyRequest.'
        );
    }
    
    public function testHasActiveKeyToApi_no_revokedKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithRevokedKeyRequest');
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they only have a revoked KeyRequest.'
        );
    }
    
    public function testHasOwnerPrivileges_admin()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $hasOwnerPrivileges = $user->hasOwnerPrivileges();
        
        // Assert:
        $this->assertTrue(
            $hasOwnerPrivileges,
            'Failed to indicate that a user with a role of "admin" has API '
            . 'Owner privileges.'
        );
    }
    
    public function testHasOwnerPrivileges_owner()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $hasOwnerPrivileges = $user->hasOwnerPrivileges();
        
        // Assert:
        $this->assertTrue(
            $hasOwnerPrivileges,
            'Failed to indicate that a user with a role of "owner" has API '
            . 'Owner privileges.'
        );
    }
    
    public function testHasOwnerPrivileges_user()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $hasOwnerPrivileges = $user->hasOwnerPrivileges();
        
        // Assert:
        $this->assertFalse(
            $hasOwnerPrivileges,
            'Incorrectly indicated that a user with a role of "user" has API '
            . 'Owner privileges.'
        );
    }
    
    public function testHasPendingKeyRequestForApi_no_noApiGiven()
    {
        // Arrange:
        $api = null;
        $user = $this->users('userWithPendingKeyRequest');
        
        // Act:
        $result = $user->hasPendingKeyRequestForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has a pending KeyRequest for a '
            . 'null API.'
        );
    }
    
    public function testHasPendingKeyRequestForApi_no_onlyHasApprovedKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithApprovedKeyRequest');
        
        // Act:
        $result = $user->hasPendingKeyRequestForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has a pending KeyRequest for an '
            . 'API when they only have an approved KeyRequest.'
        );
    }
    
    public function testHasPendingKeyRequestForApi_no_noKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithNoKeyRequests');
        
        // Act:
        $result = $user->hasPendingKeyRequestForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has a pending KeyRequest for an '
            . 'Api when they have no KeyRequests.'
        );
    }
    
    public function testHasPendingKeyRequestForApi_no_onlyHasDeniedKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithDeniedKeyRequest');
        
        // Act:
        $result = $user->hasPendingKeyRequestForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has a pending KeyRequest for an '
            . 'API when they only have a denied KeyRequest.'
        );
    }
    
    public function testHasPendingKeyRequestForApi_yes_hasPendingKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithPendingKeyRequest');
        
        // Act:
        $result = $user->hasPendingKeyRequestForApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User has a pending KeyRequest to an Api.'
        );
    }
    
    public function testHasPendingKeyRequestForApi_no_onlyHasRevokedKeyRequest()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithRevokedKeyRequest');
        
        // Act:
        $result = $user->hasPendingKeyRequestForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has a pending KeyRequest for an '
            . 'Api when they only have a revoked KeyRequest.'
        );
    }
    
    public function testIsAdmin_no_roleOfUser()
    {
        // Arrange:
        /* @var $user User */
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $result = $user->isAdmin();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User with a role of user is an admin.'
        );
    }
    
    public function testIsAdmin_no_roleOfOwner()
    {
        // Arrange:
        /* @var $user User */
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $result = $user->isAdmin();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User with a role of owner is an admin.'
        );
    }
    
    public function testIsAdmin_yes_roleOfAdmin()
    {
        // Arrange:
        /* @var $user User */
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->isAdmin();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User with a role of admin is an admin.'
        );
    }
    
    public function testIsDisabled_no()
    {
        // Arrange:
        $user = new User;
        /* Note: The value is cast to a string because Yii returns all integers
         *       as strings to avoid hitting max-value limits.  */
        $user->status = (string)\User::STATUS_ACTIVE;
        
        // Act:
        $result = $user->isDisabled();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly returned true for an active user.'
        );
    }
    
    public function testIsDisabled_yes()
    {
        // Arrange:
        $user = new User;
        /* Note: The value is cast to a string because Yii returns all integers
         *       as strings to avoid hitting max-value limits.  */
        $user->status = (string)\User::STATUS_INACTIVE;
        
        // Act:
        $result = $user->isDisabled();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to return true for an inactive user.'
        );
    }
    
    public function testIsEmailAddressInUse_no()
    {
        // Arrange:
        $unusedEmailAddress = sprintf(
            'test_unused_%s@example.org',
            microtime(true)
        );
        
        // Act:
        $result = \User::isEmailAddressInUse($unusedEmailAddress);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that an unused email address is in use.'
        );
    }
    
    public function testIsEmailAddressInUse_yes()
    {
        // Arrange:
        $user = $this->users['user1'];
        
        // Pre-assert:
        $this->assertArrayHasKey(
            'email',
            $user,
            'This test requires user fixture data with an email address entry.'
        );
        $this->assertTrue(
            is_string($user['email']),
            'This test requires an actual email address.'
        );
        $this->assertContains(
            '@',
            $user['email'],
            'This test requires a user fixture with an email address.'
        );
        
        // Act:
        $result = \User::isEmailAddressInUse($user['email']);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to return true for an email address that is in use.'
        );
    }
    
    public function testIsOwnerOfApi_yes()
    {
        // Arrange:
        $user = $this->users('userThatOwnsASingleApi');
        $api = $this->apis('apiWithOwner');
        
        // Act:
        $result = $user->isOwnerOfApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a user owns an API.'
        );
    }
    
    public function testIsOwnerOfApi_no()
    {
        // Arrange:
        $user = $this->users('userThatDoesNotOwnAnyApis');
        $api = $this->apis('apiWithOwner');
        
        // Act:
        $result = $user->isOwnerOfApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a user owns an API.'
        );
    }
}
