<?php

class m160518_143329_update_user_table extends CDbMigration
{
    public function safeUp()
    {
        $this->alterColumn('{{user}}', 'email', 'string NOT NULL'); // string = varchar(255)
        $this->createIndex('uq_user_email', '{{user}}', 'email', true);
        
        $this->alterColumn('{{user}}', 'role', "enum('user','owner','admin') NOT NULL DEFAULT 'user'");
        
        /* NOTE: Allow auth_provider to be null to prevent any (possibly
         *       incorrect) default value from being supplied. We should always
         *       treat an auth_provider value of null as an invalid auth
         *       provider.  */
        $this->alterColumn('{{user}}', 'auth_provider', "enum('Insite','Google','GitHub','Bitbucket') DEFAULT NULL");
        $this->addColumn('{{user}}', 'customer_id', 'string NULL');
        $this->addColumn('{{user}}', 'verified_nonprofit', 'bool NOT NULL DEFAULT 0');
    }

    public function safeDown()
    {
        $this->dropColumn('{{user}}', 'verified_nonprofit');
        $this->dropColumn('{{user}}', 'customer_id');
        $this->alterColumn('{{user}}', 'auth_provider', 'varchar(32) NOT NULL');
        $this->alterColumn('{{user}}', 'role', 'varchar(16) NOT NULL');
        
        $this->dropIndex('uq_user_email', '{{user}}');
        $this->alterColumn('{{user}}', 'email', 'varchar(128) NOT NULL');
    }
}
