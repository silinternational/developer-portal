<?php
namespace Sil\DevPortal\controllers;

class FaqController extends \Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionAdd()
    {
        // Create a new instance of the model.
        $faq = new \Faq;
        
        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.faqForm', $faq);
        
        // Collect the user input data (if any).
        $postData = \Yii::app()->request->getPost('Faq', false);
        
        // If form has been submitted (as evidenced by the presence of POSTed
        // user input data)...
        if ($postData !== false) {
            
            // Do a massive assignment of the POSTed data to the model (which
            // is safe because Yii only uses attributes marked as safe for such
            // an operation).
            $faq->attributes = $postData;

            // Attempt to save the changes to the FAQ (validating the user
            // input). If successful...
            if ($faq->save()) {

                // Record that in the log.
                \Yii::log(
                    'FAQ created, ID ' . $faq->faq_id,
                    \CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Send the user back to the FAQ details page.
                $this->redirect(array(
                    '/faq/details/',
                    'id' => $faq->faq_id,
                ));
            }
        }
        
        // If we reach this point, render the page.
        $this->render('add', array(
            'form' => $form,
        ));
    }
    
    public function actionDetails($id)
    {
        // Get the FAQ whose ID is specified in the URL. Expects the pk of an
        // Faq as 'id'.
        $faq = $this->getPkOr404('Faq');
        
        // Set this page to use a different layout.
        $this->layout = 'column1';

        // Show the FAQ's details.
        $this->render('details', array(
            'faq' => $faq,
        ));
    }
    
    public function actionEdit($id)
    {
        // Get the FAQ whose ID is specified in the URL. Expects the pk of an
        // Faq as 'id'.
        $faq = $this->getPkOr404('Faq');
        
        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.faqForm', $faq);
        
        // Collect the user input data (if any).
        $postData = \Yii::app()->request->getPost('Faq', false);
        
        // If form has been submitted (as evidenced by the presence of POSTed
        // user input data)...
        if ($postData !== false) {
            
            // Do a massive assignment of the POSTed data to the model (which
            // is safe because Yii only uses attributes marked as safe for such
            // an operation).
            $faq->attributes = $postData;

            // Attempt to save the changes to the FAQ (validating the user
            // input). If successful...
            if ($faq->save()) {

                // Record that in the log.
                \Yii::log(
                    'FAQ updated, ID ' . $faq->faq_id,
                    \CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Send the user back to the FAQ details page.
                $this->redirect(array('/faq/details/', 'id' => $faq->faq_id));
            }
        }
        
        // If we reach this point, render the page.
        $this->render('edit', array(
            'form' => $form,
        ));
    }
    
    public function actionIndex()
    {
        $faqs = \Faq::model()->findAll(array(
            'order' => '`order` ASC, `faq_id` ASC',
        ));
        
        $this->render('index', array(
            'faqs' => $faqs,
        ));
    }
}
