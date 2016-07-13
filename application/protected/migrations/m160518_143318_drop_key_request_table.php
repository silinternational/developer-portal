<?php

class m160518_143318_drop_key_request_table extends CDbMigration
{
    public function up()
    {
        $this->dropForeignKey('fk_key_key_request_key_request_id', '{{key}}');
        $this->dropIndex('idx_key_request_id', '{{key}}');
        $this->dropTable('{{key_request}}');
    }

    public function down()
    {
        echo "m160518_143318_drop_key_request_table does not support migration down.\n";
        return false;
    }

    /*
    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
