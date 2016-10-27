<?php
namespace Sil\DevPortal\components\ApiAxle;

class BaseClient
{
    private $config;
    
    /**
     * Create a new client for managing ApiAxle.
     * 
     * @param array $config Configuration settings, including values for at
     *     least the following keys (if you are going to actually use the
     *     created client): endpoint, key, secret
     */
    public function __construct($config = [])
    {
        /* If told how/whether to verify SSL peers, set the appropriate config
         * value.  */
        if (array_key_exists('ssl_verifypeer', $config)) {
            $config = \CMap::mergeArray($config, [
                'http_client_options' => [
                    'defaults' => [
                        'verify' => $config['ssl_verifypeer'],
                    ],
                ],
            ]);
            unset($config['ssl_verifypeer']);
        }
        
        $this->config = $config;
    }
    
    /**
     * Get the client that we use (internally) for communicating with ApiAxle
     * about APIs.
     * 
     * @return \Apiaxle\Api
     */
    protected function api()
    {
        return new \Apiaxle\Api($this->config);
    }
    
    /**
     * Get the client that we use (internally) for communicating with ApiAxle
     * about keys.
     * 
     * @return \Apiaxle\Key
     */
    protected function key()
    {
        return new \Apiaxle\Key($this->config);
    }
    
    /**
     * Get the client that we use (internally) for communicating with ApiAxle
     * about keyrings.
     * 
     * @return \Apiaxle\Keyring
     */
    protected function keyring()
    {
        return new \Apiaxle\Keyring($this->config);
    }
}
