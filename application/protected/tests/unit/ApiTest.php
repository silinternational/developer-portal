<?php

use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\ApiVisibilityDomain;
use Sil\DevPortal\models\ApiVisibilityUser;
use Sil\DevPortal\models\Key;
use Sil\DevPortal\models\User;

/**
 * @method Api apis(string $fixtureName)
 */
class ApiTest extends DeveloperPortalTestCase
{
    public $fixtures = array(
        'apis' => '\Sil\DevPortal\models\Api',
        'keys' => '\Sil\DevPortal\models\Key',
        'apiVisibilityDomains' => '\Sil\DevPortal\models\ApiVisibilityDomain',
        'apiVisibilityUsers' => '\Sil\DevPortal\models\ApiVisibilityUser',
        'users' => '\Sil\DevPortal\models\User',
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
            /* @var $api Api */
            $api = $this->apis($fixtureName);
            Key::model()->deleteAllByAttributes(array(
                'api_id' => $api->api_id,
            ));
            $this->assertTrue($api->delete(), sprintf(
                'Could not delete api fixture %s: %s',
                $fixtureName,
                print_r($api->getErrors(), true)
            ));
            $apiOnInsert = new Api();
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
        $this->confirmConstantsDiffer('\Sil\DevPortal\models\Api', 'APPROVAL_TYPE_',
                Api::getApprovalTypes());
    }
    
