<?php
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));

// change the following paths if necessary
$yii=__DIR__.'/../vendor/yiisoft/yii/framework/yii.php';
require_once($yii);

$config = require __DIR__.'/config/console.php';

// change the following paths if necessary
$yiic=__DIR__.'/../vendor/yiisoft/yii/framework/yiic.php';

require_once($yiic);