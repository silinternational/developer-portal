<?php

class UserTest extends DeveloperPortalTestCase
{
    public $fixtures = array(
        'apis' => 'Api',
        'apiVisibilityDomains' => 'ApiVisibilityDomain',
        'apiVisibilityUsers' => 'ApiVisibilityUser',
        'keys' => 'Key',
        'users' => 'User',
    );
    
    public function setUp()
    {
        global $ENABLE_AXLE;
        if (!isset($ENABLE_AXLE) || $ENABLE_AXLE == true) {
            $ENABLE_AXLE = false;
        }
        parent::setUp();       
    }
    
    public function testFixtureDataValidity()
    {
        foreach ($this->users as $fixtureName => $fixtureData) {
            /* @var $user \User */
            $user = $this->users($fixtureName);
            $avdGrants = \ApiVisibilityDomain::model()->findAllByAttributes(array(
                'invited_by_user_id' => $user->user_id,
            ));
            foreach ($avdGrants as $avdGrant) {
                $this->assertTrue($avdGrant->delete(), sprintf(
                    'Could not delete ApiVisibilityDomain fixture: %s',
                    print_r($avdGrant->getErrors(), true)
                ));
            }
            $avuGrants = \ApiVisibilityUser::model()->findAllByAttributes(array(
                'invited_by_user_id' => $user->user_id,
            ));
            foreach ($avuGrants as $avuGrant) {
                $this->assertTrue($avuGrant->delete(), sprintf(
                    'Could not delete ApiVisibilityUser fixture: %s',
                    print_r($avuGrant->getErrors(), true)
                ));
            }
            $keysProcessed = \Key::model()->findAllByAttributes(array(
                'processed_by' => $user->user_id,
            ));
            foreach ($keysProcessed as $keyProcessed) {
                $this->assertTrue($keyProcessed->delete(), sprintf(
                    'Could not delete Key fixture: %s',
                    print_r($keyProcessed->getErrors(), true)
                ));
            }
            $events = \Event::model()->findAllByAttributes(array(
                'user_id' => $user->user_id,
            ));
            foreach ($events as $event) {
                $this->assertTrue($event->delete(), sprintf(
                    'Could not delete Event fixture: %s',
                    print_r($event->getErrors(), true)
                ));
            }
            $this->assertTrue($user->delete(), sprintf(
                'Could not delete user fixture %s: %s',
                $fixtureName,
                print_r($user->getErrors(), true)
            ));
            $userOnInsert = new \User();
            $userOnInsert->setAttributes($fixtureData, false);
            $this->assertTrue($userOnInsert->save(), sprintf(
                'User fixture "%s" (ID %s) does not have valid data: %s',
                $fixtureName,
                $userOnInsert->user_id,
                var_export($userOnInsert->getErrors(), true)
            ));
        }
    }
    
    public function testAffectedByEvents_none()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testAffectedByEvents_one()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
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
    
    public function testApprovedKeyCount_none()
    {
        // Arrange:
        $user = $this->users('ownerThatDoesNotOwnAnyApisOrKeys');
        
        // Act:
        $actual = $user->approvedKeyCount;
        
        // Assert:
        $this->assertEquals(
            0,
            $actual,
            'Incorrectly reported that a user with no approved keys has some.'
        );
    }
    
    public function testApprovedKeyCount_one()
    {
        // Arrange:
        $user = $this->users('userWithOneApprovedKeyAndTwoPendingKeys');
        
        // Act:
        $actual = $user->approvedKeyCount;
        
        // Assert:
        $this->assertEquals(
            1,
            $actual,
            'Failed to return correct number of approved keys for a user that has one.'
        );
    }
    
    public function testAuthProviderRequired_emptyString()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('user1');
        
        // Pre-assert:
        $this->assertTrue($user->validate(), sprintf(
            'This test requires a user that has valid attributes: %s',
            print_r($user->getErrors(), true)
        ));
        
