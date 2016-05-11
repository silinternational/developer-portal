<?php

class UnitTestUser extends WebUser {
    private $_role = 'initial_role'; //'user'; //'admin';
    public $loginUrl = array('/auth/testLogin');
    public $display_name = '';
    public $user_id = 1;
 
    function setRole($role) {
        $this->_role = $role;
//        Yii::log('setRole2: ' . $this->_role, 'debug');
    }
 
    function getRole() {
        //$this->_role = 'user';
        return $this->_role;
    }
    
    /**
     * Log the test user in (if necessary), then indicate whether they should be
     * allowed access to something that requires the given role.
     * 
     * See the documentation at the parent class's version of this function.
     * 
     * @param string $role The role that the user must have to be allowed access.
     * @param array $params IGNORED
     * @param boolean $allowCaching IGNORED
     * @return boolean Whether the user meets the requirements and thus should
     *     be allowed access.
     * 
     * @see WebUser->checkAccess(...)
     */
    public function checkAccess($role, $params = array(), $allowCaching = true)
    {   
        // First log the user in (if necessary).
        $userRole = $this->getRole();
        if ($userRole == 'initial_role')
        {
            $identity = new TestUserIdentity('guest', '');
            $identity->authenticate(); 
            Yii::app()->user->login($identity);
        }
        
        // Now run the various access checks and return the result.
        return parent::checkAccess($role, $params, $allowCaching);
    }
}
