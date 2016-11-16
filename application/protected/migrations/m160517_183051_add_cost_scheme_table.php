<?php

class m160517_183051_add_cost_scheme_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{cost_scheme}}', array(
            'cost_scheme_id' => 'pk',
            'yearly_commercial_price' => 'decimal(19, 4) NULL',
            'yearly_commercial_plan_code' => 'string NULL',
            'yearly_nonprofit_price' => 'decimal(19, 4) NULL',
            'yearly_nonprofit_plan_code' => 'string NULL',
            'monthly_commercial_price' => 'decimal(19, 4) NULL',
            'monthly_commercial_plan_code' => 'string NULL',
            'monthly_nonprofit_price' => 'decimal(19, 4) NULL',
            'monthly_nonprofit_plan_code' => 'string NULL',
            'currency' => 'char(3) NOT NULL DEFAULT "USD"',
            'accounting_info' => 'string NULL',
            'created' => 'datetime NOT NULL',
            'updated' => 'datetime NOT NULL',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addColumn('{{api}}', 'cost_scheme_id', 'int(11) NULL');
        $this->addForeignKey(
            'fk_api_cost_scheme_cost_scheme_id',
            '{{api}}',
            'cost_scheme_id',
            '{{cost_scheme}}',
            'cost_scheme_id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_api_cost_scheme_cost_scheme_id', '{{api}}');
        $this->dropColumn('{{api}}', 'cost_scheme_id');
        $this->dropTable('{{cost_scheme}}');
    }
}
