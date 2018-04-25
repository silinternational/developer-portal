<?php
namespace Sil\DevPortal\components;

use Stringy\StaticStringy as SS;

class AuthManager
{
    /**
     * The list of known authentication types. Note that some of these might not
     * currently be enabled (in the "protected/config/main.php" file).
     * 
     * Each entry's key is the auth type code (used in URLs to specify a
     * particular auth type), and the entry's value is the name of the
     * UserIdentity subclass to use for that auth type.
     * 
     * @var array
     */
    private $knownAuthTypes = array(
        'hybrid' => '\Sil\DevPortal\components\HybridAuthUserIdentity',
        'saml' => '\Sil\DevPortal\components\SamlUserIdentity',
        'test-owner' => '\Sil\DevPortal\components\OwnerTestUserIdentity',
        'test-user' => '\Sil\DevPortal\components\UserTestUserIdentity',
    );
    
    /**
     * Determine whether there are multiple authentication types that we are set
     * up to use (not only known, but also enabled). If there are NOT multiple
     * auth types, we could presumably safely assume that the user does NOT need
     * to select a login option since there is (at most) only one option.
     * 
     * @return bool Whether there are multiple authentication types available
     *     for users to log in through.
     */
    public function canUseMultipleAuthTypes()
    {
        $numUsableAuthTypes = 0;
        foreach ($this->getKnownAuthTypeNames() as $knownAuthType) {
            if ($this->isAuthTypeEnabled($knownAuthType)) {
                $numUsableAuthTypes += 1;
            }
        }
        return ($numUsableAuthTypes > 1);
    }
    
    public function getApplicationEnv()
    {
        return (defined('APPLICATION_ENV') ? APPLICATION_ENV : null);
    }
    
    /**
     * Try to get the default authentication type to use. Returns null if there
     * is no obvious default.
     * 
     * @return string|null
     */
    public function getDefaultAuthType()
    {
        // Get the list of enabled, known auth types.
        $enabledAuthTypes = array();
        foreach ($this->getKnownAuthTypeNames() as $knownAuthType) {
            if ($this->isAuthTypeEnabled($knownAuthType)) {
                $enabledAuthTypes[] = $knownAuthType;
            }
        }
        
        // If there's exactly one, return it. Otherwise return null.
        if (count($enabledAuthTypes) === 1) {
            return $enabledAuthTypes[0];
        } else {
            return null;
        }
    }
    
    /**
     * Get the URL for the default login option (if there is one).
     * 
     * @return string|null The URL, or null if there is no default.
     */
    public function getDefaultLoginOptionUrl()
    {
        $loginOptions = $this->getLoginOptions();
        if (count($loginOptions) === 1) {
            return $loginOptions[0]->getUrl();
        }
        return null;
    }
    
    /**
     * If there is a default provider for the given auth. type, return (the
     * slugified version of) it, regardless of whether that auth. type is
     * currently enabled.
     * 
     * @param string|null $authType The auth. type (if known).
     * @return string|null
     */
    public function getDefaultProviderSlugFor($authType)
    {
        $defaultProvider = null;
        switch ($authType) {
            case 'saml':
                $defaultProvider = 'SAML';
                break;
                
            case 'hybrid':
                $hybridAuthManager = new HybridAuthManager();
                $enabledProviders = $hybridAuthManager->getEnabledProvidersList();
                if (count($enabledProviders) === 1) {
                    $defaultProvider = $enabledProviders[0];
                }
                break;
            
            case 'test-owner':
            case 'test-user':
                $defaultProvider = 'test';
                break;
        }
        
        if ($defaultProvider === null) {
            return null;
        }
        
        return self::slugify($defaultProvider);
    }
    
    /**
     * Get an identify class instance appropriate for the specified type of
     * authentication. Throws an InvalidArgumentException if an unknown auth
     * type is provided.
     * 
     * @param string $authType The authentication type (eg: 'saml', 'hybrid').
     * @return UserIdentity An instance of the correct subclass of
     *     <code>\Sil\DevPortal\components\UserIdentity</code>.
     * @throws \InvalidArgumentException
     */
    public function getIdentityForAuthType($authType)
    {
        if ( ! $this->isAuthTypeEnabled($authType)) {
            throw new \InvalidArgumentException(
                'That authentication type (' . $authType . ') is not currently '
                . 'enabled.',
                1442337689
            );
        }
        
        /* @var $identity \Sil\DevPortal\components\UserIdentity|null */
        $identity = null;
        
        foreach ($this->getKnownAuthTypes() as $knownAuthType => $identityClassName) {
            if ($authType === $knownAuthType) {
                $identity = new $identityClassName();
            }
        }
        
        if ($identity === null) {
            throw new \InvalidArgumentException(
                'Unknown auth type: ' . $authType,
                1441908933
            );
        }
        
        return $identity;
    }
    
