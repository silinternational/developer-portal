<?php
namespace Sil\DevPortal\tests\unit;

class ApiVisibilityDomainTest extends \CDbTestCase
{
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
