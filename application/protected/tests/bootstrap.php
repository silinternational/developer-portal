<?php

// Define a constant to indicate that this is a testing environment.
define('APPLICATION_ENV', 'testing');

require_once __DIR__.'/../utils/Utils.php';

// simpleSAMLphp autoloading
if (file_exists(__DIR__ . '/../../simplesamlphp/lib/_autoload.php')) {
    $loader = include_once __DIR__ . '/../../simplesamlphp/lib/_autoload.php';
} else {
    die('Unable to find simpleSAMLphp autoloader file.');
}

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

require_once(dirname(__FILE__) . '/WebTestCase.php');

// Configure Phake.
\Phake::setClient(Phake::CLIENT_PHPUNIT);

// Run the application.
Yii::createWebApplication($config);
