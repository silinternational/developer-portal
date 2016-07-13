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
    
    public function testHasAtLeastOnePrice_no_allNull()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $costScheme->yearly_commercial_price = null;
        $attribute = 'yearly_commercial_price';
        $params = array(
            'priceFields' => array(
                'yearly_commercial_price',
                'yearly_nonprofit_price',
                'monthly_commercial_price',
                'monthly_nonprofit_price',
            ),
        );
        
        // Act:
        $costScheme->hasAtLeastOnePrice($attribute, $params);
        
        // Assert:
        $this->assertNotEmpty(
            $costScheme->getErrors(),
            'Failed to report error when all of the price fields are null.'
        );
    }
    
    public function testHasAtLeastOnePrice_no_mostNullOneNegative()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $costScheme->yearly_commercial_price = -1;
        $attribute = 'yearly_commercial_price';
        $params = array(
            'priceFields' => array(
                'yearly_commercial_price',
                'yearly_nonprofit_price',
                'monthly_commercial_price',
                'monthly_nonprofit_price',
            ),
        );

        // Act:
        $costScheme->hasAtLeastOnePrice($attribute, $params);
        
        // Assert:
        $this->assertNotEmpty(
            $costScheme->getErrors(),
            'Failed to report error when one price field is negative and the rest are null.'
        );
    }
    
    public function testHasAtLeastOnePrice_no_mostNullOneNonNumeric()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $costScheme->yearly_commercial_price = '123abc'; // Alphanumeric, start with digits.
        $attribute = 'yearly_commercial_price';
        $params = array(
            'priceFields' => array(
                'yearly_commercial_price',
                'yearly_nonprofit_price',
                'monthly_commercial_price',
                'monthly_nonprofit_price',
            ),
        );

        // Act:
        $costScheme->hasAtLeastOnePrice($attribute, $params);
        
        // Assert:
        $this->assertNotEmpty(
            $costScheme->getErrors(),
            'Failed to report error when one price field has a non-numeric value and the rest are null.'
        );
    }
    
    public function testHasAtLeastOnePrice_no_mostNullOneZero()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $costScheme->yearly_commercial_price = 0;
        $attribute = 'yearly_commercial_price';
        $params = array(
            'priceFields' => array(
                'yearly_commercial_price',
                'yearly_nonprofit_price',
                'monthly_commercial_price',
                'monthly_nonprofit_price',
            ),
        );

        // Act:
        $costScheme->hasAtLeastOnePrice($attribute, $params);
        
        // Assert:
        $this->assertNotEmpty(
            $costScheme->getErrors(),
            'Failed to report error when one price field is zero and the rest are null.'
        );
    }
    
    public function testHasAtLeastOnePrice_yes()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $attribute = 'yearly_commercial_price';
        $params = array(
            'priceFields' => array(
                'yearly_commercial_price',
                'yearly_nonprofit_price',
                'monthly_commercial_price',
                'monthly_nonprofit_price',
            ),
        );

        // Act:
        $costScheme->hasAtLeastOnePrice($attribute, $params);
        
        // Assert:
        $this->assertEmpty($costScheme->getErrors(), sprintf(
            'Unexpectedly reported errors when at least one price field has a numeric value: %s',
            print_r($costScheme->getErrors(), true)
        ));
    }
    
    public function testHasBothOrNeither_has1st()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $costScheme->yearly_commercial_plan_code = null;
        $attribute = 'yearly_commercial_price';
        $params = array(
            'otherAttribute' => 'yearly_commercial_plan_code',
        );
        
        // Act:
        $costScheme->hasBothOrNeither($attribute, $params);
        
        // Assert:
        $this->assertNotEmpty(
            $costScheme->getErrors(),
            'Failed to report error when only the first attribute has a non-null value.'
        );
    }
    
    public function testHasBothOrNeither_has2nd()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $costScheme->yearly_commercial_price = null;
        $attribute = 'yearly_commercial_price';
        $params = array(
            'otherAttribute' => 'yearly_commercial_plan_code',
        );
        
        // Act:
        $costScheme->hasBothOrNeither($attribute, $params);
        
        // Assert:
        $this->assertNotEmpty(
            $costScheme->getErrors(),
            'Failed to report error when only the second attribute has a non-null value.'
        );
    }
    
    public function testHasBothOrNeither_hasBoth()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasYearlyCommPriceAndPlanCode');
        $attribute = 'yearly_commercial_price';
        $params = array(
            'otherAttribute' => 'yearly_commercial_plan_code',
        );
        
        // Act:
        $costScheme->hasBothOrNeither($attribute, $params);
        
        // Assert:
        $this->assertEmpty($costScheme->getErrors(), sprintf(
            'Unexpectedly reported errors when both attributes have a non-null value: %s',
            print_r($costScheme->getErrors(), true)
        ));
    }
    
    public function testHasBothOrNeither_hasNeither()
    {
        // Arrange:
        /* @var $costScheme \CostScheme */
        $costScheme = $this->costSchemes('hasPriceAndPlanCodeForMonthlyCommNotYearlyComm');
        $attribute = 'yearly_commercial_price';
        $params = array(
            'otherAttribute' => 'yearly_commercial_plan_code',
        );
        
        // Act:
        $costScheme->hasBothOrNeither($attribute, $params);
        
        // Assert:
        $this->assertEmpty($costScheme->getErrors(), sprintf(
            'Unexpectedly reported errors when neither attribute has a non-null value: %s',
            print_r($costScheme->getErrors(), true)
        ));
    }
}
