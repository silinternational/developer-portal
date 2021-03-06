<?php
namespace Sil\DevPortal\controllers;

use Sil\DevPortal\models\User;

class UserController extends \Controller
{
    public $layout = '//layouts/one-column-with-title';

    public function actionDetails($id)
    {
        $user = $this->getPkOr404('\Sil\DevPortal\models\User');

        $apisDataProvider = new \CActiveDataProvider('\Sil\DevPortal\models\Api', array(
            'criteria' => array(
                'condition' => 'owner_id = :owner_id',
                'params' => array(':owner_id' => $id),
            )
        ));

        $keysDataProvider = new \CActiveDataProvider('\Sil\DevPortal\models\Key', array(
            'criteria' => array(
                'condition' => 'user_id = :user_id',
                'params' => array(':user_id' => $id),
            )
        ));

        $this->render('details', array(
            'apisDataProvider' => $apisDataProvider,
            'keysDataProvider' => $keysDataProvider,
            'user' => $user,
        ));
    }

    public function actionEdit($id)
    {
        /* @var $user User */
        $user = $this->getPkOr404('\Sil\DevPortal\models\User');

        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.userForm', $user);

        // If the form was submitted and passes validation...
        if ($form->submitted('yt0') && $form->validate()) {

            // Attempt to save the changes to the User (skipping validation,
            // since the data has already been validated). If successful...
            if ($user->save(false)) {

                // Record that in the log.
                \Yii::log(
                    'User updated: ID ' . $user->user_id,
                    \CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                \Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> User updated successfully.'
                );

                // Send the user back to the User details page.
                $this->redirect(array(
                    '/user/details/',
                    'id' => $user->user_id,
                ));
            }
            // Otherwise...
            else {

                // Record that in the log.
                \Yii::log(
                    'User update FAILED: ID ' . $user->user_id,
                    \CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                \Yii::app()->user->setFlash(
                    'error',
                    sprintf(
                        '<strong>%s</strong> %s: <pre>%s</pre>',
                        'Error!',
                        'We were unable to save your changes to the User',
                        $user->getErrorsAsFlatHtmlList()
                    )
                );
            }
        }

        // If we reach this point, render the page.
        $this->render('edit', array(
            'form' => $form,
        ));
    }

    public function actionIndex()
    {
        $usersDataProvider = new \CActiveDataProvider('\Sil\DevPortal\models\User', array(
            'criteria' => array(
                'with' => 'approvedKeyCount'
            )
        ));

        $this->render('index', array(
            'usersDataProvider' => $usersDataProvider,
        ));
    }
}
