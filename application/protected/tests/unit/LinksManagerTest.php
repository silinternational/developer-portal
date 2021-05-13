<?php

use Sil\DevPortal\tests\DbTestCase;

class LinksManagerTest extends DbTestCase
{
    public $fixtures = array(
        'apis' => '\Sil\DevPortal\models\Api',
        'keys' => '\Sil\DevPortal\models\Key',
        'users' => '\Sil\DevPortal\models\User',
    );
    
    public function testGenerateActionsDropdownHtml_emptyLinksArray()
    {
        // Arrange:
        $actionLinks = array();
        
        // Act:
        $result = LinksManager::generateActionsDropdownHtml($actionLinks);
        
        // Assert:
        $this->assertSame(
            '',
            $result,
            'Failed to return an empty string when no action links were given.'
        );
    }
    
    public function testGenerateActionsDropdownHtml_nullLinksArray()
    {
        // Arrange:
        $actionLinks = null;
        
        // Act:
        $result = LinksManager::generateActionsDropdownHtml($actionLinks);
        
        // Assert:
        $this->assertSame(
            '',
            $result,
            'Failed to return an empty string when a null array of action '
            . 'links was given.'
        );
    }
    
    public function testGenerateActionsDropdownHtml_hasLinksHtml()
    {
        // Arrange:
        $actionLink1 = new ActionLink('/fake/link/url/');
        $actionLink2 = new ActionLink(
            '/different/fake/url/',
            'Fake link text',
            'fake-icon'
        );
        $actionLinks = array($actionLink1, $actionLink2);
        $actionLink1Html = $actionLink1->getAsHtml();
        $actionLink2Html = $actionLink2->getAsHtml();
        
        // Act:
        $result = LinksManager::generateActionsDropdownHtml($actionLinks);
        
        // Assert:
        $this->assertStringContainsString(
            $actionLink1Html,
            $result,
            'Failed to include the HTML for the first ActionLink given.'
        );
        $this->assertStringContainsString(
            $actionLink2Html,
            $result,
            'Failed to include the HTML for the second ActionLink given.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_noUser()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = null;
        $expected = array();
        
        // Act:
        $actual = LinksManager::getApiDetailsActionLinksForUser($api, $user);
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return no ActionLinks when given a null User.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_noApi()
    {
        // Arrange:
        $api = null;
        $user = $this->users('user18');
        $expected = array();
        
        // Act:
        $actual = LinksManager::getApiDetailsActionLinksForUser($api, $user);
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return no ActionLinks when given a null Api.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_adminUser_apiWithKeys()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfAdmin');
        $expectedLinkTexts = array(
            'Get Key',
            'Show Active Keys',
            'Show Pending Keys',
            'See API Usage',
            'See API Usage By Key',
            'Email Users With Keys',
            'Edit API',
            'Delete API',
        );
        
        // Act:
        $actionLinks = LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct links (based on link text) for an '
            . 'admin user.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_adminUser_apiWithoutKeys()
    {
        // Arrange:
        $api = $this->apis('apiWithZeroKeys');
        $user = $this->users('userWithRoleOfAdmin');
        $expectedLinkTexts = array(
            'Request Key',
            'Show Active Keys',
            'Show Pending Keys',
            'See API Usage',
            'See API Usage By Key',
            'Edit API',
            'Delete API',
        );
        
        // Act:
        $actionLinks = LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct links (based on link text) for an '
            . 'admin user.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_ownerOfApi()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('user18');
        $expectedLinkTexts = array(
            'Get Key',
            'Show Active Keys',
            'Show Pending Keys',
            'See API Usage',
            'See API Usage By Key',
            'Email Users With Keys',
            'Edit API',
            'Delete API',
        );
        
        // Act:
        $actionLinks = LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct links (based on link text) for an '
            . 'API Owner who DOES own the given API.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_notOwnerOfApi()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfOwner');
        $expectedLinkTexts = array(
            'Get Key',
        );
        
        // Act:
        $actionLinks = LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct links (based on link text) for an '
            . 'API Owner who does NOT own the given API.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_developerUserWithKey()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithKeyToApiOwnedByUser18');
        $expectedLinkTexts = array(
            'View Key Details',
        );
        
        // Act:
        $actionLinks = LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct links (based on link text) for a '
            . 'normal developer user.'
        );
    }
    
    public function testGetApiDetailsActionLinksForUser_developerUserWithoutKey()
    {
        // Arrange:
        $api = $this->apis('apiOwnedByUser18');
        $user = $this->users('userWithRoleOfUser');
        $expectedLinkTexts = array(
            'Get Key',
        );
        
        // Act:
        $actionLinks = LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct links (based on link text) for a '
            . 'normal developer user.'
        );
    }
    
    public function testGetDashboardKeyActionLinks_noKey()
    {
        // Arrange:
        $key = null;
        $expected = array();
        
        // Act:
        $actual = LinksManager::getDashboardKeyActionLinks($key);
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return no ActionLinks when given a null Key.'
        );
    }
    
    public function testGetDashboardKeyActionLinks_realKey()
    {
        // Arrange:
        $key = $this->keys('pendingKeyUser6');
        $expectedLinkTexts = array(
            'View Details',
        );
        
        // Act:
        $actionLinks = LinksManager::getDashboardKeyActionLinks(
            $key
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct link (based on link text).'
        );
    }
    
    public function testGetKeyDetailsActionLinksForUser_noKey()
    {
        // Arrange:
        $key = null;
        $user = $this->users('userWithRoleOfAdmin');
        $expected = array();
        
        // Act:
        $actual = LinksManager::getKeyDetailsActionLinksForUser(
            $key,
            $user
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Failed to return no ActionLinks when given a null Key.'
        );
    }
    
    public function testGetKeyDetailsActionLinksForUser_canRevokeKey()
    {
        // Arrange:
        $key = $this->keys('approvedKey');
        $user = $this->users('userWithRoleOfAdmin');
        $expectedLinkText = 'Revoke Key';
        
        // Act:
        $actionLinks = LinksManager::getKeyDetailsActionLinksForUser(
            $key,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertContains(
            $expectedLinkText,
            $actualLinksTexts,
            'Failed to include a link to revoke the Key (based on link text).'
        );
    }
    
    public function testGetDashboardKeyActionLinks_cannotRevokeKey()
    {
        // Arrange:
        $key = $this->keys('pendingKeyUser6');
        $user = $this->users('userWithNoPendingKeys');
        $expectedLinkTexts = array();
        
        // Act:
        $actionLinks = LinksManager::getKeyDetailsActionLinksForUser(
            $key,
            $user
        );
        $actualLinksTexts = array();
        foreach ($actionLinks as $actionLink) {
            $actualLinksTexts[] = strip_tags($actionLink->getAsHtml());
        }
        
        // Assert:
        $this->assertEquals(
            $expectedLinkTexts,
            $actualLinksTexts,
            'Failed to include the correct link (based on link text).'
        );
    }
}
