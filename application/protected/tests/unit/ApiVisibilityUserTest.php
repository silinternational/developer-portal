<?php
namespace Sil\DevPortal\tests\unit;

/**
 * @method \ApiVisibilityUser apiVisibilityUsers(string $fixtureName)
 */
class ApiVisibilityUserTest extends \CDbTestCase
{
    public $fixtures = array(
        'apiVisibilityUsers' => 'ApiVisibilityUser',
        'users' => 'User',
    );
    
    public function testGetInviteeEmailAddress_hasInvitedUserEmail()
    {
        // Arrange:
        $expected = 'someone@example.com';
        $apiVisibilityUser = new \ApiVisibilityUser();
        $apiVisibilityUser->invited_user_email = $expected;
        
        // Act:
        $actual = $apiVisibilityUser->getInviteeEmailAddress();
        
        // Assert:
        $this->assertSame($expected, $actual);
    }
    
    public function testGetInviteeEmailAddress_noInvitedUserEmail()
    {
        // Arrange:
        $apiVisibilityUser = $this->apiVisibilityUsers('avu1');
        $expected = $apiVisibilityUser->invitedUser->email;
        
        // Act:
        $actual = $apiVisibilityUser->getInviteeEmailAddress();
        
        // Assert:
        $this->assertSame($expected, $actual);
    }
}
