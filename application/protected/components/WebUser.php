<?php

class WebUser extends CWebUser
{
    private $_model = null;
    
    /**
     * @todo Remove references to access groups, since we no longer use those.
     */
    public function getAccessGroups()
    {
        // Get the User model.
        $user = $this->getModel();
        // $user = \Yii::app()->user->user; // = $this->getState('user');
        
        // If there was a User model, return it's list of access groups.
        // Otherwise return an empty list of access groups.
        return ($user instanceof User ? $user->getAccessGroups() : array());
    }
    
    public function getAuthProvider()
    {
        $user = $this->getModel();
        return ($user instanceof \User ? $user->auth_provider : null);
    }
    
    public function getAuthType()
    {
        return $this->getState('authType', null);
    }

    /**
     * Get the user's role.
     * 
     * @return string
     */
    public function getRole()
    {
        $user = $this->getModel();
        if ($user !== null) {
            return $user->role;
        } else {
            return null;
        }
    }
    
    /**
     * Get the User model for the current website user.
     * 
     * @return User|null
     */
    private function getModel()
    {
        // If the user is NOT a guest
        //    AND
        // we do NOT yet have the User model...
        if (!$this->isGuest && $this->_model === null) {
            
            // Try to get the User model from the session.
            $user = \Yii::app()->user->user;
            
            // If NOT successful, try to get it from the database.
            if ( ! ($user instanceof User)) {
                $user = User::model()->findByPk($this->id);
                //$user = User::model()->findByPk($this->id, array(
                //    'select' => 'role' // Only retrieve the role field.
                //));
            } else {
                
                // If it was in the session, refresh the data (to avoid problems
                // of Yii caching related data longer than we want it to).
                $user->refresh();
            }
            
            // Keep a reference to whatever we ended up with.
            $this->_model = $user;
        }
        
        // Return whatever we have for the User model.
        return $this->_model;
    }
    
    /**
     * Override default method to simply check if user has role.
     *  *: any user, including both anonymous and authenticated users.
     *  ?: anonymous users.
     *  @: authenticated users.
     * 
     * See http://www.yiiframework.com/doc/api/1.1/CAccessControlFilter
     * 
     * @param string $role The role that the user must have to be allowed access.
     * @param array $params IGNORED
     * @param boolean $allowCaching IGNORED
     * @return boolean Whether the user meets the requirements and thus should
     *     be allowed access.
     */
    public function checkAccess($role, $params = array(), $allowCaching = true)
    {
        // If that is the user's role, or if the given role equals anyone, allow
        // them.
        if ($this->getRole() == $role || $role == '*') {
            return true;
        }
        
        // If the user is a guest and the role is anonymous users, allow them.
        if ($this->isGuest && $role == '?') {
            return true;
        }
        
        // If the user is logged in and the role is authenticated users, allow
        // them.
        if (( ! $this->isGuest) && $role == '@') {
            return true;
        }
        
        // Otherwise block them.
        return false;
    }
    
    public function hasFlashes()
    {
        $flashes = $this->getFlashes(false);
        if (is_array($flashes) && (count($flashes) > 0)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function hasOwnerPrivileges()
    {
        $userModel = $this->getModel();
        if ($userModel instanceof \User) {
            return $userModel->hasOwnerPrivileges();
        } else {
            return false;
        }
    }
}