    public function getLoggedOutMessageHtml($authType, $authProvider)
    {
        if (($authType === 'hybrid') && ($authProvider === 'Google')) {
            $messageHtml = sprintf(
                '<h3>Warning!</h3>'
                . '<p><b>'
                . 'You have logged out of %1$s, but you are still logged '
                . 'into %2$s.'
                . '</b></p>'
                . '<p>'
                . 'Logging out of %1$s does not log you out of '
                . '%2$s. If you are using a shared or public computer '
                . 'we highly recommend you log out of %2$s as well. '
                . 'You can do that by clicking the button below. Although your '
                . 'accounts will be logged out, your browser will remember '
                . 'your username for future login attempts. You may wish to '
                . 'use the Remove option on that page so that your browser '
                . 'does not remember your username either.'
                . '</p>'
                . '<p>'
                . '<a href="%3$s" target="_blank" '
                .    'class="btn btn-primary btn-large">Go to %2$s Logout Page'
                . '</a>'
                . '</p>',
                \CHtml::encode(\Yii::app()->name),
                \CHtml::encode($authProvider),
                'https://accounts.google.com/logout'
            );
        } else {
            $messageHtml = sprintf(
                '<h3>Logged out</h3>'
                . '<p>You have been logged out of %s.</p>',
                \CHtml::encode(\Yii::app()->name)
            );
        }
        return $messageHtml;
    }
    
    /**
     * Get the login options formatted as an array usable in a menu.
     * 
     * @return array[]
     */
    public static function getLoginMenuItems()
    {
        $authManager = new AuthManager();
        $loginOptions = $authManager->getLoginOptions();
        $loginMenuItems = [];
        foreach ($loginOptions as $loginOption) {
            $loginMenuItems[] = [
                'label' => 'Login with ' . $loginOption->getDisplayName(),
                'url' => $loginOption->getUrl(),
            ];
        }
        return $loginMenuItems;
    }
    
    /**
     * Get the list of login options.
     *
     * @return LoginOption[] The list of login options.
     */
    public function getLoginOptions()
    {
        $loginOptions = array();
        if ($this->isAuthTypeEnabled('saml')) {
            $loginOptions[] = new LoginOption(
                'saml',
                null,
                \Yii::app()->params['saml']['idpName']
            );
        }
        if ($this->isAuthTypeEnabled('hybrid')) {
            $hybridAuthManager = new HybridAuthManager();
            foreach ($hybridAuthManager->getEnabledProvidersList() as $provider) {
                $loginOptions[] = new LoginOption('hybrid', $provider);
            }
        }
        if ($this->isAuthTypeEnabled('test-user')) {
            $loginOptions[] = new LoginOption('test-user', null, 'Test (User)');
        }
        if ($this->isAuthTypeEnabled('test-owner')) {
            $loginOptions[] = new LoginOption('test-owner', null, 'Test (Owner)');
        }
        return $loginOptions;
    }
    
    protected function getKnownAuthTypeNames()
    {
        return \array_keys($this->knownAuthTypes);
    }
    
    protected function getKnownAuthTypes()
    {
        return $this->knownAuthTypes;
    }
    
    public function isAuthTypeEnabled($authType)
    {
        switch ($authType) {
            case 'saml':
                return $this->isSamlAuthEnabled();
            case 'hybrid':
                return $this->isHybridAuthEnabled();
            case 'test-user':
            case 'test-owner':
                return $this->isTestAuthEnabled();
            default:
                return false;
        }
    }
    
    protected function isHybridAuthEnabled()
    {
        // If we have at least one enabled HybridAuth provider, consider
        // HybridAuth authentication enabled.
        $hybridAuthManager = new HybridAuthManager();
        return $hybridAuthManager->isHybridAuthEnabled();
    }
    
    protected function isSamlAuthEnabled()
    {
        return \Yii::app()->params['saml']['enabled'];
    }
    
    protected function isTestAuthEnabled()
    {
        $applicationEnv = $this->getApplicationEnv();
        return ($applicationEnv === 'development') || ($applicationEnv === 'testing');
    }
    
    /**
     * Log the user out of whichever authentication service they logged in
     * through (which MAY redirect them to the auth. service's website).
     * 
     * NOTE: This currently assumes that any particular user can only be logged
     *       in through a single authentication service at a time.
     * 
     * @param \WebUser $webUser
     */
    public function logout($webUser)
    {
        $authType = $webUser->getAuthType();
        
        if ($authType) {
            $identity = $this->getIdentityForAuthType($authType);
            $logoutUrl = $identity->getLogoutUrl();
        } else {
            $logoutUrl = null;
        }
        
        $webUser->clearStates();
        $webUser->logout();
        
        if ( ! empty($logoutUrl)) {
            \Yii::app()->controller->redirect($logoutUrl);
        }
    }
    
    public static function slugify($string)
    {
        return (string)SS::slugify($string);
    }
}
