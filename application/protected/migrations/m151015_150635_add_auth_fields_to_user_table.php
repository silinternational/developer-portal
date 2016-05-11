<?php

class m151015_150635_add_auth_fields_to_user_table extends CDbMigration
{
    public function safeUp()
    {
        // Add a column for authentication provider. Set all existing rows to
        // have a value of 'Insite' (so existing users will still be able to
        // log in), but then remove that default so that future rows have no
        // default (to make sure we provide a value when creating new Users).
        $this->addColumn(
            '{{user}}',
            'auth_provider',
            'varchar(32) NOT NULL DEFAULT "Insite"'
        );
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            'varchar(32) NOT NULL'
        );
        
        // Also add any other necessary columns/indexes.
        $this->addColumn(
            '{{user}}',
            'auth_provider_user_identifier',
            'varchar(255) NULL'
        );
        $this->createIndex(
            'idx_user_auth_provider',
            '{{user}}',
            'auth_provider'
        );
    }

    public function safeDown()
    {
        $this->dropIndex('idx_user_auth_provider', '{{user}}');
        
        $this->dropColumn('{{user}}', 'auth_provider_user_identifier');
        $this->dropColumn('{{user}}', 'auth_provider');
    }
}
