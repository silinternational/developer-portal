<?php
namespace Sil\DevPortal\components;

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
     * @return string
     */
    protected function getBaseUrl()
    {
        if ($this->baseUrl !== null) {
            return $this->baseUrl;
        } else {
            return \Yii::app()->createAbsoluteUrl('auth/hybridEndpoint');
        }
    }
    
    protected function getHybridAuthConfig()
    {
        $config = array(
            'base_url' => $this->getBaseUrl(),
            'providers' => $this->getProvidersConfig(),
        );
        
        if ($this->debugMode) {
            $config['debugMode'] = true;
            $config['debug_file'] = __DIR__ . '/../data/hybrid-auth-debug.log';
        }
        
        return $config;
    }
    
    /**
     * Get an instance of Hybrid_Auth (already configured) to use for
     * authenticating a user.
     * 
     * @return \Hybrid_Auth
     */
    public function getHybridAuthInstance()
    {
        return new \Hybrid_Auth($this->getHybridAuthConfig());
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
    
    public function getProvidersList()
    {
        return \array_keys($this->getProvidersConfig());
    }
    
    /**
     * Determine whether we have at least one enabled HybridAuth provider,
     * in which case we will consider HybridAuth authentication enabled.
     * 
     * @return boolean
     */
    public function isHybridAuthEnabled()
    {
        // If we have at least one enabled HybridAuth provider, consider
        // HybridAuth authentication enabled.
        $providersConfig = $this->getProvidersConfig();
        foreach ($providersConfig as $providerConfig) {
            if (array_key_exists('enabled', $providerConfig)) {
                if ($providerConfig['enabled'] === true) {
                    return true;
                }
            }
        }
        return false;
    }
}
