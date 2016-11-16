<?php
namespace Sil\DevPortal\controllers;

class EventController extends \Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionIndex()
    {
        $eventDataProvider = new \CActiveDataProvider('\Sil\DevPortal\models\Event', array(
            'sort' => array(
                'defaultOrder' => array(
                    'created' => \CSort::SORT_DESC,
                ),                
            ),
        ));
        
        $this->render('index', array(
            'eventDataProvider' => $eventDataProvider,
        ));
    }
}
