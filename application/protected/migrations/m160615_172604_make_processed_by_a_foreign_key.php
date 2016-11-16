<?php

class m160615_172604_make_processed_by_a_foreign_key extends CDbMigration
{
    public function safeUp()
    {
        $this->addForeignKey(
            'fk_key_user_processed_by',
            '{{key}}',
            'processed_by',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_key_user_processed_by', '{{key}}');
    }
}
