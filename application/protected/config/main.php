<?php

use Sil\DevPortal\components\HybridAuthManager;
use Sil\DevPortal\components\log\StdoutLogRoute;
use Sil\PhpEnv\Env;

/**
 * Get config settings from ENV vars or set defaults
 */
$mysqlHost = Env::get('MYSQL_HOST');
$mysqlDatabase = Env::get('MYSQL_DATABASE');
$mysqlUser = Env::get('MYSQL_USER');
$mysqlPassword = Env::get('MYSQL_PASSWORD');
$mailerHostname = Env::get('MAILER_HOSTNAME', 'smtp.gmail.com');
$mailerUsername = Env::get('MAILER_USERNAME', false); // Defaults to false to mimic previous getenv() behavior.
$mailerPassword = Env::get('MAILER_PASSWORD', false); // Defaults to false to mimic previous getenv() behavior.
$mailerFromEmail = Env::get('MAILER_FROM_EMAIL', $mailerUsername);
$appEnv = Env::get('APPLICATION_ENV', 'not set');

// APP_NAME is deprecated. Prefer APP_DISPLAY_NAME.
$appDisplayName = Env::get('APP_DISPLAY_NAME', Env::get('APP_NAME', 'Developer Portal'));

$adminEmail = Env::get('ADMIN_EMAIL');
$alertsEmail = Env::get('ALERTS_EMAIL');
$apiaxleEndpoint = Env::get('APIAXLE_ENDPOINT');
$apiaxleKey = Env::get('APIAXLE_KEY');
$apiaxleSecret = Env::get('APIAXLE_SECRET');
$apiaxleSslVerifyPeer = Env::get('APIAXLE_SSL_VERIFYPEER', true);
$contactUsUrl = Env::get('CONTACT_US_URL');
$gaEnabled = Env::get('GA_ENABLED', false);
$gaTrackingId = Env::get('GA_TRACKING_ID');
$githubOAuthClientId = Env::get('GITHUB_OAUTH_CLIENT_ID');
$githubOAuthClientSecret = Env::get('GITHUB_OAUTH_CLIENT_SECRET');
$githubOAuthEnabled = Env::get('GITHUB_OAUTH_ENABLED', false);
$googleOAuthClientId = Env::get('GOOGLE_OAUTH_CLIENT_ID');
$googleOAuthClientSecret = Env::get('GOOGLE_OAUTH_CLIENT_SECRET');
$googleOAuthEnabled = Env::get('GOOGLE_OAUTH_ENABLED', false);
$hidePublicApisFromGuests = Env::get('HIDE_PUBLIC_APIS_FROM_GUESTS', false);
$samlEnabled = Env::get('SAML_ENABLED', false);
$samlIdpEntityId = Env::get('SAML_IDP');
$samlIdpName = Env::get('SAML_IDP_NAME');
$samlTrustEmailFor = Env::get('SAML_TRUST_EMAIL_FOR');
$showPopularApis = (bool)Env::get('SHOW_POPULAR_APIS', false);
$themeColor = Env::get('THEME_COLOR');

