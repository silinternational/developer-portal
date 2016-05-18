<?php

class m160603_141708_further_update_key_table extends CDbMigration
{
    public function safeUp()
    {
        $this->alterColumn('{{key}}', 'created', 'datetime NOT NULL');
        $this->alterColumn('{{key}}', 'updated', 'datetime NOT NULL');
        $this->dropColumn('{{key}}', 'key_request_id');
    }

    public function safeDown()
    {
        echo "m160603_141708_further_update_key_table does not support migration down.\n";
        return false;
    }
}
