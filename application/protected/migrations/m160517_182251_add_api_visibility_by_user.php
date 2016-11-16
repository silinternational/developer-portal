<?php

class m160517_182251_add_api_visibility_by_user extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{api_visibility_user}}', array(
            'api_visibility_user_id' => 'pk',
            'api_id' => 'int(11) NOT NULL',
            'invited_user_id' => 'int(11) NULL',
            'invited_user_email' => 'string NULL',
            'invitation_code' => 'char(32) NULL',
            'invited_by_user_id' => 'int(11) NOT NULL',
            'created' => 'datetime NOT NULL',
            'updated' => 'datetime NOT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'fk_api_visibility_user_api_api_id',
            '{{api_visibility_user}}',
            'api_id',
            '{{api}}',
            'api_id',
            'NO ACTION',
            'NO ACTION'
        );
        $this->addForeignKey(
            'fk_api_visibility_user_user_invited_user_id',
            '{{api_visibility_user}}',
            'invited_user_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
        $this->addForeignKey(
            'fk_api_visibility_user_user_invited_by_user_id',
            '{{api_visibility_user}}',
            'invited_by_user_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
        
        // Grant each user who has a Key to an Api permission to see that Api.
        $db = $this->getDbConnection();
        $keys = $db->createCommand()
            ->select()
            ->from('{{key}}')
            ->queryAll();
        foreach ($keys as $key) {
            $apiId = (int)$key['api_id'];
            $userId = (int)$key['user_id'];
            
            // Make sure we have an api_id and a user_id.
            if (($apiId > 0) && ($userId > 0)) {
                $this->insert('{{api_visibility_user}}', array(
                    'api_id' => $apiId,
                    'invited_user_id' => $userId,
                    
                    // Use the User's own user_id. We know we have that value,
                    // and that will let us easily distinguish these records
                    // later from those where someone else genuinely invited
                    // them to see an Api.
                    'invited_by_user_id' => $userId,
                    
                    // Fall back to the current time, in case we have incomplete
                    // data (such as from incomplete test fixtures).
                    'created' => $key['created'] ?: time(),
                    'updated' => $key['created'] ?: time(), // Intentionally using 'created'.
                ));
            }
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_api_visibility_user_user_invited_by_user_id',
            '{{api_visibility_user}}'
        );
        $this->dropForeignKey(
            'fk_api_visibility_user_user_invited_user_id',
            '{{api_visibility_user}}'
        );
        $this->dropForeignKey(
            'fk_api_visibility_user_api_api_id',
            '{{api_visibility_user}}'
        );
        $this->dropTable('{{api_visibility_user}}');
    }
}
