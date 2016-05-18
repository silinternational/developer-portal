<?php

class m160518_153324_add_event_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{event}}', array(
            'event_id' => 'pk',
            'api_id' => 'int(11) NULL',
            'key_id' => 'int(11) NULL',
            'user_id' => 'int(11) NULL',
            'description' => 'string NOT NULL',
            'created' => 'datetime NOT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'fk_event_api_api_id',
            '{{event}}',
            'api_id',
            '{{api}}',
            'api_id',
            'NO ACTION',
            'NO ACTION'
        );
        $this->addForeignKey(
            'fk_event_key_key_id',
            '{{event}}',
            'key_id',
            '{{key}}',
            'key_id',
            'NO ACTION',
            'NO ACTION'
        );
        $this->addForeignKey(
            'fk_event_user_user_id',
            '{{event}}',
            'user_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_event_user_user_id', '{{event}}');
        $this->dropForeignKey('fk_event_key_key_id', '{{event}}');
        $this->dropForeignKey('fk_event_api_api_id', '{{event}}');
        $this->dropTable('{{event}}');
    }
}
