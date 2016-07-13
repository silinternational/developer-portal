<?php

class m160511_201702_expand_api_model extends CDbMigration
{
    public function safeUp()
    {
        // Add columns.
        // NOTE: MySQL enum fields that are not nullable use the first entry in
        //       the enum(...) list as the default value. Specifying a default
        //       value simply improves readability.
        $this->addColumn('{{api}}', 'visibility', "enum('invitation','public') NOT NULL DEFAULT 'invitation'");
        $this->addColumn('{{api}}', 'customer_support', 'string NULL');
        $this->addColumn('{{api}}', 'terms', 'text NULL');
        
        // Alter columns.
        $this->alterColumn('{{api}}', 'approval_type', "enum('owner','auto') NOT NULL DEFAULT 'owner'");
        $this->alterColumn('{{api}}', 'protocol', "enum('https','http') NOT NULL DEFAULT 'https'");
        $this->renameColumn('{{api}}', 'support', 'technical_support');
        
        $this->createIndex('uq_api_code', '{{api}}', 'code', true);
        $this->createIndex('uq_api_display_name', '{{api}}', 'display_name', true);
    }

    public function safeDown()
    {
        $this->dropIndex('uq_api_code', '{{api}}');
        $this->dropIndex('uq_api_display_name', '{{api}}');
        $this->renameColumn('{{api}}', 'technical_support', 'support');
        $this->alterColumn('{{api}}', 'protocol', 'varchar(16) DEFAULT NULL');
        $this->alterColumn('{{api}}', 'approval_type', 'varchar(16) NOT NULL');
        $this->dropColumn('{{api}}', 'terms');
        $this->dropColumn('{{api}}', 'customer_support');
        $this->dropColumn('{{api}}', 'visibility');
    }
}