        // Act:
        $user->auth_provider = '';
        $result = $user->validate();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject an empty string as an auth_provider.'
        );
    }
    
    public function testAuthProviderRequired_false()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('user1');
        
        // Pre-assert:
        $this->assertTrue($user->validate(), sprintf(
            'This test requires a user that has valid attributes: %s',
            print_r($user->getErrors(), true)
        ));
        
        // Act:
        $user->auth_provider = false;
        $result = $user->validate();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject false as an auth_provider.'
        );
    }
    
    public function testAuthProviderRequired_null()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('user1');
        
        // Pre-assert:
        $this->assertTrue($user->validate(), sprintf(
            'This test requires a user that has valid attributes: %s',
            print_r($user->getErrors(), true)
        ));
        
        // Act:
        $user->auth_provider = null;
        $result = $user->validate();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject a null auth_provider.'
        );
    }
    
    public function testBeforeDelete()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userWithKeyToApiOwnedByUser18');
        
        // Act:
        $result = $user->delete();
        
        // Assert:
        $this->assertEmpty($user->getErrors());
        $this->assertTrue(
            $result,
            'Failed to delete a user.'
        );
    }

    public function testCanDeleteKey_nullKey()
    {
        // Arrange:
        $key = null;
        $user = $this->users('user1');
        
        // Act:
        $result = $user->canDeleteKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can delete a null Key.'
        );
    }

    public function testCanDeleteKey_ownKey()
    {
        // Arrange:
        $key = $this->keys('pendingKey1_apiWithTwoPendingKeys');
        $user = $this->users('userWith1stPKForApiWithTwoPendingKeys');
        
        // Act:
        $result = $user->canDeleteKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can delete their own Key.'
        );
    }

    public function testCanDeleteKey_notOwnKey()
    {
        // Arrange:
        $key = $this->keys('pendingKey2_apiWithTwoPendingKeys');
        $user = $this->users('userWith1stPKForApiWithTwoPendingKeys');
        
        // Act:
        $result = $user->canDeleteKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a normal user could delete a key that was not theirs.'
        );
    }

    public function testCanDeleteKey_pendingKeyForApiOwnedByUser()
    {
        // Arrange:
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canDeleteKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can delete a Key for an Api '
            . 'that they own.'
        );
    }

    public function testCanDeleteKey_pendingKeyNotOwnedByUserToApiNotOwnedByUser()
    {
        // Arrange:
        $key = $this->keys('pendingKey1_apiWithTwoPendingKeys');
        $user = $this->users('ownerThatDoesNotOwnAnyApisOrKeys');
        
        // Act:
        $result = $user->canDeleteKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a user with role "owner" could delete '
            . 'a Key that they do not own for an Api that they do not '
            . 'own.'
        );
    }

    public function testCanDeleteKey_adminUser()
    {
        // Arrange:
        $key = $this->keys('pendingKey1_apiWithTwoPendingKeys');
        $user = $this->users('userWithRoleOfAdminButNoKeys');
        
        // Act:
        $result = $user->canDeleteKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that an admin User can delete any Key.'
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
    
    public function testCanSeeKey_nullKey()
    {
        // Arrange:
        $key = null;
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->canSeeKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User can see a null Key.'
        );
    }

    public function testCanSeeKey_adminUser()
    {
        // Arrange:
        $key = $this->keys('pendingKeyUser6');
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $result = $user->canSeeKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Incorrectly reported that an admin User cannot see a particular '
            . 'Key.'
        );
    }

    public function testCanSeeKey_forOwnApi()
    {
        // Arrange:
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $user = $this->users('user18');
        
        // Act:
        $result = $user->canSeeKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can see a Key for an API that '
            . 'the User owns.'
        );
    }

    public function testCanSeeKey_notForOwnApi()
    {
        // Arrange:
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $result = $user->canSeeKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            "Incorrectly reported that a User can see someone else's "
            . "Key for an API that the User does NOT own."
        );
    }

    public function testCanSeeKey_ownKey()
    {
        // Arrange:
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $user = $this->users('userWithPendingKeyForApiOwnedByUser18');
        
        // Act:
        $result = $user->canSeeKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User can see their own Key.'
        );
    }

    public function testCanSeeKey_notOwnKey()
    {
        // Arrange:
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $result = $user->canSeeKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            "Incorrectly reported that a (developer) User can see someone "
            . "else's Key."
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

    public function testCausedEvents_none()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testCausedEvents_one()
    {
        $this->markTestIncomplete('Test not yet written.');
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
    
    public function testGetDisplayName_isDefined()
    {
        // Arrange:
        $user = $this->users('user1');
        
        // Pre-assert:
        $this->assertNotEmpty(
            $user->display_name,
            'This test requires a user with a display_name defined.'
        );
        
        // Act:
        $result = $user->getDisplayName();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            'Failed to return a display name for a user with a display_name defined.'
        );
    }
    
    public function testGetDisplayName_isNotDefined()
    {
        // Arrange:
        $user = $this->users('userWithNoPendingKeys');
        
        // Pre-assert:
        $this->assertEmpty(
            $user->display_name,
            'This test requires a user with no display_name defined.'
        );
        
        // Act:
        $result = $user->getDisplayName();
        
        // Assert:
        $this->assertNotEmpty(
            $result,
            'Failed to assemble and return display name for a user with no display_name defined.'
        );
    }
    
    public function testGetEmailAddressDomain()
    {
        // Arrange:
        /* @var $user \User */
        $user = $this->users('userWithEmailDomainNotInvitedToSeeAnyApi');
        $expected = 'not-invited-domain.example.com';
        
        // Act:
        $actual = $user->getEmailAddressDomain();
        
        // Assert:
        $this->assertSame($expected, $actual);
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
    
    public function testGetKeysWithApiNames_none()
    {
        // Arrange:
        $user = $this->users('userWithNoPendingKeys');
        
        // Act:
        $keys = $user->getKeysWithApiNames();
        
        // Assert:
        $this->assertEmpty(
            $keys,
            'Failed to return an empty array for the keys of a user '
            . 'that has no keys.'
        );
    }
    
    public function testGetKeysWithApiNames_approved()
    {
        // Arrange:
        $user = $this->users('userWithApprovedKey');
        
        // Act:
        $keys = $user->getKeysWithApiNames();
        $foundOne = false;
        foreach ($keys as $key) {
            if ($key->status === \Key::STATUS_APPROVED) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return an approved key for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetKeysWithApiNames_denied()
    {
        // Arrange:
        $user = $this->users('userWithDeniedKey');
        
        // Act:
        $keys = $user->getKeysWithApiNames();
        $foundOne = false;
        foreach ($keys as $key) {
            if ($key->status === \Key::STATUS_DENIED) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return a denied key  for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetKeysWithApiNames_pending()
    {
        // Arrange:
        $user = $this->users('userWithPendingKey');
        
        // Act:
        $keys = $user->getKeysWithApiNames();
        $foundOne = false;
        foreach ($keys as $key) {
            if ($key->status === \Key::STATUS_PENDING) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return a pending key for a user that should '
            . 'have one.'
        );
    }
    
    public function testGetKeysWithApiNames_revoked()
    {
        // Arrange:
        $user = $this->users('userWithRevokedKey');
        
        // Act:
        $keys = $user->getKeysWithApiNames();
        $foundOne = false;
        foreach ($keys as $key) {
            if ($key->status === \Key::STATUS_REVOKED) {
                $foundOne = true;
            }
        }
        
        // Assert:
        $this->assertTrue(
            $foundOne,
            'Failed to return a revoked key for a user that should '
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
        $this->assertClassHasRelation(new User(), 'apis', 'Api');
    }
    
	public function testHasKeysRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new User(), 'keys', 'Key');
    }
    
    public function testGetActiveKeyToApi_noApiGiven()
    {
        // Arrange:
        $api = null;
        $user = $this->users('userWithApprovedKey');
        
        // Act:
        $result = $user->getActiveKeyToApi($api);
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when an invalid Api was given.'
        );
    }
    
    public function testGetActiveKeyToApi_approvedKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithApprovedKey');
        
        // Act:
        $result = $user->getActiveKeyToApi($api);
        
        // Assert:
        $this->assertNotNull(
            $result,
            'Failed to a User\'s active key to an Api.'
        );
    }
    
    public function testGetActiveKeyToApi_noKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithNoPendingKeys');
        
        // Act:
        $result = $user->getActiveKeyToApi($api);
        
        // Assert:
        $this->assertNull(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they do not have such a Key.'
        );
    }
    
    public function testGetActiveKeyToApi_deniedKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithDeniedKey');
        
        // Act:
        $result = $user->getActiveKeyToApi($api);
        
        // Assert:
        $this->assertNull(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they only have a denied Key.'
        );
    }
    
    public function testGetActiveKeyToApi_no_pendingKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithPendingKey');
        
        // Act:
        $result = $user->getActiveKeyToApi($api);
        
        // Assert:
        $this->assertNull(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they only have a pending Key.'
        );
    }
    
    public function testGetActiveKeyToApi_revokedKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithRevokedKey');
        
        // Act:
        $result = $user->getActiveKeyToApi($api);
        
        // Assert:
        $this->assertNull(
            $result,
            'Incorrectly indicated that a User has an active key to an Api '
            . 'when they only have a revoked Key.'
        );
    }
    
    public function testHasActiveKeyToApi_no()
    {
        // Arrange:
        $api = new Api();
        /* @var $user \User */
        $user = \Phake::mock('\User');
        \Phake::when($user)->hasActiveKeyToApi->thenCallParent();
        \Phake::when($user)->getActiveKeyToApi->thenReturn(null);
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User DOES have a Key to an Api when no Key was found.'
        );
    }
    
    public function testHasActiveKeyToApi_yes()
    {
        // Arrange:
        $api = new Api();
        $key = new Key();
        /* @var $user \User */
        $user = \Phake::mock('\User');
        \Phake::when($user)->hasActiveKeyToApi->thenCallParent();
        \Phake::when($user)->getActiveKeyToApi->thenReturn($key);
        
        // Act:
        $result = $user->hasActiveKeyToApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to indicated that a User has a Key to an Api when a Key was returned.'
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
    
    public function testHasPendingKeyForApi_no_noApiGiven()
    {
        // Arrange:
        $api = null;
        $user = $this->users('userWithPendingKey');
        
        // Act:
        $result = $user->hasPendingKeyForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has a pending Key for a '
            . 'null API.'
        );
    }
    
    public function testHasPendingKeyForApi_no_onlyHasApprovedKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithApprovedKey');
        
        // Act:
        $result = $user->hasPendingKeyForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has a pending Key for an '
            . 'API when they only have an approved Key.'
        );
    }
    
    public function testHasPendingKeyForApi_no_noKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithNoPendingKeys');
        
        // Act:
        $result = $user->hasPendingKeyForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has a pending Key for an '
            . 'Api when they have no pending Keys.'
        );
    }
    
    public function testHasPendingKeyForApi_no_onlyHasDeniedKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithDeniedKey');
        
        // Act:
        $result = $user->hasPendingKeyForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User has a pending Key for an '
            . 'API when they only have a denied Key.'
        );
    }
    
    public function testHasPendingKeyForApi_yes_hasPendingKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithPendingKey');
        
        // Act:
        $result = $user->hasPendingKeyForApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User has a pending Key to an Api.'
        );
    }
    
    public function testHasPendingKeyForApi_no_onlyHasRevokedKey()
    {
        // Arrange:
        $api = $this->apis('api2');
        $user = $this->users('userWithRevokedKey');
        
        // Act:
        $result = $user->hasPendingKeyForApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly indicated that a User has a pending Key for an '
            . 'Api when they only have a revoked Key.'
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
    
    public function testIsAuthorizedToApproveKey_no()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        /* @var $user \User */
        $user = $this->users('userWithRoleOfOwner'); // NOT owner of Key's Api.
        
        // Act:
        $result = $user->isAuthorizedToApproveKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User is authorized to approve a Key to an Api that the User does NOT own.'
        );
    }
    
    public function testIsAuthorizedToApproveKey_noKeyGiven()
    {
        // Arrange:
        $key = null;
        /* @var $user \User */
        $user = $this->users('userWithRoleOfOwner'); // NOT owner of Key's Api.
        
        // Act:
        $result = $user->isAuthorizedToApproveKey($key);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a User is authorized to approve a null Key.'
        );
    }
    
    public function testIsAuthorizedToApproveKey_yes()
    {
        // Arrange:
        /* @var $key \Key */
        $key = $this->keys('pendingKeyForApiOwnedByUser18');
        /* @var $user \User */
        $user = $this->users('user18');
        
        // Act:
        $result = $user->isAuthorizedToApproveKey($key);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User is authorized to approve a Key to an Api that the User owns.'
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
    
    public function testIsIndividuallyInvitedToSeeApi_no()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('apiVisibleByInvitationOnly');
        /* @var $user \User */
        $user = $this->users('userNotIndividuallyInvitedToSeeAnyApi');
        
        // Pre-assert:
        $apiVisibilityUsers = \ApiVisibilityUser::model()->findAllByAttributes(array(
            'invited_user_id' => $user->user_id,
        ));
        $this->assertCount(
            0,
            $apiVisibilityUsers,
            'This test requires a user that has not been individually invited to see any APIs.'
        );
        
        // Act:
        $result = $user->isIndividuallyInvitedToSeeApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to report that a User has NOT been individually invited to see an Api.'
        );
    }
    
    public function testIsIndividuallyInvitedToSeeApi_yes()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('apiVisibleByInvitationOnly');
        /* @var $user \User */
        $user = $this->users('userIndividuallyInvitedToSeeApi');
        
        // Act:
        $result = $user->isIndividuallyInvitedToSeeApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User HAS been individually invited to see an Api.'
        );
    }
    
    public function testIsInvitedByDomainToSeeApi_no()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('apiVisibleByInvitationOnly');
        /* @var $user \User */
        $user = $this->users('userWithEmailDomainNotInvitedToSeeAnyApi');
        
        // Pre-assert:
        $apiVisibilityDomains = \ApiVisibilityDomain::model()->findAllByAttributes(array(
            'domain' => $user->getEmailAddressDomain(),
        ));
        $this->assertCount(
            0,
            $apiVisibilityDomains,
            'This test requires a user that has an email domain that has NOT been invited to see any Apis.'
        );
        
        // Act:
        $result = $user->isInvitedByDomainToSeeApi($api);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that that a User has an email domain that has been invited to see an Api.'
        );
    }
    
    public function testIsInvitedByDomainToSeeApi_yes()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('apiVisibleByInvitationOnly');
        /* @var $user \User */
        $user = $this->users('userWithEmailDomainInvitedToSeeApi');
        
        // Act:
        $result = $user->isInvitedByDomainToSeeApi($api);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a User DOES have an email domain that has been invited to see an Api.'
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
    
    public function testPendingKeyCount_none()
    {
        // Arrange:
        $user = $this->users('userWithNoPendingKeys');
        
        // Act:
        $actual = $user->pendingKeyCount;
        
        // Assert:
        $this->assertEquals(
            0,
            $actual,
            'Incorrectly reported that a user with no pending keys has some.'
        );
    }
    
    public function testPendingKeyCount_two()
    {
        // Arrange:
        $user = $this->users('userWithOneApprovedKeyAndTwoPendingKeys');
        
        // Act:
        $actual = $user->pendingKeyCount;
        
        // Assert:
        $this->assertEquals(
            2,
            $actual,
            'Failed to return correct number of pending keys for a user that has two.'
        );
    }
}
