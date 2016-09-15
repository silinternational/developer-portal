<?php
namespace Sil\DevPortal\controllers;

use Sil\DevPortal\models\SiteText;

class SiteTextController extends \Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionDetails($id)
    {
        // Get the SiteText whose ID is specified in the URL. Expects the pk of a
        // SiteText as 'id'.
        $siteText = $this->getPkOr404('\Sil\DevPortal\models\SiteText');
        
        // Set this page to use a different layout.
        $this->layout = 'column1';

        // Show the SiteText's details.
        $this->render('details', array(
            'siteText' => $siteText,
        ));
    }
    
    public function actionEdit($id)
    {
        // Get the SiteText whose ID is specified in the URL. Expects the pk of a
        // SiteText as 'id'.
        $siteText = $this->getPkOr404('\Sil\DevPortal\models\SiteText');
        
        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.siteTextForm', $siteText);
        
        // Collect the user input data (if any).
        $postData = \Yii::app()->request->getPost('SiteText', false);
        
        // If form has been submitted (as evidenced by the presence of POSTed
        // user input data)...
        if ($postData !== false) {
            
            // Do a massive assignment of the POSTed data to the model (which
            // is safe because Yii only uses attributes marked as safe for such
            // an operation).
            $siteText->attributes = $postData;

            // Attempt to save the changes to the SiteText (validating the user
            // input). If successful...
            if ($siteText->save()) {

                // Record that in the log.
                \Yii::log(
                    'SiteText updated, ID ' . $siteText->site_text_id,
                    \CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Send the user back to the SiteText details page.
                $this->redirect(array('/site-text/details/', 'id' => $siteText->site_text_id));
            }
        }
        
        // If we reach this point, render the page.
        $this->render('edit', array(
            'form' => $form,
        ));
    }
    
    public function actionIndex()
    {
        $siteTexts = SiteText::model()->findAll(array(
            'order' => '`name` ASC, `site_text_id` ASC',
        ));
        
        $this->render('index', array(
            'siteTexts' => $siteTexts,
        ));
    }
}
