<?php

class UsageStats extends CComponent
{
    const INTERVAL_DAY = 'day';
    const INTERVAL_HOUR = 'hour';
    const INTERVAL_MINUTE = 'minute';
    const INTERVAL_SECOND = 'second';
    
    const INTERVALS_TO_SHOW_DAY = 28;
    const INTERVALS_TO_SHOW_HOUR = 24;
    const INTERVALS_TO_SHOW_OTHER = 30;
    
    const SECONDS_PER_DAY = 86400;
    const SECONDS_PER_HOUR = 3600;
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_SECOND = 1;
    
    protected $intervalSize = null;
    protected $data = null;
    
    /**
     * Class for managing usage statistics.
     * 
     * @param string $intervalName The name of the time interval that each data
     *     point should represent (e.g. - 'day', 'hour', etc.). See the
     *     INTERVAL_* constants for the list of valid options.
     */
    public function __construct($intervalName)
    {
        // Initialize our array of data.
        $this->data = array();
        
        // Convert the given interval name to an interval size (and record it).
        $this->intervalSize = self::getSecondsPerInterval($intervalName);
    }
    
    /**
     * Get a JSON string representing this UsageStats instance.
     * 
     * @return string
     */
    public function __toString()
    {
        return json_encode(array(
            'intervalSize' => $this->intervalSize,
            'data' => $this->data,
        ), JSON_PRETTY_PRINT);
    }
    
    /**
     * Add a set of usage data to this collection of usage statistics.
     * 
     * @param string $displayName The name to show for this usage data.
     * @param array $usageData The usage data as returned by \Key->getUsage().
     *     Note that the Key referred to here is the Key model, NOT the ApiAxle
     *     Key class.
     * @throws \Exception
     */
    public function addEntry($displayName, $usageData)
    {
        // Prevent duplicate entries with the same name.
        if (isset($this->data[$displayName])) {
            throw new \Exception(
                'An entry named "' . $displayName . '" has already been added '
                . 'to this set of usage statistics.',
                1416340133
            );
        }
        
        // Record the given data.
        $this->data[$displayName] = $usageData;
    }
    
    /**
     * Combine the usage data for the two given arrays. Useful for merging
     * cached and uncached numbers returned by ApiAxle's Key->getStats(). 
     * 
     * <b>WARNING:</b> The first array will be modified, but it is probably best
     * to simply use the returned array than to count on the modified first
     * array to have the correct results.
     * 
     * @param array $one The first cagegory's usage statistics data.
     * @param array $two The second cagegory's usage statistics data.
     * @return array The resulting data.
     */
    public static function combineUsageCategoryArrays($one, $two)
    {
        // For each entry in the second category's array of data...
        foreach ($two as $timestamp => $hitData) {
            if ( ! isset($one[$timestamp])) {
                
                // If there was NOT an entry in the first array for this
                // timestamp, just add this one (since there's no collision).
                $one[$timestamp] = $hitData;
                
            } else {
                
                // Otherwise, combine them.
                foreach ($hitData as $responseCode => $numHits) {
                    if ( ! isset($one[$timestamp][$responseCode])) {
                        
                        // If there was no entry yet for this response code,
                        // just record it.
                        $one[$timestamp][$responseCode] = $numHits;
                        
                    } else {
                        
                        // Otherwise, add it to the number of hits we already
                        // had.
                        $one[$timestamp][$responseCode] += $numHits;
                    }
                }
            }
        }
        
        // Return the resulting (combined) data.
        return $one;
    }
    
    /**
     * Get the CSS color to use for a given display name.
     * 
     * @param string $name The name of the item whose color is needed.
     * @return string The CSS color, such as '#003399'.
     */
    public static function getColorsForName($name)
    {
        return '#' . substr(md5($name), -6);
    }
    
    /**
     * Get the current interval name.
     * 
     * @return string The interval name.
     */
    public function getCurrentIntervalName()
    {
        return self::getIntervalName($this->intervalSize);
    }
    
    /**
     * Return the name of the interval that represents the given number of
     * seconds (aka. time interval). Throws an exception if an unknown time
     * interval is given.
     * 
     * @param int $intervalSize The time interval size (in seconds).
     * @return string The time interval name.
     * @throws \Exception
     */
    public static function getIntervalName($intervalSize)
    {
        switch ($intervalSize) {
            case self::SECONDS_PER_SECOND:
                return self::INTERVAL_SECOND;
            case self::SECONDS_PER_MINUTE:
                return self::INTERVAL_MINUTE;
            case self::SECONDS_PER_HOUR:
                return self::INTERVAL_HOUR;
            case self::SECONDS_PER_DAY:
                return self::INTERVAL_DAY;
            default:
                throw new \Exception(
                    'Unknown time interval size: ' . $intervalSize,
                    1416426644
                );
        }
    }
    
