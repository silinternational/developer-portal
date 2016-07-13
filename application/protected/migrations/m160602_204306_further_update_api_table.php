<?php

class m160602_204306_further_update_api_table extends CDbMigration
{
    public function safeUp()
    {
        $this->dropColumn('{{api}}', 'access_type');
        $this->dropColumn('{{api}}', 'access_options');
        $this->alterColumn('{{api}}', 'strict_ssl', 'bool NOT NULL DEFAULT 1');
        $this->alterColumn('{{api}}', 'created', 'datetime NOT NULL');
        $this->alterColumn('{{api}}', 'updated', 'datetime NOT NULL');
        $this->addColumn('{{api}}', 'logo_url', 'varchar(255) DEFAULT NULL');
    }

    public function safeDown()
    {
        echo "m160602_204306_further_update_api_table does not support migration down.\n";
        return false;
    }
}
