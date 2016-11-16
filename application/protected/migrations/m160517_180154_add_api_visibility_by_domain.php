<?php

class m160517_180154_add_api_visibility_by_domain extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{api_visibility_domain}}', array(
            'api_visibility_domain_id' => 'pk',
            'api_id' => 'int(11) NOT NULL',
            'domain' => 'varchar(255) NOT NULL',
            'invited_by_user_id' => 'int(11) NOT NULL',
            'created' => 'datetime NOT NULL',
            'updated' => 'datetime NOT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'fk_api_visibility_domain_api_api_id',
            '{{api_visibility_domain}}',
            'api_id',
            '{{api}}',
            'api_id',
            'NO ACTION',
            'NO ACTION'
        );
        $this->addForeignKey(
            'fk_api_visibility_domain_user_invited_by_user_id',
            '{{api_visibility_domain}}',
            'invited_by_user_id',
            '{{user}}',
            'user_id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_api_visibility_domain_user_invited_by_user_id',
            '{{api_visibility_domain}}'
        );
        $this->dropForeignKey(
            'fk_api_visibility_domain_api_api_id',
            '{{api_visibility_domain}}'
        );
        $this->dropTable('{{api_visibility_domain}}');
    }
}
