<?php

class WebUserTest extends CTestCase
{
    protected function createCheckAccessErrorMessage(
        $expectedResult,
        $requiredRole,
        $usersRole = null,
        $userIsGuest = null
    ) {
        return sprintf(
            'Failed to %s access by a%s user%s to something accessible by %s.',
            ($expectedResult ? 'allow' : 'prevent'),
            ($userIsGuest === null ? '' : ($userIsGuest ? ' guest' : 'n authenticated')),
            ($usersRole === null ? '' : ' with a role of "' . $usersRole . '"'),
            $this->getDescriptionOfRequiredRole($requiredRole)
        );
    }
    
    protected function getDescriptionOfRequiredRole($role)
    {
        if ($role === '*') {
            $result = 'users with any role';
        } elseif ($role === '?') {
            $result = 'guest users';
        } elseif ($role === '@') {
            $result = 'authenticated users';
        } else {
            $result = 'users with a role of ' . var_export($role, true);
        }
        return $result;
    }
    
    public function testCheckAccess_anyRole_allowed()
    {
        // Arrange (define):
        $usersRole = null;
        $requiredRole = '*';
        $expectedResult = true;
        
        // Arrange (assemble):
        $WebUserStub = $this->createPartialMock('WebUser', array('getRole'));
        $WebUserStub->expects($this->any())
                    ->method('getRole')
                    ->will($this->returnValue($usersRole));

        // Act:
        $actualResult = $WebUserStub->checkAccess($requiredRole);
        
        // Assert:
        $this->assertEquals(
            $usersRole,
            $WebUserStub->getRole(),
            'Incorrectly stubbed getRole method on WebUser class.'
        );
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            $this->createCheckAccessErrorMessage(
                $expectedResult,
                $requiredRole,
                $usersRole
            )
        );
    }
    
    public function testCheckAccess_mustBeGuest_yes()
    {
        // Arrange (define):
        $userIsGuest = true;
        $usersRole = null;
        $requiredRole = '?';
        $expectedResult = true;
        
        // Arrange (assemble):
        $WebUserStub = $this->createPartialMock(
            'WebUser',
            array('getIsGuest', 'getRole')
        );
        $WebUserStub->expects($this->any())
                    ->method('getRole')
                    ->will($this->returnValue($usersRole));
        $WebUserStub->expects($this->any())
                    ->method('getIsGuest')
                    ->will($this->returnValue($userIsGuest));

        // Act:
        $actualResult = $WebUserStub->checkAccess($requiredRole);
        
        // Assert:
        $this->assertEquals(
            $usersRole,
            $WebUserStub->getRole(),
            'Incorrectly stubbed getRole method on WebUser class.'
        );
        $this->assertEquals(
            $userIsGuest,
            $WebUserStub->isGuest,
            'Incorrectly stubbed getIsGuest method on WebUser class.'
        );
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            $this->createCheckAccessErrorMessage(
                $expectedResult,
                $requiredRole,
                $usersRole,
                $userIsGuest
            )
        );
    }
    
    public function testCheckAccess_mustBeGuest_no()
    {
        // Arrange (define):
        $userIsGuest = false;
        $usersRole = null;
        $requiredRole = '?';
        $expectedResult = false;
        
        // Arrange (assemble):
        $WebUserStub = $this->createPartialMock(
            'WebUser',
            array('getIsGuest', 'getRole')
        );
        $WebUserStub->expects($this->any())
                    ->method('getRole')
                    ->will($this->returnValue($usersRole));
        $WebUserStub->expects($this->any())
                    ->method('getIsGuest')
                    ->will($this->returnValue($userIsGuest));

        // Act:
        $actualResult = $WebUserStub->checkAccess($requiredRole);
        
        // Assert:
        $this->assertEquals(
            $usersRole,
            $WebUserStub->getRole(),
            'Incorrectly stubbed getRole method on WebUser class.'
        );
        $this->assertEquals(
            $userIsGuest,
            $WebUserStub->isGuest,
            'Incorrectly stubbed getIsGuest method on WebUser class.'
        );
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            $this->createCheckAccessErrorMessage(
                $expectedResult,
                $requiredRole,
                $usersRole,
                $userIsGuest
            )
        );
    }
    
    public function testCheckAccess_mustBeAuthenticated_yes()
    {
        // Arrange (define):
        $userIsGuest = false;
        $usersRole = null;
        $requiredRole = '@';
        $expectedResult = true;
        
        // Arrange (assemble):
        $WebUserStub = $this->createPartialMock(
            'WebUser',
            array('getIsGuest', 'getRole')
        );
        $WebUserStub->expects($this->any())
                    ->method('getRole')
                    ->will($this->returnValue($usersRole));
        $WebUserStub->expects($this->any())
                    ->method('getIsGuest')
                    ->will($this->returnValue($userIsGuest));

        // Act:
        $actualResult = $WebUserStub->checkAccess($requiredRole);
        
        // Assert:
        $this->assertEquals(
            $usersRole,
            $WebUserStub->getRole(),
            'Incorrectly stubbed getRole method on WebUser class.'
        );
        $this->assertEquals(
            $userIsGuest,
            $WebUserStub->isGuest,
            'Incorrectly stubbed getIsGuest method on WebUser class.'
        );
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            $this->createCheckAccessErrorMessage(
                $expectedResult,
                $requiredRole,
                $usersRole,
                $userIsGuest
            )
        );
    }
    
    public function testCheckAccess_mustBeAuthenticated_no()
    {
        // Arrange (define):
        $userIsGuest = true;
        $usersRole = null;
        $requiredRole = '@';
        $expectedResult = false;
        
        // Arrange (assemble):
        $WebUserStub = $this->createPartialMock(
            'WebUser',
            array('getIsGuest', 'getRole')
        );
        $WebUserStub->expects($this->any())
                    ->method('getRole')
                    ->will($this->returnValue($usersRole));
        $WebUserStub->expects($this->any())
                    ->method('getIsGuest')
                    ->will($this->returnValue($userIsGuest));

        // Act:
        $actualResult = $WebUserStub->checkAccess($requiredRole);
        
        // Assert:
        $this->assertEquals(
            $usersRole,
            $WebUserStub->getRole(),
            'Incorrectly stubbed getRole method on WebUser class.'
        );
        $this->assertEquals(
            $userIsGuest,
            $WebUserStub->isGuest,
            'Incorrectly stubbed getIsGuest method on WebUser class.'
        );
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            $this->createCheckAccessErrorMessage(
                $expectedResult,
                $requiredRole,
                $usersRole,
                $userIsGuest
            )
        );
    }
    
    public function testCheckAccess_lacksRequiredRole()
    {
        // Arrange (define):
        $userIsGuest = false;
        $usersRole = 'fakeRole';
        $requiredRole = 'differentFakeRole';
        $expectedResult = false;
        
        // Arrange (assemble):
        $WebUserStub = $this->createPartialMock(
            'WebUser',
            array('getIsGuest', 'getRole')
        );
        $WebUserStub->expects($this->any())
                    ->method('getRole')
                    ->will($this->returnValue($usersRole));
        $WebUserStub->expects($this->any())
                    ->method('getIsGuest')
                    ->will($this->returnValue($userIsGuest));

        // Act:
        $actualResult = $WebUserStub->checkAccess($requiredRole);
        
        // Assert:
        $this->assertEquals(
            $usersRole,
            $WebUserStub->getRole(),
            'Incorrectly stubbed getRole method on WebUser class.'
        );
        $this->assertEquals(
            $userIsGuest,
            $WebUserStub->isGuest,
            'Incorrectly stubbed getIsGuest method on WebUser class.'
        );
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            $this->createCheckAccessErrorMessage(
                $expectedResult,
                $requiredRole,
                $usersRole,
                $userIsGuest
            )
        );
    }
    
    public function testHasFlashes_no()
    {
        // Arrange:
        $fakeFlashMessages = array();
        $webUserStub = $this->createPartialMock('WebUser', array('getFlashes'));
        $webUserStub->expects($this->any())
                    ->method('getFlashes')
                    ->will($this->returnValue($fakeFlashMessages));
        
        // Act:
        $result = $webUserStub->hasFlashes();
        
        // Assert:
        $this->assertFalse(
            $result,
            'Incorrectly reported that a WebUser has Yii flash messages.'
        );
    }
    
    public function testHasFlashes_yes()
    {
        // Arrange:
        $fakeFlashMessages = array(
            'test' => 'A fake flash message.',
        );
        $webUserStub = $this->createPartialMock('WebUser', array('getFlashes'));
        $webUserStub->expects($this->any())
                    ->method('getFlashes')
                    ->will($this->returnValue($fakeFlashMessages));
        
        // Act:
        $result = $webUserStub->hasFlashes();
        
        // Assert:
        $this->assertTrue(
            $result,
            'Failed to report that a WebUser has a Yii flash message.'
        );
    }
}
