<?php

class UtilsTest extends CDbTestCase
{

    public function testgetFriendlyDate() 
    {
        $timeStr = '2013-01-25 01:02:03 ' . date_default_timezone_get();
        $returnValue = Utils::getFriendlyDate($timeStr);
        $expected = '2013';
        
        $results = strpos($returnValue, $expected);
        
        $this->assertTrue(($results === 0) || $results > 0, $expected . 
              ' not found in ' . $returnValue . ' ... This could' .
              ' be because of Windows not accepting certain format markers.');
    }

    public function testGetNestedArrayValue_deeplyNestedKeyExists()
    {
        // Arrange:
        $data = array(
            'one' => array(
                'two' => array(
                    'three' => 3,
                ),
            ),
        );
        $keys = array('one', 'two', 'three');
        $expected = 3;
        
        // Act:
        $actual = \Utils::getNestedArrayValue($data, $keys);
        
        // Assert:
        $this->assertSame(
            $expected,
            $actual,
            'Failed to return the correct value for a given set of data and '
            . 'list of keys specifying a path into that data.'
        );
    }
    
    public function testGetNestedArrayValue_desiredValueIsAnArray()
    {
        // Arrange:
        $data = array(
            'one' => array(
                'two' => array(
                    'three' => 3,
                ),
            ),
        );
        $keys = array('one', 'two');
        $expected = array(
            'three' => 3,
        );
        
        // Act:
        $actual = \Utils::getNestedArrayValue($data, $keys);
        
        // Assert:
        $this->assertSame(
            $expected,
            $actual,
            'Failed to return the correct value when the desired piece of data '
            . 'was itself an array.'
        );
    }
    
    public function testGetNestedArrayValue_defaultValue()
    {
        // Arrange:
        $data = array(
            'realKey' => 1,
        );
        $keys = array('nonExistentKey');
        $default = 7;
        $expected = $default;
        
        // Act:
        $actual = \Utils::getNestedArrayValue($data, $keys, $default);
        
        // Assert:
        $this->assertSame(
            $expected,
            $actual,
            'Failed to return the given default value when the specified key '
            . 'did not exist.'
        );
    }
    
    public function testGetNestedArrayValue_noDefaultValue()
    {
        // Arrange:
        $data = array(
            'realKey' => 1,
        );
        $keys = array('nonExistentKey');
        
        // Act:
        $result = \Utils::getNestedArrayValue($data, $keys);
        
        // Assert:
        $this->assertNull(
            $result,
            'Failed to return null when the specified key did not exist and no '
            . 'default value was provided.'
        );
    }
    
    public function testgetShortDate() 
    {
        $timeStr = '2013-01-25 01:02:03 ' . date_default_timezone_get();
        $returnValue = Utils::getShortDate($timeStr);
        $expected = '25';
        
        $results = strpos($returnValue, $expected);
        
        $this->assertTrue(($results === 0) || $results > 0, $expected . 
                      ' not found in ' . $returnValue);
    }

    public function testfindPkOr404() 
    {
        $model = '\Sil\DevPortal\models\Api';
        $pk = 2;
        $expected = 'www.owner.com';
        
        $returnObject = Utils::findPkOr404($model, $pk);
        $results = $returnObject->endpoint;

        $this->assertEquals($expected, $results);
    }


    public function testfindPkOr404_Fail() 
    {
        $model = '\Sil\DevPortal\models\Api';
        $pk = 999;
        $expected = 'invalid request';
        
        try {
            $results = Utils::findPkOr404($model, $pk);
        }
        catch (\Exception $ex) {
            $results = $ex->getMessage();
        }
        
        $this->assertEquals($expected, $results);
    }
}