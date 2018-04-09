<?php

// Set the path for the Yii framework.
$yii = __DIR__.'/../vendor/yiisoft/yii/framework/yii.php';

// Composer autoloading
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    $loader = include_once __DIR__ . '/../vendor/autoload.php';
}

// Bring in the Yii framework.
require_once($yii);

// Pull in (and merge) the appropriate config data.
$config = require __DIR__ . '/../protected/config/main.php';

// Define application environment (defaulting to 'production' if not set).
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'production'));

// If this is a dev. environment, show more debug info.
if (APPLICATION_ENV === 'development') {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
}

// Run the application with the resulting config settings.
Yii::createWebApplication($config)->run();
