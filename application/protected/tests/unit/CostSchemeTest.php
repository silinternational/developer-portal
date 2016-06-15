<?php
namespace Sil\DevPortal\tests\unit;

class CostSchemeTest extends \CDbTestCase
{
    public $fixtures = array(
        'costSchemes' => 'CostScheme',
    );
    
    public function testFixtureDataValidity()
    {
        foreach ($this->costSchemes as $fixtureName => $fixtureData) {
            /* @var $costScheme \CostScheme */
            $costScheme = $this->costSchemes($fixtureName);
            $this->assertTrue($costScheme->delete(), sprintf(
                'Could not delete cost scheme fixture %s: %s',
                $fixtureName,
                print_r($costScheme->getErrors(), true)
            ));
            $costSchemeOnInsert = new \CostScheme();
            $costSchemeOnInsert->setAttributes($fixtureData, false);
            $this->assertTrue($costSchemeOnInsert->save(), sprintf(
                'CostScheme fixture "%s" (ID %s) does not have valid data: %s',
                $fixtureName,
                $costSchemeOnInsert->cost_scheme_id,
                var_export($costSchemeOnInsert->getErrors(), true)
            ));
        }
    }
    
    public function testHasAtLeastOnePrice()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
    
    public function testHasBothOrNeither()
    {
        $this->markTestIncomplete('Test not yet written.');
    }
}
