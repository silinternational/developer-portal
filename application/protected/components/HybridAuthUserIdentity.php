<?php
namespace Sil\DevPortal\components;

class HybridAuthUserIdentity extends UserIdentity
{
    /**
     * @param string $providerSlug
     * @return \Hybridauth\Hybridauth
     * @throws \Hybridauth\Exception\InvalidArgumentException
     */
    protected function getHybridAuthInstance(string $providerSlug)
    {
        $hybridAuthManager = new HybridAuthManager();
        return $hybridAuthManager->getHybridAuthInstance($providerSlug);
    }
    
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
        $hybridAuth = $this->getHybridAuthInstance($providerSlug);
        
        // Try to authenticate the user with the authentication provider. The
        // user will be redirected to that auth. provider for authentication, or
        // if that has already happened then HybridAuth will ignore this step
        // and return an instance of the adapter.
        $authProvider = $this->getFullAuthProviderName($providerSlug);
        $authProviderAdapter = $hybridAuth->authenticate($authProvider);
        
        try {
            $userProfile = $authProviderAdapter->getUserProfile();
        } catch (\Exception $e) {
            
            // In case HybridAuth is trying to use something (an authorization?)
            // that Google will no longer accept, have HybridAuth forget about
            // any active user and re-send the user to the provider's login
            // screen again.
            $hybridAuth->disconnectAllAdapters();
            $authProviderAdapter = $hybridAuth->authenticate($authProvider);
            $userProfile = $authProviderAdapter->getUserProfile();
        }
        
        /* Ensure that we got a verified email address.
         * 
         * NOTE: This is important both to protect against social engineering
         *       (a user seeming to be who they are not, and asking us for help)
         *       and because certain APIs may only be visible to people with an
         *       email address on a certain domain, and that protection could be
         *       bypassed if a user's email address is not verified.
         */
        if ( ! $userProfile->emailVerified) {
            throw new \Exception(
                sprintf(
                    '%s did not return an email address for you that has been '
                    . 'verified. Please verify your email address on %s before '
                    . 'logging in here.',
                    $authProvider,
                    $authProvider
                ),
                1444924124
            );
        }
        
        return new UserAuthenticationData(
            $authProvider,
            $userProfile->identifier,
            $userProfile->emailVerified,
            $userProfile->firstName,
            $userProfile->lastName,
            $userProfile->displayName
        );
    }
    
    protected function getFullAuthProviderName($providerSlug = null)
    {
        $hybridAuthManager = new HybridAuthManager();
        $enabledProviders = $hybridAuthManager->getEnabledProvidersList();
        foreach ($enabledProviders as $enabledProvider) {
            if ($providerSlug === AuthManager::slugify($enabledProvider)) {
                return $enabledProvider;
            }
        }
        return null;
    }
    
    public function getAuthType()
    {
        return 'hybrid';
    }
    
    public function getLogoutUrl()
    {
        return null;
    }
    
    public function logout()
    {
        $hybridAuth = $this->getHybridAuthInstance('');
        $hybridAuth->disconnectAllAdapters();
    }
}