    /**
     * Return the number of seconds in the named time interval. Throws an
     * exception if an unknown time interval name is given.
     * 
     * @param string $intervalName The time interval (e.g. - 'second', 'minute',
     *     'hour', 'day').
     * @return int The number of seconds.
     * @throws \Exception
     */
    public static function getSecondsPerInterval($intervalName)
    {
        switch ($intervalName) {
            case self::INTERVAL_SECOND:
                return self::SECONDS_PER_SECOND;
            case self::INTERVAL_MINUTE:
                return self::SECONDS_PER_MINUTE;
            case self::INTERVAL_HOUR:
                return self::SECONDS_PER_HOUR;
            case self::INTERVAL_DAY:
                return self::SECONDS_PER_DAY;
            default:
                throw new \Exception(
                    'Unknown time interval name: ' . $intervalName,
                    1416336557
                );
        }
    }
    
    /**
     * Generate the HTML for a usage chart using the data already added to this
     * UsageStats instance.
     * 
     * @param int $minimumWidth The minimum chart width (in pixels).
     * @param int $height The chart height (in pixels).
     * @param string $chartDivId The HTML id to use for the div that will
     *     contain the chart.
     * @return string The HTML (which may including JavaScript) for the chart.
     */
    public function generateChartHtml(
        $minimumWidth = 160,
        $height = 320,
        $chartDivId = 'usage-chart'
    ) {
        // Figure out how many intervals of data we'll show.
        $numIntervals = self::getNumIntervalsToShow($this->intervalSize);
        
        // Get the actual list of timestamps we'll show data points for.
        $timestamps = self::getTimestampsToShow(
            $this->intervalSize,
            $numIntervals
        );
        
        // Get the data that we'll use for the chart.
        $data = $this->getDataForChart($timestamps);
        
        // Get the x-axis values for JavaScript.
        $xAxisValues = array_map(
            function($t) { return $t * 1000; },
            $timestamps
        );
        
        // Get the y-axis values for JavaScript, figure out what the highest
        // Y-value is, and assemble the list of colors to use for the lines:
        $yAxisValuesArrays = array();
        $highestYValue = 0;
        $colors = array();
        foreach ($data as $displayName => $usageData) {
            
            // Add the next entry to the list of colors.
            $colors[] = self::getColorsForName($displayName);
            
            // Set up an empty array to hold this collection of usage data.
            $tempYAxisValues = array();
            
            // For each timestamp in the usage data...
            foreach ($usageData as $timestamp => $responseAndHits) {
                
                // Add up the hits for each type of response (200, 404, etc.).
                $hitsToShow = 0;
                foreach ($responseAndHits as $responseCode => $hits) {
                    $hitsToShow += $hits;
                }
                
                // Add that to our list of usage data for this collection of
                // usage data.
                $tempYAxisValues[] = $hitsToShow;
            }
            
            // Record this collection's display name and collection of data.
            $yAxisValuesArrays[$displayName] = $tempYAxisValues;
            
            // Keep track of the highest Y-value we've found so far.
            $highestYValue = max($highestYValue, max($tempYAxisValues));
        }
        
        // Figure out what value we want to use as the max Y-value that the
        // chart should show in order to have the Y-axis steps be at nice
        // values, such as the lowest power of ten that's greater than or equal
        // to the highest Y-value we found.
        $topYValueToShow = 10;
        while ($topYValueToShow < $highestYValue) {
            $topYValueToShow *= 10;
        }
        
        // Return the resulting HTML.
        return \Yii::app()->controller->renderPartial(
            '//partials/usage-chart',
            array(
                'chartDivId' => $chartDivId,
                'height' => $height,
                'minimumWidth' => $minimumWidth,
                'xAxisValues' => $xAxisValues,
                'yAxisValuesArrays' => $yAxisValuesArrays,
                'topYValueToShow' => $topYValueToShow,
                'intervalName' => self::getIntervalName($this->intervalSize),
                'colors' => $colors,
                'hasData' => (count($colors) > 0),
            ),
            true
        );
    }
    
