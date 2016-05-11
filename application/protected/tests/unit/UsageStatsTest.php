<?php

class UsageStatsTest extends DeveloperPortalTestCase
{
    public function testCombineUsageCategoryArrays_noCollisions()
    {
        // Arrange:
        $one = array(
            1300000000 => array(
                200 => 5,
                400 => 1,
            ),
        );
        $two = array(
            1400000000 => array(
                200 => 3,
                400 => 2,
            ),
        );
        $expected = array(
            1300000000 => array(
                200 => 5,
                400 => 1,
            ),
            1400000000 => array(
                200 => 3,
                400 => 2,
            ),
        );
        
        // Act:
        $actual = UsageStats::combineUsageCategoryArrays($one, $two);
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Incorrectly combined arrays that had no keys in common.'
        );
    }
    
    public function testCombineUsageCategoryArrays_collidingTimestamps()
    {
        // Arrange:
        $one = array(
            1300000000 => array(
                200 => 5,
            ),
        );
        $two = array(
            1300000000 => array(
                400 => 2,
            ),
        );
        $expected = array(
            1300000000 => array(
                200 => 5,
                400 => 2,
            ),
        );
        
        // Act:
        $actual = UsageStats::combineUsageCategoryArrays($one, $two);
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Incorrectly combined arrays that had timestamp key(s) in common.'
        );
    }
    
    public function testCombineUsageCategoryArrays_collidingResponseCodes()
    {
        // Arrange:
        $one = array(
            1300000000 => array(
                200 => 5,
                400 => 1,
            ),
        );
        $two = array(
            1300000000 => array(
                400 => 2,
            ),
        );
        $expected = array(
            1300000000 => array(
                200 => 5,
                400 => 3,
            ),
        );
        
        // Act:
        $actual = UsageStats::combineUsageCategoryArrays($one, $two);
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            'Incorrectly combined arrays that had response code key(s) in '
            . 'common.'
        );
    }
    
    public function testConfirmIntervalsDiffer()
    {
        // Make sure the granularity constants differ (both in their values and
        // in their user-friendly versions).
        $this->confirmConstantsDiffer('UsageStats', 'INTERVAL_');
    }
    
    public function testConfirmSecondsPerIntervalDiffer()
    {
        // Make sure the seconds-per-(interval) constants differ (both in their
        // values and in their user-friendly versions).
        $this->confirmConstantsDiffer('UsageStats', 'SECONDS_PER_');
    }
    
    public function testGetTimeStart_dayWithCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_DAY;
        $intervalSize = \UsageStats::SECONDS_PER_DAY;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_DAY;
        $includeCurrentInterval = true;
        $beginningOfCurrentInterval = gmmktime(0, 0, 0);
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            )
        );
    }
    
    public function testGetTimeStart_dayWithoutCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_DAY;
        $intervalSize = \UsageStats::SECONDS_PER_DAY;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_DAY;
        $includeCurrentInterval = false;
        $beginningOfCurrentInterval = gmmktime(0, 0, 0);
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            )
        );
    }
    
    public function testGetTimeStart_hourWithCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_HOUR;
        $intervalSize = \UsageStats::SECONDS_PER_HOUR;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_HOUR;
        $includeCurrentInterval = true;
        $beginningOfCurrentInterval = gmmktime(gmdate("H"), 0, 0);
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            )
        );
    }
    
    public function testGetTimeStart_hourWithoutCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_HOUR;
        $intervalSize = \UsageStats::SECONDS_PER_HOUR;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_HOUR;
        $includeCurrentInterval = false;
        $beginningOfCurrentInterval = gmmktime(gmdate("H"), 0, 0);
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            )
        );
    }
    
    public function testGetTimeStart_minuteWithCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_MINUTE;
        $intervalSize = \UsageStats::SECONDS_PER_MINUTE;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_OTHER;
        $includeCurrentInterval = true;
        $beginningOfCurrentInterval = gmmktime(gmdate("H"), gmdate("i"), 0);
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            ),
            60 // Acceptable difference (in seconds) due to code execution time.
        );
    }
    
    public function testGetTimeStart_minuteWithoutCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_MINUTE;
        $intervalSize = \UsageStats::SECONDS_PER_MINUTE;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_OTHER;
        $includeCurrentInterval = false;
        $beginningOfCurrentInterval = gmmktime(gmdate("H"), gmdate("i"), 0);
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            ),
            60 // Acceptable difference (in seconds) due to code execution time.
        );
    }
    
    public function testGetTimeStart_secondWithCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_SECOND;
        $intervalSize = \UsageStats::SECONDS_PER_SECOND;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_OTHER;
        $includeCurrentInterval = true;
        $beginningOfCurrentInterval = time();
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            ),
            1 // Acceptable difference (in seconds) due to code execution time.
        );
    }
    
    public function testGetTimeStart_secondWithoutCurrentInterval()
    {
        // Arrange (define):
        $intervalName = \UsageStats::INTERVAL_SECOND;
        $intervalSize = \UsageStats::SECONDS_PER_SECOND;
        $numIntervals = \UsageStats::INTERVALS_TO_SHOW_OTHER;
        $includeCurrentInterval = false;
        $beginningOfCurrentInterval = time();
        
        // Arrange (calculate):
        $entireTimespanToShow = $intervalSize * $numIntervals;
        $expected = $beginningOfCurrentInterval - $entireTimespanToShow;
        if ($includeCurrentInterval) {
            // If we should include the current interval, move the expected
            // start time forward by one interval size.
            $expected += $intervalSize;
        }
        
        // Act:
        $actual = \UsageStats::getTimeStart(
            $intervalName,
            $includeCurrentInterval
        );
        
        // Assert:
        $this->assertEquals(
            $expected,
            $actual,
            sprintf(
                "Failed to return correct start time for usage-by-%s "
                . "(%sincluding the current, incomplete %s). \n"
                . "Expected: %s\n"
                . "Received: %s",
                $intervalName,
                ($includeCurrentInterval ? '' : 'NOT '),
                $intervalName,
                date('r', $expected),
                date('r', $actual)
            ),
            1 // Acceptable difference (in seconds) due to code execution time.
        );
    }
    
    public function testGetValidIntervalNames_isCompleteList()
    {
        // Arrange:
        $allIntervalNameConstantsByKey = self::getConstantsWithPrefix(
            'UsageStats',
            'INTERVAL_'
        );
        $allIntervalNames = array_values($allIntervalNameConstantsByKey);
        
        // Act:
        $actual = \UsageStats::getValidIntervalNames();
        
        // Assert:
        $this->assertEquals(
            $allIntervalNames,
            $actual,
            'Failed to retrieve the correct list of valid interval names.'
        );
    }
    
    public function testHasData_yes()
    {
        // Arrange:
        $usageStats = new \UsageStats(\UsageStats::INTERVAL_DAY);
        $entry = array(
            1416340920 => array(200 => 2),
            1416340980 => array(200 => 4),
            1416341520 => array(200 => 1),
        );
        $usageStats->addEntry('test', $entry);
        
        // Act:
        $hasData = $usageStats->hasData();
        
        // Assert;
        $this->assertTrue(
            $hasData,
            'Incorrectly reported that a non-empty UsageStats has no data.'
        );
    }
    
    public function testHasData_no()
    {
        // Arrange:
        $usageStats = new \UsageStats(\UsageStats::INTERVAL_DAY);
        
        // Act:
        $hasData = $usageStats->hasData();
        
        // Assert;
        $this->assertFalse(
            $hasData,
            'Incorrectly reported that an empty UsageStats has data.'
        );
    }
}
