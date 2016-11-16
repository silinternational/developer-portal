<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\UserAuthenticationData;

class UserTestUserIdentity extends UserIdentity
{
    /**
     * Get the data about this user as returned by the authentication provider.
     * 
     * @param string|null $providerSlug The URL-safe (aka. "slug") version of
     *     the name of what provider to use within the current authentication
     *     type (such as which HybridAuth provider to use).
     * @return \Sil\DevPortal\components\UserAuthenticationData
     */
    public function getUserAuthData($providerSlug = null)
    {
        return new UserAuthenticationData(
            'TEST',
            'test-user-001',
            'test-user@example.com',
            'Test',
            'User',
            'Test User'
        );
    }
    
    /**
     * Return the auth. type for the UserIdentity subclass in use.
     * 
     * @return string
     */
    public function getAuthType()
    {
        return 'test-user';
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
}
