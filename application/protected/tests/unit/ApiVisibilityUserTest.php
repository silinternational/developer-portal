<?php
namespace Sil\DevPortal\tests\unit;

use Sil\DevPortal\models\ApiVisibilityUser;

/**
 * @method ApiVisibilityUser apiVisibilityUsers(string $fixtureName)
 */
class ApiVisibilityUserTest extends \CDbTestCase
{
    public $fixtures = array(
        'apiVisibilityUsers' => '\Sil\DevPortal\models\ApiVisibilityUser',
        'users' => '\Sil\DevPortal\models\User',
    );
    
    public function testGetInviteeEmailAddress_hasInvitedUserEmail()
    {
        // Arrange:
        $expected = 'someone@example.com';
        $apiVisibilityUser = new ApiVisibilityUser();
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
