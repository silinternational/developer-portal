<?php

class m150203_200118_add_unique_endpoint_path_constraint extends CDbMigration
{
    public function safeUp()
    {
        $this->alterColumn(
            '{{api}}',
            'default_path',
            'varchar(255) DEFAULT NULL'
        );
        $this->createIndex(
            'uq_endpoint_default_path',
            '{{api}}',
            'endpoint,default_path',
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex(
            'uq_endpoint_default_path',
            '{{api}}'
        );
        $this->alterColumn(
            '{{api}}',
            'default_path',
            'varchar(1024) DEFAULT NULL'
        );
    }
}
