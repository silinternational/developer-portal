<?php
namespace Sil\DevPortal\controllers;

use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\Key;
use Sil\DevPortal\models\User;

class SystemController extends \Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionResyncApiaxle()
    {
        // If the form has been submitted (POSTed)...
        if (\Yii::app()->request->isPostRequest) {
            
            $numApis = (int)Api::model()->count();
            if ($numApis === 0) {
                \Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> There are no APIs to resync to ApiAxle.'
                );
                $this->redirect(['/system/resync-apiaxle/']);
            }
            
            $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
            $apiResyncErrors = Api::repopulateApiAxle($apiAxle);
            $keyResyncErrors = Key::repopulateApiAxle($apiAxle);
            
            if ( ! empty($apiResyncErrors)) {
                \Yii::log(sprintf(
                    'Resyncing APIs to ApiAxle had errors: %s',
                    var_export($apiResyncErrors, true)
                ), \CLogger::LEVEL_ERROR, __CLASS__ . '.' . __FUNCTION__);
            }
            
            if ( ! empty($keyResyncErrors)) {
                \Yii::log(sprintf(
                    'Resyncing keys to ApiAxle had errors: %s',
                    var_export($keyResyncErrors, true)
                ), \CLogger::LEVEL_ERROR, __CLASS__ . '.' . __FUNCTION__);
            }
            
            if (empty($apiResyncErrors) && empty($keyResyncErrors)) {
                \Yii::log(
                    'Successfully resynced APIs and keys ApiAxle',
                    \CLogger::LEVEL_WARNING,
                    __CLASS__ . '.' . __FUNCTION__
                );
                
                \Yii::app()->user->setFlash('success', sprintf(
                    '<strong>Success!</strong> APIs and keys resynced to '
                    . 'ApiAxle. Check the <a href="%s">Event Log</a> to see if '
                    . 'anything had to be re-added to ApiAxle.',
                    \CHtml::encode($this->createUrl('event/'))
                ));
            } else {
                \Yii::app()->user->setFlash('error', sprintf(
                    "<strong>Error!</strong> There were problems while "
                    . "resyncing APIs and/or keys to ApiAxle.\n"
                    . "APIs:\n%s\n"
                    . "keys:\n%s",
                    \CHtml::encode(var_export($apiResyncErrors, true)),
                    \CHtml::encode(var_export($keyResyncErrors, true))
                ));
            }
            
            $this->redirect(['/system/resync-apiaxle/']);
        }
        
        // Show the page.
        $this->render('resync-apiaxle');
    }

    public function actionIndex()
    {
        $this->redirect(['/system/resync-apiaxle/']);
    }
}
