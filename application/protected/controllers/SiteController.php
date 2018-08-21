<?php
namespace Sil\DevPortal\controllers;

use GuzzleHttp\Exception\ConnectException;
use PDO;
use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\components\AuthManager;
use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\SiteText;

class SiteController extends \Controller
{
    ///**
    // * Declares class-based actions.
    // */
    //public function actions()
    //{
    //    return array(
    //        // captcha action renders the CAPTCHA image displayed on the contact page
    //        'captcha'=>array(
    //            'class'=>'CCaptchaAction',
    //            'backColor'=>0xFFFFFF,
    //        ),
    //        // page action renders "static" pages stored under 'protected/views/site/pages'
    //        // They can be accessed via: index.php?r=site/page&view=FileName
    //        'page'=>array(
    //            'class'=>'CViewAction',
    //        ),
    //    );
    //}

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        if ( ! \Yii::app()->user->isGuest) {
            $this->redirect(array('dashboard/'));
        }
        
        $this->wakeTheDatabase();
        
        if (\Yii::app()->params['showPopularApis']) {
            $popularApis = Api::getPopularApis();
        } else {
            $popularApis = null;
        }
        
        $authManager = new AuthManager();
        $loginOptions = $authManager->getLoginOptions();
        
        $this->render('index', array(
            'loginOptions' => $loginOptions,
            'logoUrls' => \Utils::getLogoUrls(),
            'popularApis' => $popularApis,
            'homeLowerLeftHtml' => SiteText::getHtml('home-lower-left'),
            'homeLowerRightHtml' => SiteText::getHtml('home-lower-right'),
        ));
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        $error = \Yii::app()->errorHandler->error;
        if ($error) {
            if (\Yii::app()->request->isAjaxRequest) {
                echo $error['message'];
            } else {
                $this->render('error', $error);
            }
        }
    }
    
    /**
     * This action checks the application's ability to connect to mysql and 
     * apiaxle. It returns an HTTP code of 200 and content of 'OK' if all
     * is good, else it returns a 500 and a brief error if not good.
     */
    public function actionSystemCheck()
    {
        try {
            /**
             * Get an apixle object and try to fetch details about 'apiaxle' api
             */
            $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
            $apiInfo = $apiAxle->getApiInfo('apiaxle');
            $data = $apiInfo->getData();
            
            // Check that an expected parameter is set.
            if ($data['protocol'] !== null) {
                header('Content-type: text/plain', true, 200);
                echo 'OK';
            } else {
                header('Content-type: text/plain', true, 500);
                echo 'Error with api proxy, expected attribute not set';
            }
        } catch (\Exception $e) {
            /* Catch any exceptions from ApiAxle class and output error: */
            header('Content-type: text/plain', true, 500);
            echo 'Error with api proxy, code: ' . $e->getCode();
            
            // If we are in an environment where we should send email
            // notifications...
            if (\Yii::app()->params['mail'] !== false) {
                
                // Get some identifier for which server this is.
                if (isset($_SERVER['HTTP_HOST'])) {
                    $serverIdentifier = $_SERVER['HTTP_HOST'];
                } else {
                    $serverIdentifier = 'server?';
                }
                
                // Email us the full error info.
                $mail = \Utils::getMailer();
                $alertsEmail = \Yii::app()->params['alertsEmail'];
                if ( ! empty($alertsEmail)) {
                    $mail->setTo($alertsEmail);
                    $mail->setSubject(sprintf(
                        'System Check ERROR: API Dev. Portal (%s)',
                        $serverIdentifier
                    ));
                    $mail->setBody(nl2br(sprintf(
                        "%s \n\n"
                        . "<b>SERVER:</b> %s\n"
                        . "<b>DATE:</b> %s\n"
                        . "<b>CODE:</b> %s\n"
                        . "<b>MESSAGE:</b> %s\n"
                        . "<b>STACK TRACE:</b> \n"
                        . "%s",
                        'The API Developer Portal system check returned an error.',
                        $serverIdentifier,
                        date('D, d M Y H:i:s O'),
                        $e->getCode(),
                        $e->getMessage(),
                        $e->getTraceAsString()
                    )));
                    $mail->send();
                }
            }
        }
    }

    public function actionPrivacyPolicy()
    {
        $this->render('privacy-policy',array(
            'contactLink' => \Utils::getContactLinkValue(),
        ));
    }
    
    public function actionWake()
    {
        try {
            /** @var \CDbConnection $databaseConnection */
            $databaseConnection = \Yii::app()->db;
            $databaseConnection->getConnectionStatus();
            header('Content-Type: text/plain', true, 204);
        } catch (\Throwable $t) {
            header('Content-Type: text/plain', true, 500);
            // Don't show the error message. We don't want to expose that info.
        }
    }
    
    protected function wakeTheDatabase()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $client->get($this->createAbsoluteUrl('site/wake'), [
                'timeout' => 1,
            ]);
        } catch (ConnectException $e) {
            // We specifically want it to time out and let us proceed without
            // delay, so ignore this.
        }
    }
}