// Define a path alias for the Bootstrap extension as it's used internally.
Yii::setPathOfAlias('bootstrap', dirname(__FILE__) . '/../extensions/bootstrap');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => $appDisplayName,
    'theme' => 'bootstrap',
    'controllerNamespace' => '\\Sil\\DevPortal\\controllers',
    
    // preloading 'log' component
    'preload' => array('log'),
    
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.utils.*',
        'ext.EHttpClient.*',
        'ext.YiiMailer.YiiMailer',
    ),
    
    'modules' => array(
        
    ),
    // application components
    'components' => array(
        'bootstrap' => array(
            'class' => 'bootstrap.components.Bootstrap',
        ),
        'user' => array(
            
            // Enable cookie-based authentication?
            'allowAutoLogin' => false,
            
            'loginUrl' => array('auth/login'),
            'class' => 'WebUser',
            'autoUpdateFlash' => false,
            
            // Seconds of inactivity before session timeout. For details, see:
            // http://www.yiiframework.com/doc/api/1.1/CWebUser#authTimeout-detail
            'authTimeout' => 14400, // 14400 seconds = 4 hours
        ),
        'urlManager' => array(
            'class' => 'UrlManager',
            'urlFormat' => 'path',
            'showScriptName' => false,
            'rules' => array(
                'dashboard/<interval:[a-z]+>/<chart:[a-z-]+>/usage-chart/<rewindBy:[0-9]+>' => 'dashboard/usage-chart',
                'dashboard/<interval:[a-z]+>/<chart:[a-z-]+>/usage-chart' => 'dashboard/usage-chart',
                'dashboard/<interval:[a-z]+>/<chart:[a-z-]+>/<rewindBy:[0-9]+>' => 'dashboard/index',
                'dashboard/<interval:[a-z]+>/<chart:[a-z-]+>' => 'dashboard/index',
                'dashboard/<interval:[a-z]+>' => 'dashboard/index',
                '<controller:[\w\-]+>/api<apiAction:[\w\-]+>/<code:([a-z0-9]{1}[a-z0-9\-]{1,}[a-z0-9]{1})>' => '<controller>/api<apiAction>',
                'api/<action:[\w\-]+>/<code:([a-z0-9]{1}[a-z0-9\-]{1,}[a-z0-9]{1})>' => 'api/<action>',
                'auth/login/<authType:[\w-]+>/<providerSlug:[\w-]+>' => 'auth/login',
                'auth/login/<authType:[\w-]+>' => 'auth/login',
                '<controller:[\w\-]+>/<id:\d+>' => '<controller>/view',
                '<controller:[\w\-]+>/<action:[\w\-]+>/<id:\d+>' => '<controller>/<action>',
                '<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>',
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
            'autoConnect' => false,
        ),
        'errorHandler' => array(
            // Use 'site/error' action to display errors.
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => StdoutLogRoute::class,
                    'levels' => 'error, warning',
                    'filter' => array(
                        'class' => 'CLogFilter',
                        'logVars' => array(),
                    ),
                ),
                //// Uncomment the following to show log messages on web pages:
                //array(
                //    'class' => 'CWebLogRoute',
                //),
            ),
        ),            
        'request' => array(
            'enableCsrfValidation' => true,
            'enableCookieValidation'=>true,
        ),
        'assetManager' => array(
            'newFileMode' => 0644,
            'newDirMode' => 0755,
        ),
    ),
    
    // Application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        'adminEmail' => $adminEmail,
        'alertsEmail' => $alertsEmail,
        'contactUsUrl' => $contactUsUrl,
        'hidePublicApisFromGuests' => (bool)$hidePublicApisFromGuests,
        'showPopularApis' => $showPopularApis,
        'themeColor' => $themeColor,
        'saml' => array(
            'default-sp' => 'default-sp',
            'enabled' => (bool)$samlEnabled,
            'idpName' => $samlIdpName ?? 'SAML',
            'map' => array(
                'authProviderUserIdentifierField' => 'employeeNumber',
                'firstNameField' => 'givenName',
                'lastNameField' => 'sn',
                'displayNameField' => 'displayName',
                'emailField' => 'mail',
                'usernameField' => 'uid',
                'groupsField' => 'groups',
                'uuidField' => 'entryUUID',
            ),
            'trustEmailAsFallbackIdFor' => $samlTrustEmailFor,
            'authSources' => array(
                
                // Auth Provider Name => IdP Entity ID
                $samlIdpName => $samlIdpEntityId,
            ),
        ),
        'hybridAuth' => array(
            'providers' => array(
                'Google' => array(
                    'enabled' => (bool)$googleOAuthEnabled,
                    'keys' => array(
                        'id' => $googleOAuthClientId,
                        'secret' => $googleOAuthClientSecret,
                    ),
                    'scope' => 'email profile',
                ),
                'GitHub' => array(
                    'enabled' => (bool)$githubOAuthEnabled,
                    'keys' => array(
                        'id' => $githubOAuthClientId,
                        'secret' => $githubOAuthClientSecret,
                    ),
                    'scope' => 'user:email',
                    'wrapper' => array(
                        'class' => 'Hybrid_Providers_GitHub',
                        'path' => HybridAuthManager::getPathToAdditionalProviderFile('GitHub'),
                    )
                ),
            )
        ),

        'friendlyDateFormat' => 'F j, Y, g:ia (T)',
        'shortDateFormat'    => 'm/d/y',
        'shortDateTimeFormat' => 'n/j/y g:ia',
        'allInsiteUsersGroup' => 'grp_custgrp_po-sil_cg-641',
        'mail' => array(
            'from' => $mailerUsername,
        ),
        'smtp' => array(
            'host' => $mailerHostname,
            'port' => 465,
            'fromEmail' => $mailerFromEmail,
            'fromName' => 'API Admin (no-reply)',
            'auth' => true,
            'secure' => 'ssl',
            'user' => $mailerUsername,
            'pass' => $mailerPassword,
        ),
        "apiaxle" => array(
            'baseUrl' => $apiaxleEndpoint,
            "endpoint" => $apiaxleEndpoint,
            "key" => $apiaxleKey,
            "secret" => $apiaxleSecret,
            "ssl_verifypeer" => $apiaxleSslVerifyPeer,
        ),
        "google_analytics" => array(
            "enabled" => $gaEnabled,
            "tracking_id" => $gaTrackingId,
        ),
    ),
);
