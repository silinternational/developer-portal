<?php

class m161115_223435_switch_to_requiring_user_display_name extends CDbMigration
{
    public function safeUp()
    {
        $db = $this->getDbConnection();
        $userRecords = $db->createCommand()->select()->from('{{user}}')->queryAll();
        
        foreach ($userRecords as $userRecord) {
            if ( ! empty($userRecord['display_name'])) {
                continue;
            }

            $displayName = trim(sprintf(
                '%s %s',
                $userRecord['first_name'],
                $userRecord['last_name']
            ));
            $this->execute(
                'UPDATE {{user}} '
                . 'SET `display_name` = :display_name '
                . 'WHERE `user_id` = :user_id',
                array(
                    ':display_name' => ($displayName ?: $userRecord['email']),
                    ':user_id' => $userRecord['user_id'],
                )
            );
        }
        
        $this->alterColumn('{{user}}', 'first_name', 'varchar(32) DEFAULT NULL');
        $this->alterColumn('{{user}}', 'last_name', 'varchar(32) DEFAULT NULL');
        $this->alterColumn('{{user}}', 'display_name', 'varchar(64) NOT NULL');
    }

    public function safeDown()
    {
        $this->alterColumn('{{user}}', 'display_name', 'varchar(64) DEFAULT NULL');
        $this->alterColumn('{{user}}', 'last_name', 'varchar(32) NOT NULL');
        $this->alterColumn('{{user}}', 'first_name', 'varchar(32) NOT NULL');
    }
}
