<?php

/**
 * NOTE: This is only used for redirects to the new URLS now.
 */
class AdminController extends Controller
{
    public function actionApiAdd()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/add/'), true, 301);
    }

    public function actionApiDelete($id)
    {
        /**
         * Get the API whose ID is specified in the URL.
         * Expects the pk of an Api as 'id'.
         */
        $api = $this->getPkOr404('Api');
        
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/delete/', 'code' => $api->code), true, 301);
    }

    public function actionApiDetails($code)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/api/details/', 'code' => $code), true, 301);
    }

    public function actionApiDocsEdit($id)
    {
        /**
         * Get the API whose ID is specified in the URL.
         * Expects the pk of an Api as 'id'.
         */
        $api = $this->getPkOr404('Api');
        
        // Send a permanent redirect to the new version of this page.
        $this->redirect(
            array('/api/docs-edit/', 'code' => $api->code),
            true,
            301
        );
    }

    public function actionApiEdit($id)
    {
        /**
         * Get the API whose ID is specified in the URL.
         * Expects the pk of an Api as 'id'.
         */
        $api = $this->getPkOr404('Api');
        
        // Send a permanent redirect to the new version of this page.
        $this->redirect(
            array('/api/edit/', 'code' => $api->code),
            true,
            301
        );
    }
    
    public function actionApis()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('api/'), true, 301);
    }

    public function actionFaqAdd()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/faq/add/'), true, 301);
    }
    
    public function actionFaqDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/faq/details/', 'id' => $id), true, 301);
    }

    public function actionFaqEdit($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/faq/edit/', 'id' => $id), true, 301);
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

    public function actionKeyDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key/details/', 'id' => $id), true, 301);
    }    

    public function actionKeyRequestDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key-request/details/', 'id' => $id), true, 301);
    }

    public function actionKeyRequests()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key-request/'), true, 301);
    }

    public function actionKeys()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('//key/all/'), true, 301);
    }

    public function actionResetKey($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key/reset/', 'id' => $id), true, 301);
    }

    public function actionRevokeKey($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/key/delete/', 'id' => $id), true, 301);
    }
    
    public function actionUsers()
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/user/'), true, 301);
    }
    
    public function actionUserDetails($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/user/details/', 'id' => $id), true, 301);
    }
    
    public function actionUserEdit($id)
    {
        // Send a permanent redirect to the new version of this page.
        $this->redirect(array('/user/edit/', 'id' => $id), true, 301);
    }
}
