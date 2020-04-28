<?php
namespace Sil\DevPortal\controllers;

use Sil\DevPortal\components\Http\ClientG5 as Client;
use Sil\DevPortal\components\Http\ParamsCollection;
use Sil\DevPortal\components\Http\Response;
use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\ApiVisibilityDomain;
use Sil\DevPortal\models\ApiVisibilityUser;
use Sil\DevPortal\models\Key;
use Sil\DevPortal\models\User;
use Stringy\StaticStringy as SS;

class ApiController extends \Controller
{
    public $layout = '//layouts/one-column-with-title';
    
    public function actionActiveKeys($code)
    {
        // Make sure the specified API exists.
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // Get a reference to the current website user's User model.
        $currentUser = \Yii::app()->user->user;
        
        // Prevent information about it from being seen by user's without
        // permission to see the specified API.
        if (( ! $api) || ( ! $api->isVisibleToUser($currentUser))) {
            throw new \CHttpException(
                404,
                'Either there is no "' . $code . '" API or you do not have '
                . 'permission to view it.'
            );
        }
        
        // Prevent this list of keys from being seen by anyone who is not
        // authorized to see them.
        if (( ! ($currentUser instanceof User)) || ( ! $currentUser->canSeeKeysForApi($api))) {
            throw new \CHttpException(
                403,
                'You do not have permission to see its list of active keys for '
                . 'the "' . $api->display_name . '" API.'
            );
        }
        
        // Get the list of active keys for that API.
        $activeKeys = array();
        foreach ($api->keys as $key) {
            if ($key->isApproved()) {
                $activeKeys[] = $key;
            }
        }
        $activeKeysDataProvider = new \CArrayDataProvider($activeKeys, array(
            'keyField' => 'key_id',
        ));
        
        // Show the page.
        $this->render('activeKeys', array(
            'activeKeysDataProvider' => $activeKeysDataProvider,
            'api' => $api,
        ));
    }
    
    public function actionAdd()
    {
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Set up to add an API.
        /* @var $api Api */
        $api = new Api;
        
        // Record the current user as the owner.
        $api->owner_id = $currentUser->user_id;

        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.apiForm', $api);

        // If the form was submitted...
        if ($form->submitted('yt0')) {

            // If the user making this change is NOT an admin...
            if ($currentUser->role !== User::ROLE_ADMIN) {

                // Make sure they are still set as the owner.
                $api->owner_id = $currentUser->user_id;
            }
            
            // If the data passes validation...
            if ($form->validate()) {

                // Attempt to save the changes to the API (skipping validation,
                // since the data has already been validated). If successful...
                if ($api->save(false)) {

                    // Record that in the log.
                    \Yii::log('API created: code "' . $api->code . '", ID ' . 
                             $api->api_id,
                            \CLogger::LEVEL_INFO,
                            __CLASS__ . '.' . __FUNCTION__);

                    // Send the user to the details page for the new Api.
                    $this->redirect(array(
                        '/api/details/',
                        'code' => $api->code
                    ));
                }
                // Otherwise...
                else {

                    // Record that in the log.
                    \Yii::log(
                        'API creation FAILED: code "' . $api->code . '"',
                        \CLogger::LEVEL_ERROR,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Tell the user.
                    \Yii::app()->user->setFlash(
                        'error',
                        '<strong>Error!</strong> We were unable to create that '
                        . 'API: ' . $api->getErrorsAsFlatHtmlList()
                    );
                }
            }
        }

        // If we reach this point, render the page.
        $this->render('add', array('form' => $form));
    }

    public function actionCancelDomainInvitation($id)
    {
        /* @var $apiVisibilityDomain ApiVisibilityDomain */
        $apiVisibilityDomain = ApiVisibilityDomain::model()->findByPk($id);
        $api = (is_null($apiVisibilityDomain) ? null : $apiVisibilityDomain->api);
        
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an invitation to an API that you have permission to manage.'
            );
        }
        
