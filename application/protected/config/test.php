<?php
/**
 * Get config settings from ENV vars or set defaults
 */
$mysqlHost = getenv('MYSQL_HOST') ?: null;
$mysqlDatabase = getenv('MYSQL_DATABASE') ?: null;
return CMap::mergeArray(
    require(dirname(__FILE__).'/main.php'),
    array(
        'components' => array(
            'db' => array(
                "connectionString" => "mysql:host=$mysqlHost;dbname=$mysqlDatabase",
            ),
            'fixture' => array(
                'class' => 'system.test.CDbFixtureManager',
            ),
            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    array(
                        'class' => 'CFileLogRoute',
                        'levels' => 'error, warning, debug',
                    ),
                ),
            ),
            'request' => array(
                'hostInfo' => 'http://developer-portal.local',
                'baseUrl' => '',
                'scriptUrl' => '',
            ),
            'user' => array(
                'allowAutoLogin' => false,
                'loginUrl' => '/auth/testLogin',
                'class' => 'application.components.UnitTestUser',
            ),
        ),
        'params' => array(
            'mail' => false,
            'smtp' => false,
        ),
    )
);
