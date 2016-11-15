<?php
namespace Sil\DevPortal\controllers;

use Sil\DevPortal\components\AuthManager;
use Stringy\StaticStringy as SS;

class AuthController extends \Controller
{
    /**
     * The HybridAuth endpoint, needed for authentications managed by
     * HybridAuth.
     */
    public function actionHybridEndpoint()
    {
        \Hybrid_Endpoint::process();
    }
    
    public function actionLogin($authType = null, $providerSlug = null)
    {
        $authManager = new AuthManager();
        
        /* If there is no return-to already defined and the user came from
         * somewhere (other than the auth controller) on this website, send
         * them back to the page they came from.  */
        if (\Yii::app()->user->getReturnUrl() === '/') {
            $referrer = \Yii::app()->request->getUrlReferrer();
            $absoluteUrlForThisWebsite = SS::ensureRight(\Yii::app()->getBaseUrl(true), '/');
            $authControllerUrl = $absoluteUrlForThisWebsite . 'auth/';
            if ($referrer &&
                SS::startsWith($referrer, $absoluteUrlForThisWebsite) &&
                ( ! SS::startsWith($referrer, $authControllerUrl))) {
                \Yii::app()->user->setReturnUrl($referrer);
            }
        }

        if ($authType === null) {
            $authType = $authManager->getDefaultAuthType();
            if ($authType === null) {
                $this->redirect(['auth/login-options']);
            }
        }

        try {
            $identity = $authManager->getIdentityForAuthType($authType);
        } catch (\InvalidArgumentException $e) {
            \Yii::log($e->getMessage());
            throw new \CHttpException(
                404,
                'Oops! That is not one of the ways we allow people to log in. '
                . 'Please go back to the normal login page and try again.',
                1441909330
            );
        }
        
        /* Attempt to authenticate the user (which may itself involve
        /* redirecting the user to log in somewhere).  */
        if ($identity->authenticate($providerSlug)) {
            \Yii::app()->user->login($identity);
            $this->redirect(\Yii::app()->user->getReturnUrl());
        } else {

            if ($identity->errorMessage) {
                \Yii::app()->user->setFlash(
                    'error',
                    sprintf(
                        '<div><p><b>Error!</b></p></div>'
                        . '<div>%s</div> '
                        . '<div><p>If you believe this is a mistake, please '
                        . '<a href="%s">contact us</a>.</p></div>',
                        \CHtml::encode($identity->errorMessage),
                        \CHtml::encode(\Utils::getContactLinkValue())
                    )
                );
            }
            $this->redirect(\Yii::app()->homeUrl);
        }
    }
    
    public function actionLoginOptions()
    {
        $authManager = new AuthManager();
        
        $loginOptions = $authManager->getLoginOptions();
        
        $this->render('login-options', array(
            'loginOptions' => $loginOptions,
        ));
    }
    
    public function actionLogout()
    {
        /* @var $webUser \WebUser */
        $webUser = \Yii::app()->user;
        $authType = $webUser->getAuthType();
        $authProvider = $webUser->getAuthProvider();
        
        $authManager = new AuthManager();
        $authManager->logout($webUser);
        
        // If logging out didn't redirect the user, show them a logged out
        // screen with the appropriate message based in which authentication
        // service they used to log in.
        $this->render('logged-out', array(
            'messageHtml' => $authManager->getLoggedOutMessageHtml(
                $authType,
                $authProvider
            ),
        ));
    }
    
    public function actionIdentity()
    {
        if ( ! \Yii::app()->user->isGuest) {
            $this->render('identity');
        } else {
            $this->redirect('/');
        }
    }
}
