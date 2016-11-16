<?php

class m161101_180645_enable_custom_valid_sig_time_window extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{api}}', 'signature_window', 'tinyint DEFAULT 3 NOT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{api}}', 'signature_window');
    }
}
