<?php

class KeyRequestController extends Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionDelete($id)
    {
        // Get a reference to the current website user's User model.
        /* @var $user User */
        $user = \Yii::app()->user->user;
        
        // Try to retrieve the specified KeyRequest's data.
        /* @var $keyRequest KeyRequest */
        $keyRequest = \KeyRequest::model()->findByPk($id);
        
        // If this is not a KeyRequest that the current User is allowed to
        // delete, say so.
        if ( ! $user->canDeleteKeyRequest($keyRequest)) {
            throw new CHttpException(
                403,
                'That is not a key request that you have permission to delete.'
            );
        }
        
        // If the Key Request has already been approved (and the resulting Key
        // still exists)...
        if (($keyRequest->status === KeyRequest::STATUS_APPROVED) &&
            ($keyRequest->key instanceof Key)) {
            
            // Don't let them delete the Key Request.
            Yii::app()->user->setFlash(
                'error',
                sprintf(
                    '<strong>Error!</strong> You are not allowed to delete an '
                    . 'approved key request when the resulting key still exists. '
                    . 'Try <a href="%s">deleting the key</a> first, then (if '
                    . 'necessary) you can delete this key request.',
                    $this->createUrl('/key/details/', array(
                        'id' => $keyRequest->key->key_id,
                    ))
                )
            );
            
            // Send them back to the Key Request details page.
            $this->redirect(array(
                '/key-request/details/',
                'id' => $keyRequest->key_request_id,
            ));
        }
        
        // If the form has been submitted (POSTed)...
        if (Yii::app()->request->isPostRequest) {
            
            // Try to delete the KeyRequest. If successful...
            if ($keyRequest->delete()) {

                // Send a notification that the key request was deleted
                // (if applicable).
                $keyRequest->sendKeyRequestDeletionNotification();
                
                // Record that in the log.
                Yii::log(
                    'KeyRequest deleted: ID ' . $keyRequest->key_request_id,
                    CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> Key request deleted.'
                );
                
            } else {

                // If we failed to delete the key request, record that in the
                // log.
                Yii::log(
                    'Key Request deletion FAILED: ID '
                    . $keyRequest->key_request_id,
                    CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> We were unable to delete that key '
                    . 'request. It may have already been deleted.'
                );
            }
            
            // Send the user back to the list of Keys that they most likely
            // came from.
            $this->redirect(array('/key/mine/'));
        }
        
        // Show the page.
        $this->render('delete', array(
            'keyRequest' => $keyRequest,
        ));
    }

    public function actionDetails($id)
    {
        // Get the current user's model.
        /* @var $user User */
        $user = \Yii::app()->user->user;
        
        // Try to get the specified Key Request.
        /* @var $keyRequest KeyRequest */
        $keyRequest = KeyRequest::model()->findByPk($id);
        
        // If the User does NOT have permission to see that KeyRequest, say so.
        if ( ! $user->canSeeKeyRequest($keyRequest)) {
            throw new CHttpException(
                403,
                'That is not a Key Request that you have permission to see.'
            );
        }
        
        // If the key request is still pending...
        if ($keyRequest->status == KeyRequest::STATUS_PENDING) {

            // If the form has been submitted (i.e. - POSTed)...
            if (Yii::app()->request->isPostRequest) {

                // If the User does NOT have permission to process requests
                // for keys to the corresponding API, say so.
                if ( ! $user->hasAdminPrivilegesForApi($keyRequest->api)) {
                    throw new CHttpException(
                        403,
                        'You do not have permission to manage this API.'
                    );
                }

                // Record that the current user is the one that processed this
                // key request.
                $keyRequest->processed_by = Yii::app()->user->user->user_id;     

                // If the request was approved...
                if (isset($_POST[KeyRequest::STATUS_APPROVED])) {
                    
                    // Grant the key.
                    $createResults = Key::createKey(
                        $keyRequest->api->api_id,
                        $keyRequest->user->user_id,
                        $keyRequest->key_request_id
                    );
                    
                    // If successful...
                    if ($createResults[0] === true) {

                        // Record that the request was approved.
                        $keyRequest->status = KeyRequest::STATUS_APPROVED;
                        
                        // Try to save those changes. If NOT successful...
                        if ( ! $keyRequest->save()) {
                            
                            // Say so.
                            Yii::app()->user->setFlash(
                                'warning',
                                '<strong>Warning!</strong> We successfully '
                                . 'created that key, but were unable to mark '
                                . 'that key request as having been approved.'
                            );
                        }

                        // Redirect the user to the details page for that key.
                        $this->redirect(array(
                            '/key/details/',
                            'id' => $createResults[1]->key_id
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
                    $keyRequest->status = KeyRequest::STATUS_DENIED;
                        
                    // Try to save those changes. If NOT successful...
                    if ( ! $keyRequest->save()) {

                        // Say so.
                        Yii::app()->user->setFlash(
                            'error',
                            '<strong>Error!</strong> We were unable to mark '
                            . 'that key request as having been denied: <pre>'
                            . print_r($keyRequest->getErrors(), true) . '</pre>'
                        );
                    }

                    // Send the user to the details page for this Key Request.
                    $this->redirect(array(
                        '/key-request/details/',
                        'id' => $keyRequest->key_request_id
                    ));
                }
            }
        }
        
        // Get the list of action links that should be shown.
        $actionLinks = LinksManager::getKeyRequestDetailsActionLinksForUser(
            $keyRequest,
            $user
        );
        
        // Render the page.
        $this->render('details', array(
            'actionLinks' => $actionLinks,
            'keyRequest' => $keyRequest,
        ));
    }
    
    public function actionIndex()
    {
        // Get the list of all pending KeyRequests.
        $allPendingKeyRequests = \KeyRequest::model()->findAllByAttributes(array(
            'status' => \KeyRequest::STATUS_PENDING,
        ));
        
        // Get the current user's model.
        /* @var $user User */
        $user = \Yii::app()->user->user;
        
        // Exclude those that the user is not allowed to see.
        $keyRequestsToShow = array();
        foreach ($allPendingKeyRequests as $keyRequest) {
            if ($user->canSeeKeyRequest($keyRequest)) {
                $keyRequestsToShow[] = $keyRequest;
            }
        }
        
        // Create a data provider for showing those in a gridview.
        $keyRequestDataProvider = new CArrayDataProvider(
            $keyRequestsToShow,
            array(
                'keyField' => 'key_request_id',
            )
        );
        
        $this->render('index', array(
            'keyRequestDataProvider' => $keyRequestDataProvider,
        ));
    }
    
    public function actionMine()
    {
        
        
        
    }
}
