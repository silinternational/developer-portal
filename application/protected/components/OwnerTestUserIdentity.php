<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\UserAuthenticationData;
use Sil\DevPortal\models\User;

class OwnerTestUserIdentity extends UserIdentity
{
    /**
     * Get the data about this user as returned by the authentication provider.
     * 
     * @param string|null $providerSlug The URL-safe (aka. "slug") version of
     *     the name of what provider to use within the current authentication
     *     type (such as which HybridAuth provider to use).
     * @return \Sil\DevPortal\components\UserAuthenticationData
     */
    public function getUserAuthData($providerSlug)
    {
        return new UserAuthenticationData(
            'TEST',
            'test-owner-002',
            'test-owner@example.com',
            'Test',
            'Owner',
            'Test Owner'
        );
    }
    
    /**
     * Return the auth. type for the UserIdentity subclass in use.
     * 
     * @return string
     */
    public function getAuthType()
    {
        return 'test-owner';
    }
    
    /**
     * Get the URL to send the user's browser to in order to log them out of the
     * applicable authentication service. Returns null if there is no need to
     * send the user to the auth. service's website.
     * 
     * @return string|null
     */
    public function getLogoutUrl()
    {
        return null;
    }
    
    /**
     * Create (and return) a new User record with the applicable data from the
     * given user auth. data.
     * 
     * @param UserAuthenticationData $userAuthData
     * @return User The new User.
     */
    protected function createUserRecord($userAuthData)
    {
        $user = parent::createUserRecord($userAuthData);
        $user->role = User::ROLE_OWNER;
        
        if ( ! $user->save()) {
            throw new \Exception(
                'Failed to update new (TEST) API Owner user record to have '
                . 'owner privileges: '
                . PHP_EOL . $user->getErrorsAsFlatTextList(),
                1474309286
            );
        }
        
        return $user;
    }
}