    /**
     * Get the list of timestamps that we should show in the chart, based on the
     * given interval size and number of intervals.
     * 
     * @param int $intervalSize The number of seconds represented by each
     *     interval.
     * @param int $numIntervals The number of intervals (aka. timeframes).
     * @param boolean $includeCurrentInterval (Optional:) Whether to include the
     *     current time interval, even though we only have incomplete data for
     *     it. Defaults to true.
     * @return array The list of timestamps.
     */
    public static function getTimestampsToShow(
        $intervalSize,
        $numIntervals,
        $includeCurrentInterval = true
    ) {
        // Find a timestamp in the past that we know would be included in the
        // data from ApiAxle if anything happened in that timeframe (aka. 
        // that interval).
        // 
        // Currently using one interval before midnight (UTC) last night.
        $tempTime = gmmktime(0, 0, 0) - $intervalSize;
        
        // Repeatedly add the given interval size to that timestamp to find the
        // most recent interval to show.
        $finalTimestamp = $tempTime;
        $stopBefore = time() - ($includeCurrentInterval ? 0 : $intervalSize);
        for ($i = $tempTime; $i < $stopBefore; $i += $intervalSize) {
            $finalTimestamp = $i;
        }
        
        // Populate an array (ending with that final timestamp) which has the
        // given number of timestamps in it.
        $backwardsResults = array();
        $timestampToAdd = $finalTimestamp;
        for ($i = 0; $i < $numIntervals; $i++) {
            $backwardsResults[] = $timestampToAdd;
            $timestampToAdd -= $intervalSize;
        }
        return array_reverse($backwardsResults);
    }

    /**
     * Get the timestamp to start with for the given interval (taking into
     * account whether to include the current interval).
     * 
     * @param string $intervalName The name of the time interval that each data
     *     point should represent (e.g. - 'day', 'hour', etc.). See the
     *     INTERVAL_* constants for the list of valid options.
     * @param boolean $includeCurrentInterval (Optional:) Whether to include the
     *     current time interval, even though we only have incomplete data for
     *     it. Defaults to true.
     * @return int The timestamp.
     */
    public static function getTimeStart(
        $intervalName,
        $includeCurrentInterval = true
    ) {
        // Get the list of timestamps to be shown.
        $intervalSize = self::getSecondsPerInterval($intervalName);
        $numInterals = self::getNumIntervalsToShow($intervalSize);
        $timestamps = self::getTimestampsToShow(
            $intervalSize,
            $numInterals,
            $includeCurrentInterval
        );
        
        // Return the first one.
        return $timestamps[0];
    }

    /**
     * Get this UsageStats's data in the form needed for generating the chart
     * HTML/JS.
     * 
     * @param array $timestamps The timestamps that we will show on the chart.
     *     These should line up with the timestamps we have data for. See
     *     getNumIntervalsToShow() and getTimestampsToShow().
     * @return array The array of data for the chart.
     */
    public function getDataForChart($timestamps)
    {
        // Assemble an array of entries, each being an array of usage stats that
        // has each of the desired timestamps (using an empty array for
        // timestamps not in the usage data we have for that entry).
        $data = array();
        foreach ($this->data as $displayName => $usageData) {
            $tempEntry = array();
            foreach ($timestamps as $timestamp) {
                if (isset($usageData[$timestamp])) {
                    $tempEntry[$timestamp] = $usageData[$timestamp];
                } else {
                    $tempEntry[$timestamp] = array();
                }
            }
            $data[$displayName] = $tempEntry;
        }
        
        // Return the resulting collection of data for the chart.
        return $data;
    }
    
    /**
     * Return the number of intervals to show based on the interval size. This
     * is to enable us to show a logical number of data points based on the
     * timeframe that each data point represents (such as 24 hours, 28 days,
     * etc.).
     * 
     * @param int $intervalSize The number of seconds represented by each
     *     interval.
     * @return int The number of intervals to show.
     */
    public static function getNumIntervalsToShow($intervalSize)
    {
        switch ($intervalSize) {
            
            case self::SECONDS_PER_DAY:
                return self::INTERVALS_TO_SHOW_DAY;
                
            case self::SECONDS_PER_HOUR:
                return self::INTERVALS_TO_SHOW_HOUR;
                
            default:
                return self::INTERVALS_TO_SHOW_OTHER;
        }
    }
    
    public static function getValidIntervalNames()
    {
        return array(
            self::INTERVAL_DAY,
            self::INTERVAL_HOUR,
            self::INTERVAL_MINUTE,
            self::INTERVAL_SECOND,
        );
    }
    
    public function hasData()
    {
        return (($this->data !== null) && (count($this->data) > 0));
    }
}
