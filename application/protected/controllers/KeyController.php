<?php

class KeyController extends Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionActive()
    {
        // Get the list of all active Keys.
        $activeKeysDataProvider = \Key::getActiveKeysDataProvider();
        
        // Render the page.
        $this->render('active', array(
            'activeKeysDataProvider' => $activeKeysDataProvider,
        ));
    }
    
    public function actionDelete($id)
    {
        // Get a reference to the current website user's User model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Try to retrieve the specified Key's data.
        /* @var $key Key */
        $key = \Key::model()->findByPk($id);
        
        // If this is not a Key that the current User is allowed to delete,
        // say so.
        if ( ! $currentUser->canDeleteKey($key)) {
            throw new CHttpException(
                403,
                'That is not a key that you have permission to delete.'
            );
        }
        
        // If the form has been submitted (POSTed)...
        if (Yii::app()->request->isPostRequest) {
            
            // If unable to delete that Key...
            if ( ! $key->delete()) {
                
                // Record that in the log.
                Yii::log(
                    'Key deletion FAILED: ID ' . $key->key_id,
                    CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> Unable to delete key: <pre>'
                    . print_r($key->getErrors(), true) . '</pre>'
                );
            }
            // Otherwise...
            else {
                
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
            }
            
            // Send the user back to the list of Keys.
            $this->redirect(array('/key/'));
        }
        
        // Show the page.
        $this->render('delete', array(
            'key' => $key,
            'currentUser' => $currentUser,
        ));
    }

    public function actionDetails($id)
    {
        // Get a reference to the current website user's User model.
        /* @var $currentUser \User */
        $currentUser = \Yii::app()->user->user;
        
        // Try to retrieve the specified Key's data.
        /* @var $key \Key */
        $key = \Key::model()->findByPk($id);
        
        // Prevent access by users without permission to see this key.
        if (( ! $key) || ( ! $key->isVisibleToUser($currentUser))) {
            throw new CHttpException(
                404,
                'Either there is no key with an ID of ' . $id . ' or you do '
                . 'not have permission to view it.'
            );
        }
        
        if (Yii::app()->request->isPostRequest &&
            ($key->status == \Key::STATUS_PENDING)) {
            
            // If the User does NOT have permission to process requests
            // for keys to the corresponding API, say so.
            if ( ! $currentUser->hasAdminPrivilegesForApi($key->api)) {
                throw new CHttpException(
                    403,
                    'You do not have permission to manage this API.'
                );
            }
            
            // If the request was approved...
            if (isset($_POST[\Key::STATUS_APPROVED])) {
                
                // Try to approve the key.
                if ($key->approve($currentUser)) {
                    
                    // Update our local copy of this Key's data.
                    $key->refresh();
                    
                    Yii::app()->user->setFlash(
                        'success', 
                        '<strong>Success!</strong> Key granted.'
                    );
                    
                } else {
                    Yii::app()->user->setFlash(
                        'error', 
                        '<strong>Error!</strong> We were unable to approve '
                        . 'that key: <pre>'
                        . \CHtml::encode(print_r($key->getErrors(), true)) . '</pre>'
                    );
                }
                
                $this->redirect(array(
                    '/key/details/',
                    'id' => $key->key_id
                ));  
            }
            // Otherwise (i.e. - it was denied)...
            else {

                if ($key->deny($currentUser)) {
                    
                    // Update our local copy of this Key's data.
                    $key->refresh();
                    
                    Yii::app()->user->setFlash(
                        'success', 
                        '<strong>Success!</strong> Key denied.'
                    );
                } else {
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
        
        // Get the list of action links that should be shown.
        $actionLinks = LinksManager::getKeyDetailsActionLinksForUser(
            $key,
            $currentUser
        );
        
        // Render the page.
        $this->render('details', array(
            'actionLinks' => $actionLinks,
            'currentUser' => $currentUser,
            'key' => $key,
        ));
    }

    public function actionIndex()
    {
        // Get a reference to the current website user's User model.
        $currentUser = \Yii::app()->user->user;
        
        // If the user is an admin, redirect them to the list of active keys.
        // Otherwise redirect them to the list of their keys.
        if ($currentUser->role === \User::ROLE_ADMIN) {
            $this->redirect(array('/key/active'));
        } else {
            $this->redirect(array('/key/mine'));
        }
    }

    public function actionPending()
    {
        // Be extra certain that only admins can see this page.
        $currentUser = \Yii::app()->user->user;
        if ( ! $currentUser->isAdmin()) {
            throw new CHttpException(
                403,
                'You are not authorized to perform this action.',
                1468250367
            );
        }
        
        // Get the list of all pending Keys.
        $pendingKeysDataProvider = \Key::getPendingKeysDataProvider();
        
        // Render the page.
        $this->render('pending', array(
            'pendingKeysDataProvider' => $pendingKeysDataProvider,
        ));
    }
    
    public function actionReset($id)
    {
        // Get a reference to the current website user's User model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Try to retrieve the specified Key's data.
        /* @var $key Key */
        $key = \Key::model()->findByPk($id);
        
        // If this is not a Key that the current User is allowed to reset, say
        // so.
        if ( ! $currentUser->canResetKey($key)) {
            throw new CHttpException(
                403,
                'That is not a Key that you have permission to reset.'
            );
        }

        // If the form has been submitted (POSTed)...
        if (Yii::app()->request->isPostRequest) {
            
            // Reset the key, paying attention to the results.
            $resetResults = Key::resetKey($key->key_id);
            
            // If we were unable to reset that Key...
            if ( ! $resetResults[0]) {
                
                // Record that in the log.
                Yii::log(
                    'Key reset FAILED: ID ' . $key->key_id,
                    CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> Unable to reset key: <pre>'
                    . $resetResults[1] . '</pre>'
                );
            }
            // Otherwise...
            else {
                
                // Record that in the log.
                Yii::log(
                    'Key reset: ID ' . $key->key_id,
                    CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> Key reset.'
                );
            }
            
            // Send the user back to the Key details.
            $this->redirect(array(
                '/key/details/',
                'id' => $key->key_id
            ));
        }
        
        // Show the page.
        $this->render('reset', array(
            'currentUser' => $currentUser,
            'key' => $key,
        ));
    }
    
    public function actionMine()
    {
        // Get the current user's model.
        $currentUser = \Yii::app()->user->user;
        
        // Get lists of the user's active and inactive keys (as data providers
        // for the view).
        $activeKeys = array();
        $nonActiveKeys = array();
        foreach ($currentUser->keys as $key) {
            if ($key->status === \Key::STATUS_APPROVED) {
                $activeKeys[] = $key;
            } else {
                $nonActiveKeys[] = $key;
            }
        }
        $activeKeysDataProvider = new CArrayDataProvider($activeKeys, array(
            'keyField' => 'key_id',
        ));
        $nonActiveKeysDataProvider = new CArrayDataProvider($nonActiveKeys, array(
            'keyField' => 'key_id',
        ));

        // Render the page.
        $this->render('mine', array(
            'activeKeysDataProvider' => $activeKeysDataProvider,
            'nonActiveKeysDataProvider' => $nonActiveKeysDataProvider,
        ));
    }
    
    public function actionRevoke($id)
    {
        // Get a reference to the current website user's User model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Try to retrieve the specified Key's data.
        /* @var $key Key */
        $key = \Key::model()->findByPk($id);
        
        if ( ! $currentUser->canRevokeKey($key)) {
            
            if ($key && $key->isOwnedBy($currentUser)) {
                $this->redirect(array(
                    '/key/delete',
                    'id' => $key->key_id,
                ));
            }
            
            throw new CHttpException(
                403,
                'That is not a Key that you have permission to revoke.'
            );
        }
        
        // If the form has been submitted (POSTed)...
        if (Yii::app()->request->isPostRequest) {
            
            // Revoke the key, paying attention to the results.
            $revokeResults = Key::revokeKey($key->key_id, $currentUser);
            
            // If we were unable to delete that Key...
            if ( ! $revokeResults[0]) {
                
                // Record that in the log.
                Yii::log(
                    'Key revokation FAILED: ID ' . $key->key_id,
                    CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> Unable to revoke key: <pre>'
                    . $revokeResults[1] . '</pre>'
                );
            }
            // Otherwise...
            else {
                
                // Record that in the log.
                Yii::log(
                    'Key revoked: ID ' . $key->key_id,
                    CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );
                
                // Tell the user.
                Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> Key revoked.'
                );
            }
            
            // Send the user back to the list of Keys.
            $this->redirect(array('/key/'));
        }
        
        // Show the page.
        $this->render('revoke', array(
            'currentUser' => $currentUser,
            'key' => $key,
        ));
    }
}
