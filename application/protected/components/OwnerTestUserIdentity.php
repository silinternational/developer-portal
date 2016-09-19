<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\UserAuthenticationData;

class OwnerTestUserIdentity extends UserIdentity
{
    /**
     * Get the data about this user as returned by the authentication provider.
     * 
     * @return \Sil\DevPortal\components\UserAuthenticationData
     */
    public function getUserAuthData()
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
}
