<?php

class m160815_171806_enable_secretless_keys extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{api}}', 'require_signature', "enum('yes', 'no') DEFAULT 'yes'");
    }

    public function safeDown()
    {
        $this->dropColumn('{{api}}', 'require_signature');
    }
}
