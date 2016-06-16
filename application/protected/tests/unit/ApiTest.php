<?php

class ApiTest extends DeveloperPortalTestCase
{
    public $fixtures = array(
        'apis' => 'Api',
        'keys' => 'Key',
        'apiVisibilityDomains' => 'ApiVisibilityDomain',
        'apiVisibilityUsers' => 'ApiVisibilityUser',
        'users' => 'User',
    );
    
    public function setUp()
    {
        global $ENABLE_AXLE;
        if(!isset($ENABLE_AXLE) || $ENABLE_AXLE == true){
            $ENABLE_AXLE = false;
        }
        Yii::app()->user->id = 1;
        parent::setUp();
    }
    
    //protected function tearDown()
    //{
    //    $this->deleteApis();
    //    parent::tearDown();
    //}
    //
    //public static function tearDownAfterClass()
    //{
    //    Api::model()->deleteAll();
    //    parent::tearDownAfterClass();
    //}
    
    public function deleteApis()
    {
        $apis = Api::model()->findAll();
        foreach ($apis as $api) {
            $api->delete();  
        }            
    }
    
    public function testFixtureDataValidity()
    {
        foreach ($this->apis as $fixtureName => $fixtureData) {
            /* @var $api \Api */
            $api = $this->apis($fixtureName);
            $this->assertTrue($api->delete(), sprintf(
                'Could not delete api fixture %s: %s',
                $fixtureName,
                print_r($api->getErrors(), true)
            ));
            $apiOnInsert = new \Api();
            $apiOnInsert->setAttributes($fixtureData, false);
            $this->assertTrue($apiOnInsert->save(), sprintf(
                'Api fixture "%s" (%s) does not have valid data: %s',
                $fixtureName,
                $apiOnInsert->display_name,
                var_export($apiOnInsert->getErrors(), true)
            ));
        }
    }
    
    public function testConfirmApprovalTypesDiffer()
    {
        // Make sure the approval type constants differ (both in their values
        // and in their user-friendly versions).
        $this->confirmConstantsDiffer('Api', 'APPROVAL_TYPE_',
                Api::getApprovalTypes());
    }
    
