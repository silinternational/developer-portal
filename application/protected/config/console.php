<?php
/**
 * Get config settings from ENV vars or set defaults
 */
$mysqlHost = getenv('MYSQL_HOST') ?: null;
$mysqlDatabase = getenv('MYSQL_DATABASE') ?: null;
$mysqlUser = getenv('MYSQL_USER') ?: null;
$mysqlPassword = getenv('MYSQL_PASSWORD') ?: null;
// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'API Console',
    
    // preloading 'log' component
    'preload' => array('log'),

    // application components
    'components' => array(
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
        'db' => array(
            'class'=>'CDbConnection', //    ======>   THIS IS IMPORTANT
            "connectionString" => "mysql:host=$mysqlHost;dbname=$mysqlDatabase",
            "username" => $mysqlUser,
            "password" => $mysqlPassword,
            'emulatePrepare' => false,
            'charset' => 'utf8',
            'tablePrefix' => '',
        ),
    ),
    'commandMap' => array(
        'migrate' => array(
            'class' => 'system.cli.commands.MigrateCommand',
            'migrationPath' => 'application.migrations',
            'migrationTable' => '{{yii_migrations}}',
            'connectionID' => 'db',
        ),
        'dbreset' => array(
            'class' => 'application.commands.DbResetCommand'
        ),
    ),
);