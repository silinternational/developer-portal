<?php
namespace Sil\DevPortal\controllers;

use Sil\DevPortal\models\User;

class DashboardController extends \Controller
{
    const CHART_ALL_APIS = 'all-api';
    const CHART_MY_APIS = 'api';
    const CHART_MY_KEYS = 'key';
    const CHART_TOTALS = 'total';
    
    public $layout = '//layouts/left-menu';
    
    public function actionUsageChart($rewindBy = 0)
    {
        $defaultChart = $this->getDefaultChart();
        
        // Get the name of the interval we should show in the usage chart.
        $interval = $this->getIntervalToShow();
        
        // Get the user model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // If we should include API Owner content on the dashboard...
        if ($currentUser->hasOwnerPrivileges()) {
            
            // Get the identifier for which chart to show (the user's keys'
            // usage, or usage of the user's APIs).
            $chart = \Yii::app()->request->getParam('chart', $defaultChart);

        } else {
            
            // Otherwise, just get the normal user's usage data.
            $chart = $defaultChart;
        }

        // Get the appropriate set of usage data for the chart.
        if ($chart === self::CHART_MY_KEYS) {
            $usageStats = $currentUser->getUsageStatsForKeys($interval, $rewindBy);
        } elseif ($chart === self::CHART_ALL_APIS) {
            $usageStats = $currentUser->getUsageStatsForAllApis($interval, $rewindBy);
        } elseif ($chart === self::CHART_TOTALS) {
            $usageStats = $currentUser->getUsageStatsTotals($interval, $rewindBy);
        } else {
            $usageStats = $currentUser->getUsageStatsForApis($interval, $rewindBy);
        }
        
        $this->renderPartial('usage-chart', [
            'usageStats' => $usageStats,
        ]);
    }
    
    public function actionIndex($rewindBy = 0)
    {
        $defaultChart = $this->getDefaultChart();
        
        // Get the name of the interval we should show in the usage chart.
        $interval = $this->getIntervalToShow();
        
        // Get the user model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Get the list of the APIs that the user either has a Key for (of any
        // status). To do this, get the list of their Keys, but include the
        // names of the APIs.
        $keys = $currentUser->getKeysWithApiNames();
        
        // If we should include API Owner content on the dashboard...
        if ($currentUser->hasOwnerPrivileges()) {
            
            // Get the identifier for which chart to show (the user's keys'
            // usage, or usage of the user's APIs).
            $chart = \Yii::app()->request->getParam('chart', $defaultChart);

            // Get the list of APIs that this user owns.
            $apisOwnedByUser = $currentUser->apis;
            
        } else {
            
            // Otherwise, just get the normal user's usage data.
            $chart = $defaultChart;
            $apisOwnedByUser = null;
        }
        
        // Show the page.
        $this->render('index', array(
            'user' => $currentUser,
            'keys' => $keys,
            'apisOwnedByUser' => $apisOwnedByUser,
            'currentInterval' => $interval,
            'chart' => $chart,
            'rewindBy' => (int)$rewindBy,
        ));
    }
    
    protected function getDefaultChart()
    {
        // Default to showing the key (not api) chart, since both developer- and
        // owner-type users can see that.
        return self::CHART_MY_KEYS;
    }

    /**
     * Based on the relevant request param, get the name of the time interval to
     * show usage for.
     * 
     * @return string
     */
    protected function getIntervalToShow()
    {
        // Figure out what detail level to show in the usage chart.
        $requestedInterval = \Yii::app()->request->getParam('interval');
        switch ($requestedInterval) {
            case \UsageStats::INTERVAL_DAY:
            case \UsageStats::INTERVAL_HOUR:
            case \UsageStats::INTERVAL_MINUTE:
            case \UsageStats::INTERVAL_SECOND:
                $interval = $requestedInterval;
                break;
            default:
                $interval = \UsageStats::INTERVAL_DAY;
                break;
        }
        return $interval;
    }
}
