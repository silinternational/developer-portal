<?php

class KeyRequest extends KeyRequestBase
{
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // Get the relations defined by the base (aka. parent) class.
        $baseClassRelations = parent::relations();
        
        // Remove the 'keys' relation (because there will/should never be
        // multiple keys).
        unset($baseClassRelations['keys']);
        
        // Add a 'key' relation (because there will/should only be one).
        $baseClassRelations['key'] = array(
            self::HAS_ONE, 'Key', 'key_request_id'
        );
        
        // Return the resulting relations definitions.
        return $baseClassRelations;
    }

    public function rules() {
        $rules = parent::rules();
        $newRules = array_merge($rules, array(
            array('updated', 'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false, 'on' => 'update'),
            array('created,updated', 'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false, 'on' => 'insert'),
            array('status', 'in',
                'range' => self::getValidStatusValues(),
                'message' => 'That is not a valid status value.'),
        ));
        
        return $newRules;
    }
    
    public function afterSave()
    {
        // Run the parent class's version of this method.
        parent::afterSave();
        
        if ($this->status === self::STATUS_PENDING) {
            
            // If this KeyRequest is now pending approval, tell the Owner of the
            // API (if known/possible).
            $this->notifyApiOwnerOfPendingRequest();
            
        } elseif ($this->status === self::STATUS_DENIED) {
            
            // OR, if this KeyRequest was denied, tell the user that was
            // requesting the key.
            $this->notifyUserOfDeniedKey();
            
        }
    }
}