        if ($apiVisibilityDomain === null) {
            throw new \CHttpException(404, 'We could not find that invitation.');
        }
        
        $hasDependentKey = $apiVisibilityDomain->hasDependentKey();
        if ($hasDependentKey) {
            
            \Yii::app()->user->setFlash('error', sprintf(
                '<b>Oops!</b> Before you can uninvite "%s" users, you must '
                . 'first revoke/deny the following keys, which depend on '
                . 'this invitation: %s',
                $apiVisibilityDomain->domain,
                $apiVisibilityDomain->getLinksToDependentKeysAsHtmlList()
            ));
            
        } elseif (\Yii::app()->request->isPostRequest) {
            
            if ( ! $apiVisibilityDomain->delete()) {
                
                \Yii::log(
                    'ApiVisibilityDomain deletion FAILED: ID ' . $id,
                    \CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                \Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> Unable to withdraw invitation: '
                    . $apiVisibilityDomain->getErrorsAsFlatHtmlList()
                );
            } else {
                \Yii::log(
                    'ApiVisibilityDomain deleted: ID ' . $id,
                    \CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                \Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> Invitation withdrawn.'
                );
            }
            
            $this->redirect(array(
                '/api/invited-domains',
                'code' => $api->code,
            ));
        }
        
        // Show the page.
        $this->render('uninvite-domain', array(
            'api' => $api,
            'apiVisibilityDomain' => $apiVisibilityDomain,
            'currentUser' => $currentUser,
            'hasDependentKey' => $hasDependentKey,
        ));
    }

    public function actionCancelUserInvitation($id)
    {
        /* @var $apiVisibilityUser ApiVisibilityUser */
        $apiVisibilityUser = ApiVisibilityUser::model()->findByPk($id);
        $api = (is_null($apiVisibilityUser) ? null : $apiVisibilityUser->api);
        
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an invitation to an API that you have permission to manage.'
            );
        }
        
        if ($apiVisibilityUser && $apiVisibilityUser->invitedUser) {
            $invitedUser = $apiVisibilityUser->invitedUser;
        } else {
            $invitedUser = null;
        }
        
        $hasDependentKey = $apiVisibilityUser->hasDependentKey();
        if ($hasDependentKey) {
            
            \Yii::app()->user->setFlash('error', sprintf(
                '<b>Oops!</b> Before you can uninvite %s, you must first '
                . 'first revoke/deny the following keys, which depend on '
                . 'this invitation: %s',
                $invitedUser->getDisplayName(),
                $apiVisibilityUser->getLinksToDependentKeysAsHtmlList()
            ));
            
        } elseif (\Yii::app()->request->isPostRequest) {
            
            if ( ! $apiVisibilityUser->delete()) {
                
                \Yii::log(
                    'ApiVisibilityUser deletion FAILED: ID ' . $id,
                    \CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                \Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> Unable to withdraw invitation: '
                    . $apiVisibilityUser->getErrorsAsFlatHtmlList()
                );
            } else {
                \Yii::log(
                    'ApiVisibilityUser deleted: ID ' . $id,
                    \CLogger::LEVEL_INFO,
                    __CLASS__ . '.' . __FUNCTION__
                );

                \Yii::app()->user->setFlash(
                    'success',
                    '<strong>Success!</strong> Invitation withdrawn.'
                );
            }
            
            $this->redirect(array(
                '/api/invited-users',
                'code' => $api->code,
            ));
        }
        
        // Show the page.
        $this->render('uninvite-user', array(
            'api' => $api,
            'apiVisibilityUser' => $apiVisibilityUser,
            'currentUser' => $currentUser,
            'hasDependentKey' => $hasDependentKey,
        ));
    }

    public function actionDelete($code)
    {
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Get the API by the code name.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // If that is NOT an Api that the User has permission to manage, say so.
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an API that you have permission to manage.'
            );
        }
        
        // If the form has been submitted (POSTed)...
        if (\Yii::app()->request->isPostRequest) {
            
            try {
                
                // Try to delete the API. If successful...
                if ($api->delete()) {
                    
                    // Record that in the log.
                    \Yii::log(
                        'API deleted: ID ' . $api->api_id,
                        \CLogger::LEVEL_INFO,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Tell the user.
                    \Yii::app()->user->setFlash(
                        'success',
                        '<strong>Success!</strong> API deleted.'
                    );

                    // Send the user back to the list of APIs.
                    $this->redirect(array('/api/'));
                }
                // Otherwise...
                else {

                    // Record that in the log.
                    \Yii::log(
                        'API deletion FAILED: ID ' . $api->api_id,
                        \CLogger::LEVEL_ERROR,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Tell the user.
                    \Yii::app()->user->setFlash(
                        'error',
                        '<strong>Error!</strong> Unable to delete that API: '
                        . $api->getErrorsAsFlatHtmlList()
                    );

                    $this->redirect(array('/api/delete/',
                        'code' => $api->code,
                    ));
                }
            }
            catch (\CDbException $ex) {
                
                // Record that in the log.
                \Yii::log(
                    'API deletion FAILED: ID ' . $api->api_id . ', '
                    . 'CDbException thrown',
                    \CLogger::LEVEL_ERROR,
                    __CLASS__ . '.' . __FUNCTION__
                );

                // Tell the user.
                \Yii::app()->user->setFlash(
                    'error',
                    '<strong>Error!</strong> Unable to delete that API. It is '
                    . 'possible that the Keys and/or Key Requests were not '
                    . 'successfully deleted, preventing the API from being '
                    . 'deleted.'
                );
                
                \Yii::trace($ex->getMessage());
            }
        }
        
        // Get the list of all Keys to this API.
        $criteria = new \CDbCriteria;
        $criteria->compare('api_id', $api->api_id);
        $keyList = new \CActiveDataProvider('\Sil\DevPortal\models\Key',
            array('criteria' => $criteria)
        );
        
        // Show the page.
        $this->render('delete', array(
            'api'  => $api,
            'keyList' => $keyList,
        ));
    }

    public function actionDetails($code)
    {
        /* @var $webUser WebUser */
        $webUser = \Yii::app()->user;
        /* @var $currentUser User|null */
        $currentUser = $webUser->getUser();
        
        // Get the API by the code name.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // If no such Api was found 
        //    OR
        // if the Api isn't visible to the current user... say so.
        if (($api === null) || ( ! $api->isVisibleToUser($currentUser))) {
            if ($webUser->isGuest) {
                // Prompt guests to login (in case they have been invited to
                // see this API).
                \Yii::app()->user->setReturnUrl($this->createUrl(
                    'api/details',
                    ['code' => $code]
                ));
                $this->redirect(['auth/login']);
            }
            throw new \CHttpException(
                404,
                'Either there is no "' . $code . '" API or you do not have '
                . 'permission to view it.'
            );
        }
        
        // Get the list of action links that should be shown.
        $actionLinks = \LinksManager::getApiDetailsActionLinksForUser(
            $api,
            $currentUser
        );
        
        // Render the page.
        $this->render('details', array(
            'actionLinks' => $actionLinks,
            'api' => $api,
            'webUser' => $webUser,
        ));
    }

    public function actionDocsEdit($code)
    {
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Get the API by the code name.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // If that is NOT an Api that the User has permission to manage, say so.
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an API that you have permission to manage.'
            );
        }
        
        // Make a note of what the Api's current owner_id is (in case we need to
        // prevent changes to it).
        $apiOwnerId = $api->owner_id;
        
        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.apiDocsForm', $api);
        
        // If the form was submitted...
        if ($form->submitted('yt0')) {

            // If the user making this change is NOT an admin...
            if ($currentUser->role !== User::ROLE_ADMIN) {

                // Make sure they didn't change the owner_id.
                $api->owner_id = $apiOwnerId;
            }
            
            // If the data passes validation...
            if ($form->validate()) {

                // Attempt to save the changes to the API (skipping validation,
                // since the data has already been validated). If successful...
                if ($api->save(false)) {

                    // Record that in the log.
                    \Yii::log(
                        'API docs updated: ID ' . $api->api_id,
                        \CLogger::LEVEL_INFO,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Send the user back to the appropriate page.
                    $this->redirect(array('/api/details/',
                        'code' => $api->code,
                    ));
                }
                // Otherwise...
                else {

                    // Record that in the log.
                    \Yii::log(
                        'API docs update FAILED: ID ' . $api->api_id,
                        \CLogger::LEVEL_ERROR,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Tell the user.
                    \Yii::app()->user->setFlash(
                        'error',
                        '<strong>Error!</strong> We were unable to save your '
                        . 'changes to the API documentation: '
                        . $api->getErrorsAsFlatHtmlList()
                    );
                }
            }
        }
        
        // If we reach this point, render the page.
        $this->render('docsEdit', array(
            'form' => $form,
        ));
    }

    public function actionEdit($code)
    {
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Get the API by the code name.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // If that is NOT an Api that the User has permission to manage, say so.
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an API that you have permission to manage.'
            );
        }
        
        // Make a note of what the Api's current owner_id is (in case we need to
        // prevent changes to it).
        $apiOwnerId = $api->owner_id;
        
        // Get the form object.
        $form = new \YbHorizForm('application.views.forms.apiForm', $api);
        
        // If the form was submitted...
        if ($form->submitted('yt0')) {
            
            // If the user making this change is NOT an admin...
            if ($currentUser->role !== User::ROLE_ADMIN) {

                // Make sure they didn't change the owner_id.
                $api->owner_id = $apiOwnerId;
            }
            
            // If the data passes validation...
            if ($form->validate()) {

                // Attempt to save the changes to the API (skipping validation,
                // since the data has already been validated). If successful...
                if ($api->save(false)) {

                    // Record that in the log.
                    \Yii::log(
                        'API updated: ID ' . $api->api_id,
                        \CLogger::LEVEL_INFO,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Send the user back to the API details page.
                    $this->redirect(array('/api/details/', 'code' => $api->code));
                }
                // Otherwise...
                else {

                    // Record that in the log.
                    \Yii::log(
                        'API update FAILED: ID ' . $api->api_id,
                        \CLogger::LEVEL_ERROR,
                        __CLASS__ . '.' . __FUNCTION__
                    );

                    // Tell the user.
                    \Yii::app()->user->setFlash(
                        'error',
                        '<strong>Error!</strong> We were unable to save your '
                        . 'changes to the API: '
                        . $api->getErrorsAsFlatHtmlList()
                    );
                }
            }
        }
        
        // If we reach this point, render the page.
        $this->render('edit', array(
            'form' => $form,
        ));
    }
    
    public function actionIndex()
    {
        /* @var $webUser WebUser */
        $webUser = \Yii::app()->user;
        
        $sortSettings = array(
            'attributes' => array('display_name', 'owner_id'),
            'defaultOrder' => array(
                'display_name' => \CSort::SORT_ASC,
            ),
        );
        
        // If the website user is an admin, get the list of all APIs.
        if ($webUser->isAdmin()) {
            $apiList = new \CActiveDataProvider(Api::class, array(
                'criteria' => array(
                    'with' => array('approvedKeyCount', 'pendingKeyCount'),
                ),
                'sort' => $sortSettings,
            ));
        } else {
            
            // Otherwise, get the list of APIs that should be visible to the
            // current user.
            $visibleApis = array();
            /* @var $allApis Api[] */
            $allApis = Api::model()->findAll();
            foreach ($allApis as $api) {
                if ($api->isVisibleToUser($webUser->getUser())) {
                    $visibleApis[] = $api;
                }
            }
            $apiList = new \CArrayDataProvider($visibleApis, array(
                'keyField' => 'api_id',
                'sort' => $sortSettings,
            ));
        }
        
        // Render the page.
        $this->render('index', array(
            'apiList' => $apiList,
            'webUser' => $webUser,
        ));
    }

    public function actionInvitedDomains($code)
    {
        // Make sure the specified API exists.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // Get a reference to the current website user's User model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an API that you have permission to manage.'
            );
        }
        
        $apiVisibilityDomains = ApiVisibilityDomain::model()->findAllByAttributes(array(
            'api_id' => $api->api_id,
        ));
        $invitedDomainsDataProvider = new \CArrayDataProvider(
            $apiVisibilityDomains,
            array(
                'keyField' => 'api_visibility_domain_id',
                'sort' => array(
                    'attributes' => array('domain', 'created'),
                    'defaultOrder' => array('created' => \CSort::SORT_ASC)
                ),
            )
        );
        
        // Show the page.
        $this->render('invited-domains', array(
            'api' => $api,
            'invitedDomainsDataProvider' => $invitedDomainsDataProvider,
        ));
    }

    public function actionInviteDomain($code)
    {
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        if ( ! $currentUser->canInviteDomainToSeeApi($api)) {
            throw new \CHttpException(403, sprintf(
                'That is not an API that you have permission to invite users (by domain) to see.'
            ));
        }
        
        $apiVisibilityDomain = new ApiVisibilityDomain();
        
        // If the form was submitted...
        if (\Yii::app()->request->isPostRequest) {
            
            $postedData = \Yii::app()->request->getPost(
                \CHtml::modelName($apiVisibilityDomain)
            );
            
            $apiVisibilityDomain->attributes = array(
                'api_id' => $api->api_id,
                'domain' => $postedData['domain'],
                'invited_by_user_id' => $currentUser->user_id,
            );
            if ($apiVisibilityDomain->validate(array('domain'))) {
                if ($apiVisibilityDomain->save()) {
                    \Yii::app()->user->setFlash('success', sprintf(
                        '<strong>Success!</strong> You have successfully '
                        . 'enabled anyone with an email address ending with '
                        . '"@%s" to see the "%s" API.',
                        \CHtml::encode($postedData['domain']),
                        \CHtml::encode($api->display_name)
                    ));

                    $this->redirect(array(
                        '/api/details/',
                        'code' => $api->code,
                    ));
                }
            }
        }
        
        $this->render('invite-domain', array(
            'api' => $api,
            'apiVisibilityDomain' => $apiVisibilityDomain,
        ));
    }

    public function actionInvitedUsers($code)
    {
        // Make sure the specified API exists.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // Get a reference to the current website user's User model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an API that you have permission to manage.'
            );
        }
        
        $apiVisibilityUsers = ApiVisibilityUser::model()->findAllByAttributes(array(
            'api_id' => $api->api_id,
        ));
        $invitedUsersDataProvider = new \CArrayDataProvider(
            $apiVisibilityUsers,
            array(
                'keyField' => 'api_visibility_user_id',
                'sort' => array(
                    'attributes' => array('created'),
                    'defaultOrder' => array('created' => \CSort::SORT_ASC)
                ),
            )
        );
        
        // Show the page.
        $this->render('invited-users', array(
            'api' => $api,
            'invitedUsersDataProvider' => $invitedUsersDataProvider,
        ));
    }
    
    public function actionInviteUser($code)
    {
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        if ( ! $currentUser->canInviteUserToSeeApi($api)) {
            throw new \CHttpException(403, sprintf(
                'That is not an API that you have permission to invite users to see.'
            ));
        }
        
        $apiVisibilityUser = new ApiVisibilityUser();
        
        // If the form was submitted...
        if (\Yii::app()->request->isPostRequest) {
            
            $postedData = \Yii::app()->request->getPost(
                \CHtml::modelName($apiVisibilityUser)
            );
            
            $apiVisibilityUser->attributes = array(
                'api_id' => $api->api_id,
                'invited_by_user_id' => $currentUser->user_id,
                'invited_user_email' => $postedData['invited_user_email'],
            );
            if ($apiVisibilityUser->validate(array('invited_user_email'))) {
                if ($apiVisibilityUser->save()) {
                    \Yii::app()->user->setFlash('success', sprintf(
                        '<strong>Success!</strong> You have successfully '
                        . 'invited %s to see the "%s" API.',
                        \CHtml::encode($postedData['invited_user_email']),
                        \CHtml::encode($api->display_name)
                    ));

                    $this->redirect(array(
                        '/api/invite-user/',
                        'code' => $api->code,
                    ));
                }
            }
        }
        
        $this->render('invite-user', array(
            'api' => $api,
            'apiVisibilityUser' => $apiVisibilityUser,
        ));
    }

    /**
     * Render or process request to test an API
     */
    public function actionPlayground()
    {
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        $request = \Yii::app()->request;

        /* Initialize variables in case this is a GET request or in case there
         * are errors during POST processing.  */
        $download = $request->getPost('download', false);
        $debugText = null;
        $method = $request->getPost('method', 'GET');
        $requestPath = $request->getPost('path', '');
        $rawRequestBody = $request->getPost('raw-request-body');
        $requestedUrl = null;
        $rawApiRequest = null;
        $response = new Response();
        $responseSyntax = null;
        $params = (array)$request->getPost('param', [
            [
                'type' => null,
                'name' => null,
                'value' => null,
            ],
        ]);
        
        $keyId = $request->getParam('key_id', null);
        
        if ($request->isPostRequest) {
            
            /* Create copy of the request path for manipulation, ensuring it
             * begins with a forward-slash (/).  */
            $path = SS::ensureLeft($requestPath, '/');
            
            /* @var $key Key */
            $key = Key::model()->findByPk($keyId);
            if ($key && $key->isOwnedBy($currentUser)) {
                
                // Build url from components.
                $url = sprintf(
                    '%s://%s.%s%s',
                    $key->api->getApiProxyProtocol(),
                    $key->api->code,
                    $key->api->getApiProxyDomain(),
                    $path
                );
                
                $paramsCollection = new ParamsCollection();

                // Calculate the necessary parameters for the ApiAxle call.
                $paramsCollection->addParam(
                    ParamsCollection::TYPE_QUERY,
                    'api_key',
                    $key->value
                );
                if ($key->requiresSignature()) {
                    $paramsCollection->addParam(
                        ParamsCollection::TYPE_QUERY,
                        'api_sig',
                        \CalcApiSig\HmacSigner::CalcApiSig(
                            $key->value,
                            $key->secret
                        )
                    );
                }
                
                foreach ($params as $param) {
                    $paramName = (empty($param['name']) ? null : $param['name']);
                    $paramType = (empty($param['type']) ? null : $param['type']);
                    if ($paramName && ParamsCollection::isValidType($paramType)) {
                        $paramsCollection->addParam(
                            $paramType,
                            $paramName,
                            $param['value']
                        );
                    }
                }
                
                $client = new Client();
                $response = $client->request(
                    $method,
                    $url,
                    $paramsCollection,
                    $rawRequestBody
                );
                $debugText = $response->getDebugText();
                $rawApiRequest = $response->getRawRequest();
                $requestedUrl = $response->getRequestedUrl();
                
                if ($response->isJson()) {
                    $responseSyntax = 'javascript';
                } else {
                    $responseSyntax = 'markup';
                }
            } else {
                // Display an error
                \Yii::app()->user->setFlash('error', 'Invalid API selected');
            }
        }
        
        // Get list of Apis that the User has an active Key for.
        $apiOptions = $currentUser->approvedKeys;
        
        Key::sortKeysByApiName($apiOptions);
        
        if ( ! $download) {
            $this->render('playground', array(
                'keyId' => $keyId,
                'method' => $method,
                'apiOptions' => $apiOptions,
                'params' => $params,
                'path' => $requestPath,
                'rawRequestBody' => $rawRequestBody,
                'responseBody' => $response->getPrettyPrintedBody(),
                'responseHeaders' => $response->getHeaders(),
                'requestedUrl' => $requestedUrl,
                'rawApiRequest' => $rawApiRequest,
                'responseSyntax' => $responseSyntax,
                'debugText' => $debugText,
                'currentUser' => $currentUser,
            ));
        } else {
            
            /* We expect results to be either JSON, XML, or CSV, so set  *
             * headers appropriately.                                    */
            if ($response->isJson()) {
                header('Content-disposition: attachment; filename=results.json');
                header('Content-type: application/json');
            } elseif ($response->isXml()) {
                header('Content-disposition: attachment; filename=results.xml');
                header('Content-type: application/xml');
            } else {
                header('Content-disposition: attachment; filename=results.csv');
                header('Content-type: text/csv');
            }
            echo $response->getBody();
        }
    }
    
    public function actionPendingKeys($code)
    {
        // Make sure the specified API exists.
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // Get a reference to the current website user's User model.
        $currentUser = \Yii::app()->user->user;
        
        // Prevent information about it from being seen by users without
        // permission to see the specified API.
        if (( ! $api) || ( ! $api->isVisibleToUser($currentUser))) {
            throw new \CHttpException(
                404,
                'Either there is no "' . $code . '" API or you do not have '
                . 'permission to view it.'
            );
        }
        
        // Prevent this list of keys from being seen by anyone who is not
        // authorized to see them.
        if (( ! ($currentUser instanceof User)) || ( ! $currentUser->canSeeKeysForApi($api))) {
            throw new \CHttpException(
                403,
                'You do not have permission to see the list of pending keys '
                . 'for the "' . $api->display_name . '" API.'
            );
        }
        
        // Get the list of pending keys for that API.
        $pendingKeys = array();
        foreach ($api->keys as $key) {
            if ($key->isPending()) {
                $pendingKeys[] = $key;
            }
        }
        $pendingKeysDataProvider = new \CArrayDataProvider($pendingKeys, array(
            'keyField' => 'key_id',
        ));
        
        // Show the page.
        $this->render('pendingKeys', array(
            'pendingKeysDataProvider' => $pendingKeysDataProvider,
            'api' => $api,
        ));
    }
    
    public function actionRequestKey($code)
    {
        // Get the API by the code name.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));

        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // If the user already has an active key to this API, show its details
        // instead.
        if ($currentUser->hasActiveKeyToApi($api)) {
            \Yii::app()->user->setFlash('info', sprintf(
                '<strong>Note:</strong> You already have the following key '
                . 'to the %s API.',
                $api->display_name
            ));
            $activeKey = $currentUser->getActiveKeyToApi($api);
            $this->redirect(array('/key/details/', 'id' => $activeKey->key_id));
        }

        // If the user already has a pending key for this API, show its details
        // instead.
        if ($currentUser->hasPendingKeyForApi($api)) {
            \Yii::app()->user->setFlash('info', sprintf(
                '<strong>Note:</strong> You already have the following '
                . 'pending key for the %s API.',
                $api->display_name
            ));
            $pendingKey = $currentUser->getPendingKeyForApi($api);
            $this->redirect(array(
                '/key/details/',
                'id' => $pendingKey->key_id,
            ));
        }
        
        // Create a new (pending) Key object.
        $key = new Key();
        
        $request = \Yii::app()->getRequest();
        $acceptedTerms = $request->getPost('accept_terms', false);
        
        // If the form has been submitted...
        if ($request->isPostRequest) {
            
            /**
             * @todo Refactor the following to something like...
             *     $key = $currentUser->requestKeyForApi($api, $domain, $purpose);
             */

            /* Retrieve ONLY the applicable pieces of data that we trust the
             * user to provide when requesting a Key.  */
            $formData = $request->getPost(\CHtml::modelName($key));
            $key->domain = isset($formData['domain']) ? $formData['domain'] : null;
            $key->purpose = isset($formData['purpose']) ? $formData['purpose'] : null;
            if ($acceptedTerms) {
                $key->accepted_terms_on = new \CDbExpression('NOW()');
            }
            
            // Also record the extra data it needs (not submitted by the user).
            $key->user_id = $currentUser->user_id;
            $key->api_id = $api->api_id;
            $key->status = Key::STATUS_PENDING;
            $key->queries_day = $api->queries_day;
            $key->queries_second = $api->queries_second;
            
            // If the form submission was valid...
            if ($key->validate()) {
                
                // If this API is set to auto-approve key requests...
                if ( ! $key->requiresApproval()) {
                    
                    // Try to approve this pending (i.e. - requested) Key.
                    if ( ! $key->approve()) {
                        
                        // If not successful, record that in the log.
                        \Yii::log(
                            'Key request auto-approval FAILED: User ID '
                            . $currentUser->user_id . ', API ID ' . $api->api_id,
                            \CLogger::LEVEL_ERROR,
                            __CLASS__ . '.' . __FUNCTION__
                        );

                        // Tell the user.
                        \Yii::app()->user->setFlash(
                            'error',
                            '<strong>Error!</strong> Unable to create key: '
                            . $key->getErrorsAsFlatHtmlList()
                        );
                    }
                    // Otherwise...
                    else {
                        
                        // Record that in the log.
                        \Yii::log(
                            'Key request auto-approved: User ID '
                            . $currentUser->user_id . ', API ID ' . $api->api_id
                            . ', Key ID ' . $key->key_id,
                            \CLogger::LEVEL_INFO,
                            __CLASS__ . '.' . __FUNCTION__
                        );

                        // Tell the user.
                        \Yii::app()->user->setFlash(
                            'success',
                            '<strong>Success!</strong> Key created.'
                        );

                        $this->redirect(array(
                            '/key/details/',
                            'id' => $key->key_id,
                        ));
                    }
                }
                // Otherwise (i.e. - this API is NOT set to auto-approve)...
                else {
                    
                    // Save the new pending Key to the database.
                    if ( ! $key->save()) {
                        \Yii::log(
                            'Saving validated pending Key FAILED: User ID '
                            . $currentUser->user_id . ', API ID ' . $api->api_id,
                            \CLogger::LEVEL_ERROR,
                            __CLASS__ . '.' . __FUNCTION__
                        );
                        throw new \CHttpException(
                            500,
                            "Something didn't work... but we're not sure why. Please try again.",
                            1468440868
                        );
                    }

                    // Record that in the log.
                    \Yii::log(
                        'Key requested: User ID ' . $currentUser->user_id . ', API ID '
                        . $api->api_id,
                        \CLogger::LEVEL_INFO,
                        __CLASS__ . '.' . __FUNCTION__
                    );
                    
                    // Tell the user the status.
                    \Yii::app()->user->setFlash(
                        'success',
                        '<strong>Success!</strong> Key requested.'
                    );
                    
                    $this->redirect(array(
                        '/key/details/',
                        'id' => $key->key_id,
                    ));
                    
                    // NOTE: The Key model's afterSave method should have sent
                    //       an email to the API Owner (if set) about the
                    //       pending key request.
                }
            }
        }

        // If we reach this point, show the Request Key page.
        $this->render('requestKey', array(
            'api' => $api,
            'key' => $key,
            'acceptedTerms' => $acceptedTerms,
        ));
    }
    
    public function actionUsage($code)
    {
        // Get the current user's model.
        /* @var $currentUser User */
        $currentUser = \Yii::app()->user->user;
        
        // Get the API by the code name.
        /* @var $api Api */
        $api = Api::model()->findByAttributes(array('code' => $code));
        
        // If that is NOT an Api that the User has permission to manage, say so.
        if ( ! $currentUser->hasAdminPrivilegesForApi($api)) {
            throw new \CHttpException(
                403,
                'That is not an API that you have permission to manage.'
            );
        }
        
        $interval = \UsageStats::INTERVAL_DAY;
        $usageStats = new \UsageStats($interval);
        $usageStats->addEntry($api->display_name, $api->getUsage($interval));
        
        // Show the page.
        $this->render('usage', array(
            'api' => $api,
            'usageStats' => $usageStats,
            'summary' => $api->getUsageSummary()
        ));
    }
}
