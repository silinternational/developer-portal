<?php

class m160919_175902_add_test_login_auth_provider extends CDbMigration
{
    public function safeUp()
    {
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            "enum('Insite','Google','GitHub','Bitbucket','TEST') DEFAULT NULL"
        );
    }

    public function safeDown()
    {
        $this->alterColumn(
            '{{user}}',
            'auth_provider',
            "enum('Insite','Google','GitHub','Bitbucket') DEFAULT NULL"
        );
    }
}
