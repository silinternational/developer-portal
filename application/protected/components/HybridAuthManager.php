<?php
namespace Sil\DevPortal\components;

use Hybridauth\Hybridauth;

class HybridAuthManager
{
    /** @var string|null */
    private $baseUrl;
    
    private $debugMode = false;
    
    /**
     * Constructor
     * 
     * @param string|null $baseUrl (Optional:) The URL that points to our
     *     HybridAuth endpoint.
     */
    public function __construct($baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get the URL that points to our HybridAuth endpoint. If no base URL was
     * provided to the constructor, a default value will be used.
     *
     * @param string $providerSlug
     * @return string
     */
    protected function getBaseUrl(string $providerSlug)
    {
        if ($this->baseUrl !== null) {
            return $this->baseUrl;
        } else {
            return \Yii::app()->createAbsoluteUrl('auth/login/hybrid/' . $providerSlug);
        }
    }
    
    /**
     * Get the names of the enabled HybridAuth providers. Note that this can
     * be mixed case, so slugify them before using them in a URL.
     * 
     * @return string[]
     */
    public function getEnabledProvidersList()
    {
        $enabledProviders = [];
        foreach ($this->getProvidersConfig() as $providerName => $providerConfig) {
            if ( ! array_key_exists('enabled', $providerConfig)) {
                continue;
            }
            
            if ($providerConfig['enabled'] === true) {
                $enabledProviders[] = $providerName;
            }
        }
        return $enabledProviders;
    }

    /**
     * @param string $providerSlug
     * @return array
     */
    protected function getHybridAuthConfig(string $providerSlug)
    {
        $config = [
            'callback' => $this->getBaseUrl($providerSlug),
            'providers' => $this->getProvidersConfig(),
        ];
        
        if ($this->debugMode) {
            $config['debugMode'] = true;
            $config['debug_file'] = __DIR__ . '/../data/hybrid-auth-debug.log';
        }
        
        return $config;
    }

    /**
     * Get an instance of Hybridauth (already configured) to use for
     * authenticating a user.
     *
     * @param string $providerSlug
     * @return Hybridauth
     * @throws \Hybridauth\Exception\InvalidArgumentException
     */
    public function getHybridAuthInstance(string $providerSlug)
    {
        return new Hybridauth($this->getHybridAuthConfig($providerSlug));
    }

    /**
     * Get the wrapper path for one of HybridAuth's additional providers. If
     * the given $providerName is not one we have manually configured within
     * this function, null will be provided.
     * 
     * @param string $providerName The name of the provider (e.g. 'GitHub').
     * @return string|null
     */
    public static function getPathToAdditionalProviderFile($providerName)
    {
        switch ($providerName) {
            case 'GitHub':
                return sprintf(
                    '%s/../../vendor/hybridauth/hybridauth/additional-providers/'
                    . 'hybridauth-%s/Providers/%s.php',
                    __DIR__,
                    AuthManager::slugify($providerName),
                    $providerName
                );

            default:
                return null;
        }
    }
    
    /**
     * Get the array of configuration data for the various HybridAuth providers
     * that we have config data for.
     * 
     * @return array
     */
    protected function getProvidersConfig()
    {
        $paramsCollection = \Yii::app()->getParams();
        if ($paramsCollection->hasProperty('hybridAuth') &&
            \array_key_exists('providers', $paramsCollection['hybridAuth'])) {
            $providers = $paramsCollection['hybridAuth']['providers'];
        } else {
            $providers = array();
        }
        
        return $providers;
    }
    
    /**
     * Determine whether we have at least one enabled HybridAuth provider,
     * in which case we will consider HybridAuth authentication enabled.
     * 
     * @return boolean
     */
    public function isHybridAuthEnabled()
    {
        return !empty($this->getEnabledProvidersList());
    }
}
