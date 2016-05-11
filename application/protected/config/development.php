<?php

// Configuration settings specific to the "development" environment.

return array(
    'modules' => array(
        'generatorPaths' => array(
            'bootstrap.gii',
        ),
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => 'developer-portal-123',
            
            // If removed, Gii defaults to localhost only. Edit carefully to
            // taste.
            'ipFilters' => array('127.0.0.1', '::1'),
        ),
    ),
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=localhost;dbname=developer_portal',
            'username' => 'developer_portal',
            'password' => 'developer_portal',
            'class'=>'CDbConnection',
            'emulatePrepare' => true,
            'charset' => 'utf8',
            'tablePrefix' => '',
        ),
    ),
    'params' => array(
        'apiaxle' => array(
            'endpoint' => 'http://apiaxle.api.proxy:80/v1',
            'key' => 'developer-portal-dev-key',
            'secret' => 'developer-portal-dev-secret',
            'ssl_verifypeer' => false,
            //'ssl_cainfo' => null,
            //'ssl_capath' => null,
            'proxy_enable' => false,
            //'proxy_host' => '127.0.0.1',
            //'proxy_port' => '8888',
        ),
    ),
);