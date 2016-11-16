<?php

class m160517_192303_merge_key_and_key_request_tables extends CDbMigration
{
    public function safeUp()
    {
        $db = $this->getDbConnection();
        
        // Add the fields whose data will come from the related KeyRequests,
        // populate them for existing Key records, then set any that shouldn't
        // be nullable to NOT NULL.
        $this->addColumn(
            '{{key}}',
            'status',
            "enum('pending','approved','denied','revoked') NOT NULL DEFAULT 'pending'"
        );
        $this->addColumn('{{key}}', 'requested_on', 'datetime NULL');
        $this->addColumn('{{key}}', 'processed_on', 'datetime NULL');
        $this->addColumn('{{key}}', 'processed_by', 'int(11) DEFAULT NULL');
        $this->addColumn('{{key}}', 'purpose', 'string NULL');
        $this->addColumn('{{key}}', 'domain', 'string NULL');
        $keys = $db->createCommand()
            ->select()
            ->from('{{key}}')
            ->queryAll();
        foreach ($keys as $key) {
            
            // Get this Key's related KeyRequest (if applicable).
            $keyRequest = $db->createCommand()
                ->select()
                ->from('{{key_request}}')
                ->where('key_request_id = :key_request_id')
                ->queryRow(true, array(
                    ':key_request_id' => (int)$key['key_request_id']
                ));
            
            if ($keyRequest !== false) {
                // Use that KeyRequest's created date as the Key's new
                // requested_on field.
                $this->update('{{key}}', array(
                    'status' => $keyRequest['status'],
                    
                    // Fall back to the current time, in case we have incomplete
                    // data (such as from incomplete test fixtures).
                    'requested_on' => $keyRequest['created'] ?: time(),
                    'processed_on' => $key['created'],
                    'processed_by' => (int)$keyRequest['processed_by'],
                    'purpose' => $keyRequest['purpose'],
                    'domain' => $keyRequest['domain'],
                ), 'key_id = :key_id', array(
                    ':key_id' => (int)$key['key_id']
                ));
            } else {
                // If no KeyRequest was found, provide some fallback value for
                // the non-nullable fields. Assume the key's status is
                // "approved" since the key already exists.
                $this->update('{{key}}', array(
                    'status' => 'approved',
                    
                    // Fall back to the current time, in case we have incomplete
                    // data (such as from incomplete test fixtures).
                    'requested_on' => $key['created'] ?: time(),
                    'purpose' => '',
                    'domain' => '',
                ), 'key_id = :key_id', array(
                    ':key_id' => (int)$key['key_id']
                ));
            }
        }
        $this->alterColumn('{{key}}', 'requested_on', 'datetime NOT NULL');
        $this->alterColumn('{{key}}', 'purpose', 'string NOT NULL');
        $this->alterColumn('{{key}}', 'domain', 'string NOT NULL');
        
        // Value and secret are now nullable (for while a key is pending, for
        // example).
        $this->alterColumn('{{key}}', 'value', 'char(32) NULL DEFAULT NULL');
        $this->alterColumn('{{key}}', 'secret', 'char(128) NULL DEFAULT NULL');
        
        // Add any remaining fields needed.
        $this->addColumn('{{key}}', 'accepted_terms_on', 'datetime NULL');
        $this->addColumn('{{key}}', 'subscription_id', 'string NULL');
        
        $this->createIndex('uq_key_value_api_id', '{{key}}', 'value,api_id', true);
        
        // Add any existing key requests that have not yet been approved.
        $otherKeyRequests = $db->createCommand()
            ->select()
            ->from('{{key_request}}')
            ->where('status != "approved"')
            ->queryAll();
        foreach ($otherKeyRequests as $otherKeyRequest) {
            $api = $db->createCommand()
                ->select()
                ->from('{{api}}')
                ->where('api_id = :api_id')
                ->queryRow(true, array(
                    ':api_id' => (int)$otherKeyRequest['api_id']
                ));
            if ($api === false) {
                continue;
            }
            $this->insert('{{key}}', array(
                'value' => null,
                'secret' => null,
                'user_id' => (int)$otherKeyRequest['user_id'],
                'api_id' => (int)$otherKeyRequest['api_id'],
                'queries_second' => (int)$api['queries_second'],
                'queries_day' => (int)$api['queries_day'],
                'status' => $otherKeyRequest['status'],
                
                // Fall back to the current time, in case we have incomplete
                // data (such as from incomplete test fixtures).
                'created' => $otherKeyRequest['created'] ?: time(),
                'updated' => $otherKeyRequest['updated'] ?: time(),
                'requested_on' => $otherKeyRequest['created'] ?: time(),
                'purpose' => $otherKeyRequest['purpose'],
                'domain' => $otherKeyRequest['domain'],
            ));
        }
    }

    public function safeDown()
    {
        $this->delete('{{key}}', 'status != "approved"');
        
        $this->dropIndex('uq_key_value_api_id', '{{key}}');
        $this->dropColumn('{{key}}', 'subscription_id');
        $this->dropColumn('{{key}}', 'accepted_terms_on');
        $this->alterColumn('{{key}}', 'secret', 'char(128) NOT NULL');
        $this->alterColumn('{{key}}', 'value', 'char(32) NOT NULL');
        $this->dropColumn('{{key}}', 'domain');
        $this->dropColumn('{{key}}', 'purpose');
        $this->dropColumn('{{key}}', 'processed_by');
        $this->dropColumn('{{key}}', 'processed_on');
        $this->dropColumn('{{key}}', 'requested_on');
        $this->dropColumn('{{key}}', 'status');
    }
}
