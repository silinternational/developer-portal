<?php

class m141217_150801_change_api_contacts_to_owner extends CDbMigration
{
    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
        // Create an owner_id column on the Api table (and set it up as a
        // foreign key).
        $this->addColumn('{{api}}', 'owner_id', 'int(11) DEFAULT NULL');
        $this->addForeignKey(
            'fk_api_user_owner_id',
            '{{api}}',
            'owner_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
        
        // Get the database connection.
        $db = $this->getDbConnection();
        
        // Get the list of all contact records.
        $contactRecords = $db->createCommand()
            ->select()
            ->from('{{contact}}')
            ->queryAll();
        
        // For each of those contact records...
        foreach ($contactRecords as $contactRecord) {
            
            // If it has an api_id and a user_id...
            $apiId = (int)$contactRecord['api_id'];
            $userId = (int)$contactRecord['user_id'];
            if (($apiId > 0) && ($userId > 0)) {
                
                // Set that API's new owner_id attribute to be that user_id.
                $this->execute(
                    'UPDATE {{api}} '
                    . 'SET `owner_id` = :owner_id '
                    . 'WHERE `api_id` = :api_id',
                    array(
                        ':owner_id' => $userId,
                        ':api_id' => $apiId,
                    )
                );
            }
        }
        
        // Delete the contact table (and references to it).
        $this->dropForeignKey('fk_contact_api_api_id', 'contact');
        $this->dropForeignKey('fk_contact_user_user_id', 'contact');
        $this->dropTable('{{contact}}');
    }

    public function safeDown()
    {
        // Create the contact table (and its indexes).
        $this->createTable('contact', array(
            'user_id' => 'int(11) NOT NULL',
            'api_id' => 'int(11) NOT NULL',
            'type' => 'varchar(32) NOT NULL',
            'contact_id' => 'pk',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->createIndex('idx_api_id', 'contact', 'api_id', FALSE);
        $this->createIndex('idx_user_id', 'contact', 'user_id', FALSE);
        $this->addForeignKey('fk_contact_api_api_id', 'contact', 'api_id', 'api', 'api_id', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('fk_contact_user_user_id', 'contact', 'user_id', 'user', 'user_id', 'NO ACTION', 'NO ACTION');
        
        // Get the database connection.
        $db = $this->getDbConnection();
        
        // Get the list of all Api records with an owner_id set.
        $apiWithOwnerRecords = $db->createCommand()
            ->select()
            ->from('{{api}}')
            ->where('`owner_id` IS NOT NULL')
            ->queryAll();
        
        // For each of those Api records...
        foreach ($apiWithOwnerRecords as $apiWithOwnerRecord) {
            
            // Attempt to set that API's owner as its technical and approval
            // contacts.
            $this->execute(
                'INSERT INTO {{contact}} (`api_id`, `user_id`, `type`) '
                . 'VALUES (:api_id, :user_id, :type)',
                array(
                    ':api_id' => $apiWithOwnerRecord['api_id'],
                    ':user_id' => $apiWithOwnerRecord['owner_id'],
                    ':type' => 'technical',
                )
            );
            $this->execute(
                'INSERT INTO {{contact}} (`api_id`, `user_id`, `type`) '
                . 'VALUES (:api_id, :user_id, :type)',
                array(
                    ':api_id' => $apiWithOwnerRecord['api_id'],
                    ':user_id' => $apiWithOwnerRecord['owner_id'],
                    ':type' => 'approval',
                )
            );
        }
        
        // Delete the api.owner_id field (and references to it).
        $this->dropForeignKey('fk_api_user_owner_id', '{{api}}');
        $this->dropColumn('{{api}}', 'owner_id');
    }
}