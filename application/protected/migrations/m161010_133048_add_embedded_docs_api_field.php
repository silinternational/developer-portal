<?php

class m161010_133048_add_embedded_docs_api_field extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{api}}', 'embedded_docs_url', 'string null');
    }

    public function safeDown()
    {
        $this->dropColumn('{{api}}', 'embedded_docs_url');
    }
}
