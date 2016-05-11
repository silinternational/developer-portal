<?php

class m141218_200920_add_api_brief_description extends CDbMigration
{
    public function up()
    {
        $this->addColumn(
            '{{api}}',
            'brief_description',
            'string NULL AFTER display_name'
        );
    }

    public function down()
    {
        $this->dropColumn('{{api}}', 'brief_description');
    }
}
