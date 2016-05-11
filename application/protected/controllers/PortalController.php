<?php

/**
 * NOTE: This is only used for redirects to the new URLS now.
 */
class PortalController extends Controller
{    
    public function actionApiDocs($code)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/details/', 'code' => $code), true, 301);
    }

    public function actionApiUsage($code)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/usage/', 'code' => $code), true, 301);
    }

    public function actionApis()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('api/'), true, 301);
    }

    public function actionApiDetails($code)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/details/', 'code' => $code), true, 301);
    }

    public function actionFaqDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/faq/details/', 'id' => $id), true, 301);
    }

    public function actionFaqs()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/faq/'), true, 301);
    }

    public function actionIndex()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/dashboard/'), true, 301);
    }

    public function actionKeys()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('//key/mine'), true, 301);
    }

    public function actionKeyDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('//key/details', 'id' => $id), true, 301);
    }

    public function actionDeleteKey($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key/delete/', 'id' => $id), true, 301);
    }

    public function actionResetKey($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key/reset/', 'id' => $id), true, 301);
    }

    public function actionRequestKey($id)
    {
        /**
         * Get the API whose ID is specified in the URL.
         * Expects the pk of an Api as 'id'.
         */
        $api = $this->getPkOr404('Api');
        
        // Send a permanent redirect to the new version of this page.
        $this->redirect(
            array('/api/request-key/', 'code' => $api->code),
            true,
            301
        );
    }

    public function actionKeyRequests()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key-request/'), true, 301);
    }

    public function actionKeyRequestDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key-request/details/', 'id' => $id), true, 301);
    }
    
    /**
     * Render or process request to test an API
     */
    public function actionPlayground()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/playground/'), true, 301);
    }
}
