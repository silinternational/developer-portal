<?php

// Define a constant to indicate that this is a testing environment.
define('APPLICATION_ENV', 'testing');

// Composer autoloading
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    $loader = include_once __DIR__ . '/../../vendor/autoload.php';
} else {
    die('Unable to find Composer autoloader file.');
}

// Bring in the Yii framework.
$yiit = dirname(__FILE__) . '/../../vendor/yiisoft/yii/framework/yiit.php';
require_once($yiit);

// Assemble the path to the appropriate config data.
$config = dirname(__FILE__) . '/../config/test.php';

// Configure Phake.
\Phake::setClient(Phake::CLIENT_PHPUNIT6);

// Tell Yii to let other autoloaders attempt to find a class, too.
Yii::$enableIncludePath = false;

// Run the application.
Yii::createWebApplication($config);
