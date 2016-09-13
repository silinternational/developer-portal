<?php

class m160907_201014_add_site_text_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{site_text}}', array(
            'site_text_id' => 'pk',
            'name' => 'varchar(255) NOT NULL',
            'markdown_content' => 'text NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci');
        
        $this->createIndex('uq_site_text_name', '{{site_text}}', 'name', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{site_text}}');
    }
}