    public function testConfirmProtocolsDiffer()
    {
        // Make sure the protocol constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('Api', 'PROTOCOL_',
                Api::getProtocols());
    }
    
    public function testConfirmStrictSslsDiffer()
    {
        // Make sure the strict SSL constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('Api', 'STRICT_SSL_',
                Api::getStrictSsls());
    }
    
    public function testConfirmVisibilityConstantsDiffer()
    {
        $this->confirmConstantsDiffer(
            'Api',
            'VISIBILITY_',
            Api::getVisibilityDescriptions()
        );
    }
    
    public function testCreateApiWithModelBadCode()
    {
        $this->deleteApis();
        $api = new Api();
        $api->code = '-"test-api1';
        $api->display_name = 'Test Api';
        $api->endpoint = 'localhost';
        $api->queries_second = 2;
        $api->queries_day = 1000;
        $api->visibility = \Api::VISIBILITY_PUBLIC;
        $api->protocol = 'http';
        $api->strict_ssl = 0;
        $api->approval_type = 'auto';
        $api->endpoint_timeout = 2;
        
        $result = $api->save();
        
        $this->assertFalse($result,'Creating API through model accepted invalid Code');
    }
    
    public function testCreateApiWithModelBadEndpoint()
    {
        $this->deleteApis();
        $api = new Api();
        $api->code = 'test-api1';
        $api->display_name = 'Test Api';
        $api->endpoint = 'http://localhost';
        $api->queries_second = 2;
        $api->queries_day = 1000;
        $api->visibility = Api::VISIBILITY_PUBLIC;
        $api->protocol = Api::PROTOCOL_HTTP;
        $api->strict_ssl = Api::STRICT_SSL_FALSE;
        $api->approval_type = Api::APPROVAL_TYPE_AUTO;
        $api->endpoint_timeout = 2;
        
        $result = $api->save();
        
        $this->assertFalse($result,
				'Creating API through model accepted invalid Endpoint');
    }
	
	
	
	// TODO: Test more invalid inputs.
	
	
	
	
    public function testCreateApiWithModelGoodInputs()
    {
        $this->deleteApis();
        $api = new Api();
        $api->code = 'test-api1';
        $api->display_name = 'Test Api';
        $api->endpoint = 'localhost';
        $api->default_path = '/123/path/WITH/just-valid.char_acters';
        $api->queries_second = 2;
        $api->queries_day = 1000;
        $api->visibility = Api::VISIBILITY_PUBLIC;
        $api->protocol = Api::PROTOCOL_HTTP;
        $api->strict_ssl = Api::STRICT_SSL_FALSE;
        $api->approval_type = Api::APPROVAL_TYPE_AUTO;
        $api->endpoint_timeout = 2;
        
        $result = $api->save();
        
        $this->assertTrue(
            $result,
            'Incorrectly reject valid values: '
            . print_r($api->getErrors(), true)
        );
    }
    
    public function testGenerateBadgeHtml_hasBadgeValue()
    {
        // Arrange:
        $badgeValue = 3;
        
        // Act:
        $result = \Api::generateBadgeHtml($badgeValue);
        
        // Assert:
        $this->assertContains(
            strval($badgeValue),
            $result,
            'Failed to include the given badge value in the generated badge '
            . 'HTML.'
        );
        $this->assertContains(
            strval($badgeValue),
            strip_tags($result),
            'Failed to include the given badge value in the text contents of '
            . 'the generated badge HTML.'
        );
    }
    
    public function testGenerateBadgeHtml_hasExtraCssClass()
    {
        // Arrange:
        $extraCssClass = 'fake-css-class';
        
        // Act:
        $result = \Api::generateBadgeHtml(3, $extraCssClass);
        
        // Assert:
        $this->assertContains(
            $extraCssClass,
            $result,
            'Failed to include the extra CSS class given in the generated '
            . 'badge HTML.'
        );
    }
    
    public function testGenerateBadgeHtml_hasHoverTitle()
    {
        // Arrange:
        $hoverTitle = 'Some text to show on mouse hover.';
        
        // Act:
        $result = \Api::generateBadgeHtml(3, null, $hoverTitle);
        
        // Assert:
        $this->assertContains(
            $hoverTitle,
            $result,
            'Failed to include the hover title text given in the generated '
            . 'badge HTML.'
        );
    }
    
    public function testGenerateBadgeHtml_hasLinkTarget()
    {
        // Arrange:
        $linkTarget = 'http://localhost/';
        
        // Act:
        $result = \Api::generateBadgeHtml(3, null, null, $linkTarget);
        
        // Assert:
        $this->assertContains(
            $linkTarget,
            $result,
            'Failed to include the link target URL in the generated badge '
            . 'HTML.'
        );
    }
    
    public function testGetApprovalTypeDescription()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('api4');
        
        // Act:
        $result = $api->getApprovalTypeDescription();
        
        // Assert:
        $this->assertNotNull(
            $result,
            'Failed to retrieve approval type description.'
        );
    }
    
    public function testGetActiveKeyCountBadgeHtml_hasBadgeValue()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoKeys');
        
        // Act:
        $result = $api->getActiveKeyCountBadgeHtml();
        
        // Assert:
        $this->assertContains(
            strval(2),
            $result,
            'Failed to include the correct number of active keys in the '
            . 'generated badge HTML.'
        );
        $this->assertContains(
            strval(2),
            strip_tags($result),
            'Failed to include the correct number of active keys in the text '
            . 'contents of the generated badge HTML.'
        );
    }
    
    public function testGetActiveKeyCountBadgeHtml_hasHoverTitle()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoKeys');
        $hoverTitle = 'Sample hover text/title.';
        
        // Act:
        $result = $api->getActiveKeyCountBadgeHtml($hoverTitle);
        
        // Assert:
        $this->assertContains(
            $hoverTitle,
            $result,
            'Failed to include the given hover title text in the generated '
            . 'badge HTML.'
        );
    }
    
    public function testGetActiveKeyCountBadgeHtml_highlightForNonZero()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithTwoKeys');
        
        // Act:
        $result = $api->getActiveKeyCountBadgeHtml();
        
        // Assert:
        $this->assertContains(
            'badge-',
            $result,
            'Failed to highlight the badge for a non-zero badge value.'
        );
    }
    
    public function testGetActiveKeyCountBadgeHtml_noHighlightForZero()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithZeroKeys');
        
        // Act:
        $result = $api->getActiveKeyCountBadgeHtml();
        
        // Assert:
        $this->assertNotContains(
            'badge-',
            $result,
            'Incorrectly highlighted the badge for a badge value of zero.'
        );
    }
    
    public function testGetEmailAddressesOfUsersWithActiveKeys_apiWithNoKeys()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithZeroKeys');
        $expected = array();
        
        // Act:
        $actual = $api->getEmailAddressesOfUsersWithActiveKeys();
        
        // Assert:
        $this->assertSame(
            $expected,
            $actual,
            'Incorrectly return email address(es) for an Api with no Keys.'
        );
    }
    
    public function testGetEmailAddressesOfUsersWithActiveKeys_apiWithTwoKeys()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithTwoKeys');
        $userA = $this->users('userWithFirstKeyForApiWithTwoKeys');
        $userB = $this->users('userWithSecondKeyForApiWithTwoKeys');
        $expected = array(
            $userA->email,
            $userB->email,
        );
        
        // Act:
        $actual = $api->getEmailAddressesOfUsersWithActiveKeys();
        
        // Assert:
        $this->assertCount(
            2,
            $actual,
            'Returned the wrong number of email addresses.'
        );
        $this->assertEquals(
            $expected,
            $actual,
            'Returned incorrect list of email address for an Api with two Keys.'
        );
    }
    
    public function testGetInternalApiEndpoint_hasDefaultPath()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithDefaultPath');
        $expected = $api->protocol . '://' . $api->endpoint;
        if ($api->default_path) {
            $expected .= $api->default_path;
        } else {
            $expected .= '/';
        }
        
        // Act:
        $actual = $api->getInternalApiEndpoint();
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return the correct internal API endpoint URL for an API'
            . 'that has a default_path.'
        );
    }
	
    public function testGetInternalApiEndpoint_noDefaultPath()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutDefaultPath');
        $expected = $api->protocol . '://' . $api->endpoint;
        if ($api->default_path) {
            $expected .= $api->default_path;
        } else {
            $expected .= '/';
        }
        
        // Act:
        $actual = $api->getInternalApiEndpoint();
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return the correct internal API endpoint URL for an API'
            . 'that does not have a default_path.'
        );
    }
	
    public function testGetPendingKeyCountBadgeHtml_hasBadgeValue()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoPendingKeys');
        
        // Act:
        $result = $api->getPendingKeyCountBadgeHtml();
        
        // Assert:
        $this->assertContains(
            strval(2),
            $result,
            'Failed to include the correct number of pending keys in the '
            . 'generated badge HTML.'
        );
        $this->assertContains(
            strval(2),
            strip_tags($result),
            'Failed to include the correct number of pending keys in the text '
            . 'contents of the generated badge HTML.'
        );
    }
    
    public function testGetPendingKeyCountBadgeHtml_hasHoverTitle()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoKeys');
        $hoverTitle = 'Sample hover text/title.';
        
        // Act:
        $result = $api->getPendingKeyCountBadgeHtml($hoverTitle);
        
        // Assert:
        $this->assertContains(
            $hoverTitle,
            $result,
            'Failed to include the given hover title text in the generated '
            . 'badge HTML.'
        );
    }
    
    public function testGetPendingKeyCountBadgeHtml_highlightForNonZero()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithTwoPendingKeys');
        
        // Act:
        $result = $api->getPendingKeyCountBadgeHtml();
        
        // Assert:
        $this->assertContains(
            'badge-',
            $result,
            'Failed to highlight the badge for a non-zero badge value.'
        );
    }
    
    public function testGetPendingKeyCountBadgeHtml_noHighlightForZero()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithZeroPendingKeys');
        
        // Act:
        $result = $api->getPendingKeyCountBadgeHtml();
        
        // Assert:
        $this->assertNotContains(
            'badge-',
            $result,
            'Incorrectly highlighted the badge for a badge value of zero.'
        );
    }
    
    public function testGetApiProxyDomain()
    {
        // Arrange:
        $api = new Api;
        $expected = \Yii::app()->params['apiProxyDomain'];
        
        // Act:
        $actual = $api->getApiProxyDomain();
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return the correct apiProxyDomain from the config data.'
        );
    }
    
    public function testGetApiProxyProtocol()
    {
        // Arrange:
        $api = new Api;
        if (isset(\Yii::app()->params['apiProxyProtocol'])) {
            $expected = \Yii::app()->params['apiProxyProtocol'];
        } else {
            $expected = 'https';
        }
        
        // Act:
        $actual = $api->getApiProxyProtocol();
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return the correct apiProxyProtocol from the config '
            . 'data.'
        );
    }
    
    public function testGetPublicUrl()
    {
        // Arrange:
        $api = $this->apis('api4');
        $expected = $api->getApiProxyProtocol() . '://' . 
                    $api->code . $api->getApiProxyDomain() . '/';
        
        // Act:
        $actual = $api->getPublicUrl();
        
        // Assert:
        $this->assertNotNull(
            $api->code,
            'Unable to test getPublicUrl because this API has no code value.'
        );
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return the correct public URL for an API.'
        );
    }
	
    public function testGetStyledPublicUrlHtml_styledUrlEqualsNormalUrl()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('api4');
        $expected = $api->getPublicUrl();
        
        // Act:
        $actual = strip_tags($api->getStyledPublicUrlHtml());
        
        // Assert:
        $this->assertSame(
            $expected,
            $actual,
            'Styled public URL and normal public URL do not match.'
        );
    }
	
    public function testGetStyledPublicUrlHtml_styledUrlHasCodeInTags()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('api4');
        
        // Act:
        $result = $api->getStyledPublicUrlHtml();
        
        // Assert:
        $this->assertContains(
            '>' . CHtml::encode($api->code) . '</',
            $result,
            'Api code does not seem to be in HTML tags in styled public URL.'
        );
    }
	
    public function testGetStyledPublicUrlHtml_hasGivenCssClass()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('api4');
        $cssClass = 'test-css';
        
        // Act:
        $result = $api->getStyledPublicUrlHtml($cssClass);
        
        // Assert:
        $this->assertContains(
            $cssClass,
            $result,
            'HTML for styled public URL does not contain given CSS class.'
        );
    }
	
    public function testGetVisibilityDescription()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('api4');
        
        // Act:
        $result = $api->getVisibilityDescription();
        
        // Assert:
        $this->assertNotNull(
            $result,
            'Failed to retrieve visibility description.'
        );
    }
    
	public function testHasKeysRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Api(), 'keys', 'Key');
    }
    
	public function testHasOwnerRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Api(), 'owner', 'User');
    }
    
    public function testIsPubliclyVisible_no()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('apiVisibleByInvitationOnly');
        
        // Act:
        $result = $api->isPubliclyVisible();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a non-public Api is publicly visible.'
        );
    }
    
    public function testIsPubliclyVisible_yes()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('publicApi');
        
        // Act:
        $result = $api->isPubliclyVisible();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a public Api is publicly visible.'
        );
    }
    
    public function testIsVisibleToUser_publicApi_noUser()
    {
        // Arrange:
        $user = null;
        $api = $this->apis('publicApi');
        
        // Act:
        $result = $api->isVisibleToUser($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to hide public Api from unauthenticated (null) User.'
        );
    }
    
    public function testIsVisibleToUser_publicApi_roleUser()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfUser');
        $api = $this->apis('publicApi');
        
        // Act:
        $result = $api->isVisibleToUser($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to show public Api to a normal authenticated user.'
        );
    }
    
    public function testIsVisibleToUser_nonPublicApi_adminUser()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfAdmin');
        $api = $this->apis('apiVisibleByInvitationOnlyWithNoInvitations');
        
        // Act:
        $result = $api->isVisibleToUser($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to show non-public Api to an admin user.'
        );
    }
    
    public function testIsVisibleToUser_nonPublicApi_apiOwner()
    {
        // Arrange:
        $user = $this->users('userWithRoleOfOwner');
        $api = $this->apis('apiVisibleByInvitationOnlyWithNoInvitations');
        
        // Act:
        $result = $api->isVisibleToUser($user);
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to show non-public Api to the owner of that Api.'
        );
    }
    
    public function testIsVisibleToUser_no()
    {
        // Arrange:
        /* @var $api \Api */
        $api = $this->apis('apiVisibleByInvitationOnlyWithNoInvitations');
        /* @var $user \User */
        $user = $this->users('userNotInvitedToSeeAnyApi');
        
        // Pre-assert:
        $apiVisibilityDomains = \ApiVisibilityDomain::model()->findAllByAttributes(array(
            'api_id' => $api->api_id,
        ));
        $this->assertCount(
            0,
            $apiVisibilityDomains,
            'This test requires an Api that no domains have been invited to see.'
        );
        $apiVisibilityUsers = \ApiVisibilityUser::model()->findAllByAttributes(array(
            'api_id' => $api->api_id,
        ));
        $this->assertCount(
            0,
            $apiVisibilityUsers,
            'This test requires an Api that no individuals have been invited to see.'
        );
        
        
        // Act:
        $result = $api->isVisibleToUser($user);
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that an uninvited user could see a non-public Api.'
        );
    }
    
    public function testIsUniqueEndpointDefaultPathCombo_sameEndpointNoDefaultPath()
    {
        // Arrange:
        /* @var $existingApi Api */
        $existingApi = $this->apis('apiWithoutDefaultPath');
        $newApi = new Api;
        $newApi->attributes = $existingApi->attributes;
        $newApi->code = $existingApi->code . '-duplicate';
        
        // Act:
        $result = $newApi->validate();
        
        // Assert:
        $this->assertNotEmpty($newApi->errors);
        $this->assertFalse(
            $result,
            'Incorrectly allowed a duplicate endpoint/default_path combination '
            . 'for an Api with no default_path.'
        );
    }
    
    public function testIsUniqueEndpointDefaultPathCombo_sameEndpointAndDefaultPath()
    {
        // Arrange:
        /* @var $existingApi Api */
        $existingApi = $this->apis('apiWithDefaultPath');
        $newApi = new Api;
        $newApi->attributes = $existingApi->attributes;
        $newApi->code = $existingApi->code . '-duplicate';
        
        // Act:
        $result = $newApi->validate();
        
        // Assert:
        $this->assertNotEmpty($newApi->errors);
        $this->assertFalse(
            $result,
            'Incorrectly allowed a duplicate endpoint/default_path combination '
            . 'for an Api with a default_path.'
        );
    }
    
    public function testIsUniqueEndpointDefaultPathCombo_differentEndpointNoDefaultPath()
    {
        // Arrange:
        /* @var $existingApi Api */
        $existingApi = $this->apis('apiWithoutDefaultPath');
        $newApi = new Api;
        $newApi->attributes = $existingApi->attributes;
        $newApi->code = $existingApi->code . '-duplicate';
        $newApi->endpoint = $existingApi->endpoint . '-duplicate';
        
        // Act:
        $result = $newApi->validate();
        
        // Assert:
        $this->assertEmpty($newApi->errors);
        $this->assertTrue(
            $result,
            'Failed to recognize that a different endpoint results in a '
            . 'different endpoint/default_path combination (for an Api with no '
            . 'default_path).'
        );
    }
    
    public function testIsUniqueEndpointDefaultPathCombo_differentEndpointSameDefaultPath()
    {
        // Arrange:
        /* @var $existingApi Api */
        $existingApi = $this->apis('apiWithDefaultPath');
        $newApi = new Api;
        $newApi->attributes = $existingApi->attributes;
        $newApi->code = $existingApi->code . '-duplicate';
        $newApi->endpoint = $existingApi->endpoint . '-duplicate';
        
        // Act:
        $result = $newApi->validate();
        
        // Assert:
        $this->assertEmpty($newApi->errors);
        $this->assertTrue(
            $result,
            'Failed to recognize that a different endpoint and the same '
            . 'default_path results in a different endpoint/default_path '
            . 'combination.'
        );
    }
    
    public function testIsUniqueEndpointDefaultPathCombo_differentEndpointDifferentDefaultPath()
    {
        // Arrange:
        /* @var $existingApi Api */
        $existingApi = $this->apis('apiWithDefaultPath');
        $newApi = new Api;
        $newApi->attributes = $existingApi->attributes;
        $newApi->code = $existingApi->code . '-duplicate';
        $newApi->endpoint = $existingApi->endpoint . '-duplicate';
        $newApi->default_path = $existingApi->default_path . '-duplicate';
        
        // Act:
        $result = $newApi->validate();
        
        // Assert:
        $this->assertEmpty($newApi->errors);
        $this->assertTrue(
            $result,
            'Failed to recognize that a different endpoint and different '
            . 'default_path results in a different endpoint/default_path '
            . 'combination.'
        );
    }
    
    public function testKeyCount()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoKeys');
        
        // Act:
        $actual = $api->keyCount;
        
        // Assert:
        $this->assertEquals(
            2,
            $actual,
            'Failed to report the correct number of keys for an Api.'
        );
    }
    
    public function testPendingKeyCount()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoPendingKeys');
        
        // Act:
        $actual = $api->pendingKeyCount;
        
        // Assert:
        $this->assertEquals(
            2,
            $actual,
            'Failed to report the correct number of pending keys for '
            . 'an Api.'
        );
    }
    
    public function testOwner_hasNoOwner()
    {
        // Arrange:
        $api = $this->apis('apiWithoutOwner');
        
        // Act:
        $result = $api->owner;
        
        // Assert:
        $this->assertNull(
            $result,
            'Incorrectly returned a non-null value for the owner of an Api '
            . 'that has no owner.'
        );
    }
    
    public function testOwner_hasOwner()
    {
        // Arrange:
        $api = $this->apis('apiWithOwner');
        $expected = $this->users('userThatOwnsASingleApi');
        
        // Act:
        $actual = $api->owner;
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return an API\'s owner.'
        );
    }
    
    public function testValidateOwnerId_invalidOwnerId_0()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutOwner');
        
        // Act:
        $api->owner_id = 0;
        $api->validateOwnerId('owner_id', array());
        
        // Assert:
        $this->assertArrayHasKey(
            'owner_id',
            $api->getErrors(),
            'Failed to reject an invalid owner_id (0).'
        );
    }
    
    public function testValidateOwnerId_unspecifiedOwnerId_null()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutOwner');
        
        // Act:
        $api->owner_id = null;
        $api->validateOwnerId('owner_id', array());
        
        // Assert:
        $this->assertEquals(
            array(),
            $api->getErrors('owner_id'),
            'Incorrectly rejected use of null to indicate no owner.'
        );
    }
    
    public function testValidateOwnerId_unspecifiedOwnerId_emptyString()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutOwner');
        
        // Act:
        $api->owner_id = '';
        $api->validateOwnerId('owner_id', array());
        
        // Assert:
        $this->assertEquals(
            array(),
            $api->getErrors('owner_id'),
            'Incorrectly rejected use of an empty string to indicate no owner.'
        );
    }
    
    public function testValidateOwnerId_normalNonOwnerUserId()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutOwner');
        $user = $this->users('userWithRoleOfUser');
        
        // Act:
        $api->owner_id = $user->user_id;
        $api->validateOwnerId('owner_id', array());
        
        // Assert:
        $this->assertArrayHasKey(
            'owner_id',
            $api->getErrors(),
            'Failed to reject non-owner User as the Owner for an Api.'
        );
    }
    
    public function testValidateOwnerId_ownerUserAsOwner()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutOwner');
        $user = $this->users('userWithRoleOfOwner');
        
        // Act:
        $api->owner_id = $user->user_id;
        $api->validateOwnerId('owner_id', array());
        
        // Assert:
        $this->assertEquals(
            array(),
            $api->getErrors('owner_id'),
            'Incorrectly rejected an API Owner as the owner of an Api.'
        );
    }
    
    public function testValidateOwnerId_adminUserAsOwner()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithoutOwner');
        $user = $this->users('userWithRoleOfAdmin');
        
        // Act:
        $api->owner_id = $user->user_id;
        $api->validateOwnerId('owner_id', array());
        
        // Assert:
        $this->assertEquals(
            array(),
            $api->getErrors('owner_id'),
            'Incorrectly rejected an Admin as the owner of an Api.'
        );
    }
}
