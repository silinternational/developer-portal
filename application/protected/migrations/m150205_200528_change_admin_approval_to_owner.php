<?php

class m150205_200528_change_admin_approval_to_owner extends CDbMigration
{
    public function safeUp()
    {
        // Change any admin-approved Apis to be owner-approved.
        $this->execute(
            'UPDATE {{api}} '
            . 'SET `approval_type` = :approval_type_owner '
            . 'WHERE `approval_type` = :approval_type_admin',
            array(
                ':approval_type_owner' => 'owner',
                ':approval_type_admin' => 'admin',
            )
        );
    }

    public function safeDown()
    {
        echo "m150205_200528_change_admin_approval_to_owner does not support migration down.\n";
        return false;
    }
}
