<?php

class m160617_142730_split_event_user_field extends CDbMigration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk_event_user_user_id', '{{event}}');
        $this->renameColumn('{{event}}', 'user_id', 'acting_user_id');
        $this->addForeignKey(
            'fk_event_user_acting_user_id',
            '{{event}}',
            'acting_user_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
        
        $this->addColumn('{{event}}', 'affected_user_id', 'int(11) NULL');
        $this->addForeignKey(
            'fk_event_user_affected_user_id',
            '{{event}}',
            'affected_user_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_event_user_affected_user_id', '{{event}}');
        $this->dropColumn('{{event}}', 'affected_user_id');
        
        $this->dropForeignKey('fk_event_user_acting_user_id', '{{event}}');
        $this->renameColumn('{{event}}', 'acting_user_id', 'user_id');
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
}
