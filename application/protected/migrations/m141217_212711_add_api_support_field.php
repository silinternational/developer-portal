<?php

class m141217_212711_add_api_support_field extends CDbMigration
{
    public function up()
    {
        $this->addColumn('{{api}}', 'support', 'string null');
    }

    public function down()
    {
        $this->dropColumn('{{api}}', 'support');
    }
}
