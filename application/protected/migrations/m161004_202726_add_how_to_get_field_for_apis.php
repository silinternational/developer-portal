<?php

class m161004_202726_add_how_to_get_field_for_apis extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{api}}', 'how_to_get', 'text null');
    }

    public function safeDown()
    {
        $this->dropColumn('{{api}}', 'how_to_get');
    }
}