    public function testConfirmProtocolsDiffer()
    {
        // Make sure the protocol constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('\Sil\DevPortal\models\Api', 'PROTOCOL_',
                Api::getProtocols());
    }
    
    public function testConfirmStrictSslsDiffer()
    {
        // Make sure the strict SSL constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('\Sil\DevPortal\models\Api', 'STRICT_SSL_',
                Api::getStrictSsls());
    }
    
    public function testConfirmVisibilityConstantsDiffer()
    {
        $this->confirmConstantsDiffer(
            '\Sil\DevPortal\models\Api',
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
        $api->visibility = Api::VISIBILITY_PUBLIC;
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
    
    public function testRulesForEmbeddedDocsUrl()
    {
        // Arrange:
        $testCases = [
            
        ];
        
        // Act:
        
        // Assert:
        
    }
    
    public function testFindByPk_nullPkAfterInsert()
    {
        // Arrange:
        /* Ensure that the MySQL/MariaDB sql_auto_is_null setting is on for this
         * test. See http://stackoverflow.com/a/32396258/3813891 for details. */
        \Yii::app()->getDb()->pdoInstance->exec('SET sql_auto_is_null = 1;');
        $newApi = new Api();
        $newApi->attributes = array(
            'code' => 'test-1467819002',
            'display_name' => 'Test 1467819002',
            'endpoint' => 'local',
            'default_path' => '/test-1467819002',
            'queries_second' => 10,
            'queries_day' => 1000,
            'endpoint_timeout' => 10,
        );
        $insertResult = $newApi->save();
        
        // Pre-assert:
        $this->assertTrue(
            $insertResult,
            'Failed to insert a new Api record as part of the setup for this '
            . 'test: ' . print_r($newApi->getErrors(), true)
        );
        
        // Act:
        $result = Api::model()->findByPk(null);
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when requesting a null primary key after '
            . 'inserting a new record.'
        );
    }
    
    public function testGenerateBadgeHtml_hasBadgeValue()
    {
        // Arrange:
        $badgeValue = 3;
        
        // Act:
        $result = Api::generateBadgeHtml($badgeValue);
        
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
        $result = Api::generateBadgeHtml(3, $extraCssClass);
        
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
        $result = Api::generateBadgeHtml(3, null, $hoverTitle);
        
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
        $result = Api::generateBadgeHtml(3, null, null, $linkTarget);
        
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
    
    public function testGetAdditionalHeadersArray()
    {
        // Arrange:
        $apiWithNoAdditionalHeaders = new Api();
        
        // Act:
        $result = $apiWithNoAdditionalHeaders->getAdditionalHeadersArray();
        
        // Assert:
        $this->assertEmpty($result);
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
	
    public function testGetInvitedDomainsCountBadgeHtml_hasBadgeValue()
    {
        // Arrange:
        $api = $this->apis('apiVisibleByInvitationOnlyWith2UserAnd1DomainInvitation');
        
        // Act:
        $result = $api->getInvitedDomainsCountBadgeHtml();
        
        // Assert:
        $this->assertContains(
            strval(1),
            $result,
            'Failed to include the correct number of invited domains in the '
            . 'generated badge HTML.'
        );
        $this->assertContains(
            strval(1),
            strip_tags($result),
            'Failed to include the correct number of invited domains in the '
            . 'text contents of the generated badge HTML.'
        );
    }
    
    public function testGetInvitedUsersCountBadgeHtml_hasBadgeValue()
    {
        // Arrange:
        $api = $this->apis('apiVisibleByInvitationOnlyWith2UserAnd1DomainInvitation');
        
        // Act:
        $result = $api->getInvitedUsersCountBadgeHtml();
        
        // Assert:
        $this->assertContains(
            strval(2),
            $result,
            'Failed to include the correct number of invited users in the '
            . 'generated badge HTML.'
        );
        $this->assertContains(
            strval(2),
            strip_tags($result),
            'Failed to include the correct number of invited users in the text '
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
        
        // Act:
        $result = $api->getApiProxyDomain();
        
        // Assert:
        $this->assertNotEmpty($result);
    }
    
    public function testGetApiProxyProtocol()
    {
        // Arrange:
        $api = new Api;
        
        // Act:
        $result = $api->getApiProxyProtocol();
        
        // Assert:
        $this->assertTrue(
            in_array($result, ['http', 'https']),
            'Failed to return a valid API proxy protocol.'
        );
    }
    
    public function testGetPopularApis()
    {
        // Arrange: (n/a)
        
        // Act:
        $popularApis = Api::getPopularApis();
        
        // Assert:
        $this->assertGreaterThan(1, count($popularApis));
        $this->assertInstanceOf('\Sil\DevPortal\models\Api', $popularApis[0]);
        $this->assertGreaterThan(
            $popularApis[1]->approvedKeyCount,
            $popularApis[0]->approvedKeyCount,
            'The most popular API incorrectly has fewer approved keys than the '
            . '2nd most popular API.'
        );
    }
    
    public function testGetPublicUrl()
    {
        // Arrange:
        $api = $this->apis('api4');
        $expected = $api->getApiProxyProtocol() . '://' . 
                    $api->code . '.' . $api->getApiProxyDomain() . '/';
        
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
        $this->assertClassHasRelation(new Api(), 'keys', '\Sil\DevPortal\models\Key');
    }
    
	public function testHasOwnerRelationship()
    {
        // Confirm that the relationship is set up between the classes.
        $this->assertClassHasRelation(new Api(), 'owner', '\Sil\DevPortal\models\User');
    }
    
    public function testIsPubliclyVisible_no()
    {
        // Arrange:
        /* @var $api Api */
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
        /* @var $api Api */
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
        $this->assertTrue(
            $result,
            'Incorrectly hid public Api from unauthenticated (null) User.'
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
        /* @var $api Api */
        $api = $this->apis('apiVisibleByInvitationOnlyWithNoInvitations');
        /* @var $user User */
        $user = $this->users('userNotInvitedToSeeAnyApi');
        
        // Pre-assert:
        $apiVisibilityDomains = ApiVisibilityDomain::model()->findAllByAttributes(array(
            'api_id' => $api->api_id,
        ));
        $this->assertCount(
            0,
            $apiVisibilityDomains,
            'This test requires an Api that no domains have been invited to see.'
        );
        $apiVisibilityUsers = ApiVisibilityUser::model()->findAllByAttributes(array(
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
        $newApi->code = $existingApi->code . '-dup';
        $newApi->display_name = $existingApi->display_name . '-duplicate';
        $newApi->endpoint = $existingApi->endpoint . '-duplicate';
        
        // Act:
        $result = $newApi->validate();
        
        // Assert:
        $this->assertEmpty($newApi->errors, $newApi->getErrorsForConsole());
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
        $newApi->display_name = $existingApi->display_name . '-duplicate';
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
        $newApi->display_name = $existingApi->display_name . '-duplicate';
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
    
    public function testApprovedKeyCount()
    {
        // Arrange:
        $api = $this->apis('apiWithTwoKeys');
        
        // Act:
        $actual = $api->approvedKeyCount;
        
        // Assert:
        $this->assertEquals(
            2,
            $actual,
            'Failed to report the correct number of (approved) keys for an Api.'
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
    
    public function testRequireSignature_acceptValidValues()
    {
        // Arrange:
        $api = new Api();
        $validValues = ['yes', 'no'];
        
        foreach ($validValues as $validValue) {
            
            // Act:
            $api->require_signature = $validValue;
            $result = $api->validate(['require_signature']);

            // Assert:
            $this->assertTrue(
                $result,
                'Failed to accept valid value (' . var_export($validValue, true)
                . '): ' . PHP_EOL . $api->getErrorsForConsole()
            );
        }
    }
    
    public function testRequireSignature_rejectInvalidValues()
    {
        // Arrange:
        $api = new Api();
        $invalidValues = [0, 1, '0', '1', '', 'abc'];
        
        foreach ($invalidValues as $invalidValue) {
            
            // Act:
            $api->require_signature = $invalidValue;
            $result = $api->validate(['require_signature']);

            // Assert:
            $this->assertFalse(
                $result,
                'Accepted invalid value: ' . var_export($invalidValue, true)
            );
        }
    }
    
    public function testRequiresSignature_invalidValue()
    {
        // Arrange:
        $api = new Api();
        $api->require_signature = '';
        
        // Act:
        $result = $api->requiresSignature();
        
        // Assert:
        $this->assertTrue($result);
    }
    
    public function testRequiresSignature_no()
    {
        // Arrange:
        $api = new Api();
        $api->require_signature = Api::REQUIRE_SIGNATURES_NO;
        
        // Act:
        $result = $api->requiresSignature();
        
        // Assert:
        $this->assertFalse($result);
    }
    
    public function testRequiresSignature_yes()
    {
        // Arrange:
        $api = new Api();
        $api->require_signature = Api::REQUIRE_SIGNATURES_YES;
        
        // Act:
        $result = $api->requiresSignature();
        
        // Assert:
        $this->assertTrue($result);
    }
    
    public function testGetRequiresSignatureText_no()
    {
        // Arrange:
        $api = new Api();
        $api->require_signature = Api::REQUIRE_SIGNATURES_NO;
        $expected = 'No';
        
        // Act:
        $actual = $api->getRequiresSignatureText();
        
        // Assert:
        $this->assertSame($expected, $actual);
    }
    
    public function testGetRequiresSignatureText_yes()
    {
        // Arrange:
        $api = new Api();
        $api->require_signature = Api::REQUIRE_SIGNATURES_YES;
        $expected = 'Yes';
        
        // Act:
        $actual = $api->getRequiresSignatureText();
        
        // Assert:
        $this->assertSame($expected, $actual);
    }
    
    public function testSignatureWindowRules()
    {
        // Arrange:
        $testCases = [
            'null' => [
                'value' => null,
                'valid' => false,
            ],
            'an empty string' => [
                'value' => '',
                'valid' => false,
            ],
            'a negative value' => [
                'value' => -1,
                'valid' => false,
            ],
            'zero' => [
                'value' => 0,
                'valid' => true,
            ],
            'a normal value' => [
                'value' => 3,
                'valid' => true,
            ],
            'the max allowed value' => [
                'value' => Api::SIGNATURE_WINDOW_MAX,
                'valid' => true,
            ],
            'a value too big' => [
                'value' => Api::SIGNATURE_WINDOW_MAX + 1,
                'valid' => false,
            ],
        ];
        $api = new Api();
        foreach ($testCases as $description => $testCase) {
            $api->signature_window = $testCase['value'];
            $expectedResult = $testCase['valid'];
            
            // Act:
            $actualResult = $api->validate(['signature_window']);
            
            // Assert:
            $this->assertSame($expectedResult, $actualResult, sprintf(
                'When using %s for the signature window, it incorrectly came '
                . 'back as %s.',
                $description,
                ($actualResult ? 'valid' : 'not valid')
            ));
        }
    }
    
    public function testUniqueDisplayNameCaseInsensitive()
    {
        // Arrange:
        $existingApi = $this->apis('api1');
        $newApi = new Api();
        $newApi->display_name = strtoupper($existingApi->display_name);
        
        // Pre-assert:
        $this->assertNotSame($existingApi->display_name, $newApi->display_name);
        $this->assertSame(
            strtoupper($existingApi->display_name),
            strtoupper($newApi->display_name)
        );
        
        // Act:
        $result = $newApi->validate(array('display_name'));
        
        // Assert:
        $this->assertFalse(
            $result,
            'Failed to reject a duplicate display_name.'
        );
    }
    
    public function testUpdateKeysRateLimitsToMatch()
    {
        // Arrange:
        /* @var $api Api */
        $api = $this->apis('apiWithTwoKeys');
        /* @var $key1 Key */
        $key1 = $api->keys[0];
        $key1->queries_day += 1000;
        $key1->queries_second += 10;
        $key1UpdateResult = $key1->save();
        /* @var $key2 Key */
        $key2 = $api->keys[1];
        
        // Pre-assert:
        $this->assertTrue(
            $key1UpdateResult,
            'Failed up change a Key\'s rate limits as part of the setup for '
            . 'this test: ' . print_r($key1->getErrors(), true)
        );
        $this->assertNotEquals($api->queries_day, $key1->queries_day);
        $this->assertNotEquals($api->queries_second, $key1->queries_second);
        $this->assertEquals($api->queries_day, $key2->queries_day);
        $this->assertEquals($api->queries_second, $key2->queries_second);
        
        // Act:
        $api->updateKeysRateLimitsToMatch();
        
        // Assert:
        $key1->refresh();
        $this->assertEquals(
            $api->queries_day,
            $key1->queries_day,
            'Failed to set Key\'s queries_day back to match Api\'s.'
        );
        $this->assertEquals(
            $api->queries_second,
            $key1->queries_second,
            'Failed to set Key\'s queries_second back to match Api\'s.'
        );
        $key2->refresh();
        $this->assertEquals(
            $api->queries_day,
            $key2->queries_day,
            'Already-matching Key\'s queries_day was (incorrectly) changed.'
        );
        $this->assertEquals(
            $api->queries_second,
            $key2->queries_second,
            'Already-matching Key\'s queries_second was (incorrectly) changed.'
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
