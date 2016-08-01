<?php
namespace Sil\DevPortal\tests\unit;

/**
 * @method \Api apis(string $fixtureName)
 * @method \ApiVisibilityDomain apiVisibilityDomains(string $fixtureName)
 * @method \Key keys(string $fixtureName)
 * @method \User users(string $fixtureName)
 */
class ApiVisibilityDomainTest extends \CDbTestCase
{
    public $fixtures = array(
        'api' => 'Api',
        'apiVisibilityDomains' => 'ApiVisibilityDomain',
        'keys' => 'Key',
        'users' => 'User',
    );
    
    public function testFixtureDataValidity()
    {
        foreach ($this->apiVisibilityDomains as $fixtureName => $fixtureData) {
            $apiVisibilityDomain = $this->apiVisibilityDomains($fixtureName);
            $this->assertTrue($apiVisibilityDomain->delete(), sprintf(
                'Could not delete apiVisibilityDomain fixture %s: %s',
                $fixtureName,
                print_r($apiVisibilityDomain->getErrors(), true)
            ));
            $apiVisibilityDomainOnInsert = new \ApiVisibilityDomain();
            $apiVisibilityDomainOnInsert->setAttributes($fixtureData, false);
            $this->assertTrue($apiVisibilityDomainOnInsert->save(), sprintf(
                'ApiVisibilityDomain fixture "%s" (ID %s) does not have valid data: %s',
                $fixtureName,
                $apiVisibilityDomainOnInsert->api_visibility_domain_id,
                var_export($apiVisibilityDomainOnInsert->getErrors(), true)
            ));
        }
    }
    
    public function testGetDependentKeys()
    {
        // Arrange:
        $apiVisibilityDomain = $this->apiVisibilityDomains('avdWithTwoDependentKeys');
        $dependentKey1 = $this->keys('firstUserKeyDependentOnAvd3');
        $dependentKey2 = $this->keys('secondUserKeyDependentOnAvd3');
        $notDependentKey = $this->keys('keyNotDependentOnAvd3');
        $deniedKey = $this->keys('deniedKeyThusNotDependentOnAvd3AnyMore');
        
        // Act:
        $actualDependentKeys = $apiVisibilityDomain->getDependentKeys();
        
        // Assert:
        $this->assertTrue(
            is_array($actualDependentKeys),
            'Failed to return an array.'
        );
        $actualDependentKeyIds = array();
        foreach ($actualDependentKeys as $actualDependentKey) {
            $actualDependentKeyIds[] = $actualDependentKey->key_id;
        }
        
        $this->assertContains($dependentKey1->key_id, $actualDependentKeyIds);
        $this->assertContains($dependentKey2->key_id, $actualDependentKeyIds);
        $this->assertNotContains($notDependentKey->key_id, $actualDependentKeyIds);
        $this->assertNotContains(
            $deniedKey->key_id,
            $actualDependentKeyIds,
            'Incorrectly included a denied Key in the list of dependent Keys.'
        );
    }
    
    public function testGetDependentKeys_keyAllowedByTwoAvds()
    {
        // Arrange:
        $apiVisibilityDomain = $this->apiVisibilityDomains('firstAvdAllowingKeyAllowedByTwoAvds');
        $keyAllowedByTwoAvds = $this->keys('allowedByTwoApiVisibilityDomains');
        
        // Act:
        $actualDependentKeys = $apiVisibilityDomain->getDependentKeys();
        
        // Assert:
        $actualDependentKeyIds = array();
        foreach ($actualDependentKeys as $actualDependentKey) {
            $actualDependentKeyIds[] = $actualDependentKey->key_id;
        }
        $this->assertNotContains(
            $keyAllowedByTwoAvds->key_id,
            $actualDependentKeyIds,
            'Incorrectly said a Key was dependent on (i.e. would not be '
            . 'allowed without) an ApiVisibilityDomain (AVD) when there was a '
            . '2nd AVD also allowing the Key.'
        );
    }
    
    public function testGetLinksToDependentKeysAsHtmlList()
    {
        // Arrange:
        $apiVisibilityDomain = $this->apiVisibilityDomains('avdWithTwoDependentKeys');
        
        // Act:
        $linksAsHtmlList = $apiVisibilityDomain->getLinksToDependentKeysAsHtmlList();
        
        // Assert:
        $this->assertTrue(is_string($linksAsHtmlList));
        $this->assertContains('<ul>', $linksAsHtmlList);
        $this->assertContains('</ul>', $linksAsHtmlList);
    }
    
    public function testIsApparentlyValidDomain_valid()
    {
        // Arrange:
        $apiVisibilityDomain = new \ApiVisibilityDomain();
        $apiVisibilityDomain->domain = 'example.com';
        
        // Act:
        $apiVisibilityDomain->isApparentlyValidDomain('domain');
        
        // Assert:
        $this->assertNull($apiVisibilityDomain->getError('domain'));
    }
    
    public function testIsApparentlyValidDomain_hasSpace()
    {
        // Arrange:
        $apiVisibilityDomain = new \ApiVisibilityDomain();
        $apiVisibilityDomain->domain = 'exam ple.com';
        
        // Act:
        $apiVisibilityDomain->isApparentlyValidDomain('domain');
        
        // Assert:
        $this->assertNotNull(
            $apiVisibilityDomain->getError('domain'),
            'Incorrectly reported that "' . $apiVisibilityDomain->domain . '" is valid.'
        );
    }
    
    public function testIsApparentlyValidDomain_noDomain()
    {
        // Arrange:
        $apiVisibilityDomain = new \ApiVisibilityDomain();
        
        // Act:
        $apiVisibilityDomain->isApparentlyValidDomain('domain');
        
        // Assert:
        $this->assertNotNull(
            $apiVisibilityDomain->getError('domain'),
            'Incorrectly reported that null is a valid domain name.'
        );
    }
}
