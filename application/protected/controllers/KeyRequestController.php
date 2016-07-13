<?php

class KeyRequestController extends Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionDelete($id)
    {
        // Get a reference to the current website user's User model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Try to retrieve the specified Key's data.
        /* @var $key Key */
        $key = \Key::model()->findByPk($id);
        
        // If this is not a Key that the current User is allowed to
        // delete, say so.
        if ( ! $currentUser->canDeleteKey($key)) {
            throw new CHttpException(
                403,
                'That is not a key that you have permission to delete.'
            );
        }
        
        // If the Key has already been approved...
        if ($key->status === \Key::STATUS_APPROVED) {
            
            // Don't let them delete the Key.
            Yii::app()->user->setFlash(
                'error',
                sprintf(
                    '<strong>Error!</strong> You are not allowed to delete an '
                    . 'approved key. Try <a href="%s">revoking the key</a> '
                    . 'first, then (if necessary) you can delete this key.',
                    $this->createUrl('/key/details/', array(
                        'id' => $key->key_id,
                    ))
                )
            );
            
            // Send them back to the Key details page.
            $this->redirect(array(
                '/key/details/',
                'id' => $key->key_id,
            ));
        }
        
        // If the form has been submitted (POSTed)...
        if (Yii::app()->request->isPostRequest) {
            
            // Try to delete the Key. If successful...
            if ($key->delete()) {

                // Send a notification that the key was deleted
                // (if applicable).
                $key->sendKeyDeletionNotification();
                
                // Record that in the log.
                Yii::log(
                    'Key deleted: ID ' . $key->key_id,
                    CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> Key deleted.'
                );
                
            } else {

                // If we failed to delete the key, record that in the
                // log.
                Yii::log(
                    'Key deletion FAILED: ID '
                    . $key->key_id,
                    CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> We were unable to delete that '
                    . 'key. It may have already been deleted.'
                );
            }
            
            // Send the user back to the list of Keys that they most likely
            // came from.
            $this->redirect(array('/key/mine/'));
        }
        
        // Show the page.
        $this->render('delete', array(
            'key' => $key,
        ));
    }

    public function actionDetails($id)
    {
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Try to get the specified Key.
        /* @var $key \Key */
        $key = \Key::model()->findByPk($id);
        
        // If the User does NOT have permission to see that Key, say so.
        if ( ! $currentUser->canSeeKey($key)) {
            throw new CHttpException(
                403,
                'That is not a Key that you have permission to see.'
            );
        }
        
        // If the key is still pending...
        if ($key->status == \Key::STATUS_PENDING) {

            // If the form has been submitted (i.e. - POSTed)...
            if (Yii::app()->request->isPostRequest) {

                // If the User does NOT have permission to process requests
                // for keys to the corresponding API, say so.
                if ( ! $currentUser->hasAdminPrivilegesForApi($key->api)) {
                    throw new CHttpException(
                        403,
                        'You do not have permission to manage this API.'
                    );
                }

                // Record that the current user is the one that processed this
                // key.
                $key->processed_by = Yii::app()->user->user->user_id;     

                // If the request was approved...
                if (isset($_POST[\Key::STATUS_APPROVED])) {
                    
                    // Try to approve the key.
                    if ($key->approve(\Yii::app()->user->user)) {

                        // Redirect the user to the details page for that key.
                        $key->refresh();
                        $this->redirect(array(
                            '/key/details/',
                            'id' => $key->key_id
                        ));  
                    }
                    // Otherwise...
                    else {

                        // Say so.
                        Yii::app()->user->setFlash(
                            'error', 
                            '<strong>Error!</strong> We were unable to create '
                            . 'that key: <pre>'
                            . CHtml::encode($createResults[1]) . '</pre>'
                        );
                    }
                }
                // Otherwise (i.e. - it was denied)...
                else {
                    
                    // Record that fact.
                    $key->status = \Key::STATUS_DENIED;
                        
                    // Try to save those changes. If NOT successful...
                    if ( ! $key->save()) {

                        // Say so.
                        Yii::app()->user->setFlash(
                            'error',
                            '<strong>Error!</strong> We were unable to mark '
                            . 'that key as having been denied: <pre>'
                            . print_r($key->getErrors(), true) . '</pre>'
                        );
                    }

                    // Send the user to the details page for this Key.
                    $this->redirect(array(
                        '/key/details/',
                        'id' => $key->key_id
                    ));
                }
            }
        }
        
        // Get the list of action links that should be shown.
        $actionLinks = LinksManager::getKeyDetailsActionLinksForUser(
            $key,
            $currentUser
        );
        
        // Render the page.
        $this->render('details', array(
            'actionLinks' => $actionLinks,
            'key' => $key,
        ));
    }
    
    public function actionIndex()
    {
        // Get the list of all pending Keys.
        $allPendingKeys = \Key::model()->findAllByAttributes(array(
            'status' => \Key::STATUS_PENDING,
        ));
        
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Exclude those that the user is not allowed to see.
        $keysToShow = array();
        foreach ($allPendingKeys as $key) {
            if ($currentUser->canSeeKey($key)) {
                $keysToShow[] = $key;
            }
        }
        
        // Create a data provider for showing those in a gridview.
        $keyDataProvider = new CArrayDataProvider(
            $keysToShow,
            array(
                'keyField' => 'key_id',
            )
        );
        
        $this->render('index', array(
            'keyDataProvider' => $keyDataProvider,
        ));
    }
    
    public function actionMine()
    {
        
        
        
    }
}
