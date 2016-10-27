<?php

class m161027_195031_enable_additional_api_headers extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{api}}', 'additional_headers', 'string null');
    }

    public function safeDown()
    {
        $this->dropColumn('{{api}}', 'additional_headers');
    }
}
