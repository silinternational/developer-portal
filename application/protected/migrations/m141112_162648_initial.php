<?php

class m141112_162648_initial extends CDbMigration
{
    public function up()
    {
        $this->createTable('api', array(
            'api_id' => 'pk',
            'code' => 'varchar(32) NOT NULL',
            'display_name' => 'varchar(64) NOT NULL',
            'endpoint' => 'varchar(255) NOT NULL',
            'queries_second' => 'int(11) NOT NULL',
            'queries_day' => 'int(11) NOT NULL',
            'access_type' => 'varchar(32) NOT NULL',
            'access_options' => 'varchar(255) DEFAULT NULL',
            'documentation' => 'text DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'updated' => 'datetime DEFAULT NULL',
            'approval_type' => 'varchar(16) NOT NULL',
            'protocol' => 'varchar(16) DEFAULT NULL',
            'strict_ssl' => 'tinyint(1) DEFAULT NULL',
            'endpoint_timeout' => 'int(4) NOT NULL',
            'default_path' => 'varchar(1024) DEFAULT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createTable('contact', array(
            'user_id' => 'int(11) NOT NULL',
            'api_id' => 'int(11) NOT NULL',
            'type' => 'varchar(32) NOT NULL',
            'contact_id' => 'pk',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx_api_id', 'contact', 'api_id', FALSE);

        $this->createIndex('idx_user_id', 'contact', 'user_id', FALSE);

        $this->createTable('faq', array(
            'faq_id' => 'pk',
            'question' => 'varchar(255) NOT NULL',
            'answer' => 'text NOT NULL',
            'order' => 'int(6) NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'updated' => 'datetime DEFAULT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createTable('key', array(
            'key_id' => 'pk',
            'value' => 'char(32) NOT NULL',
            'secret' => 'char(128) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'api_id' => 'int(11) NOT NULL',
            'queries_second' => 'int(11) NOT NULL',
            'queries_day' => 'int(11) NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'updated' => 'datetime DEFAULT NULL',
            'key_request_id' => 'int(11) DEFAULT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx_key_request_id', 'key', 'key_request_id', FALSE);

        $this->createIndex('idx_api_id', 'key', 'api_id', FALSE);

        $this->createIndex('idx_user_id', 'key', 'user_id', FALSE);

        $this->createTable('key_request', array(
            'key_request_id' => 'pk',
            'user_id' => 'int(11) NOT NULL',
            'api_id' => 'int(11) NOT NULL',
            'status' => 'varchar(32) NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'updated' => 'datetime DEFAULT NULL',
            'processed_by' => 'int(11) DEFAULT NULL',
            'purpose' => 'varchar(255) NOT NULL',
            'domain' => 'varchar(255) NOT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx_processed_by', 'key_request', 'processed_by', FALSE);

        $this->createIndex('idx_api_id', 'key_request', 'api_id', FALSE);

        $this->createIndex('idx_user_id', 'key_request', 'user_id', FALSE);

        $this->createTable('user', array(
            'user_id' => 'pk',
            'email' => 'varchar(128) NOT NULL',
            'first_name' => 'varchar(32) NOT NULL',
            'last_name' => 'varchar(32) NOT NULL',
            'display_name' => 'varchar(64) DEFAULT NULL',
            'status' => 'tinyint(1) NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'updated' => 'datetime DEFAULT NULL',
            'role' => 'varchar(16) NOT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('fk_contact_api_api_id', 'contact', 'api_id', 'api', 'api_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_contact_user_user_id', 'contact', 'user_id', 'user', 'user_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_key_key_request_key_request_id', 'key', 'key_request_id', 'key_request', 'key_request_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_key_api_api_id', 'key', 'api_id', 'api', 'api_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_key_user_user_id', 'key', 'user_id', 'user', 'user_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_key_request_user_processed_by', 'key_request', 'processed_by', 'user', 'user_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_key_request_api_api_id', 'key_request', 'api_id', 'api', 'api_id', 'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_key_request_user_user_id', 'key_request', 'user_id', 'user', 'user_id', 'NO ACTION', 'NO ACTION');

    }


    public function down()
    {
        $this->dropForeignKey('fk_contact_api_api_id', 'contact');

        $this->dropForeignKey('fk_contact_user_user_id', 'contact');

        $this->dropForeignKey('fk_key_key_request_key_request_id', 'key');

        $this->dropForeignKey('fk_key_api_api_id', 'key');

        $this->dropForeignKey('fk_key_user_user_id', 'key');

        $this->dropForeignKey('fk_key_request_user_processed_by', 'key_request');

        $this->dropForeignKey('fk_key_request_api_api_id', 'key_request');

        $this->dropForeignKey('fk_key_request_user_user_id', 'key_request');

        $this->dropTable('api');
        $this->dropTable('contact');
        $this->dropTable('faq');
        $this->dropTable('key');
        $this->dropTable('key_request');
        $this->dropTable('user');
    }
}