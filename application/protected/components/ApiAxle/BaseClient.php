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
     * @return \ApiAxle\Api\Key
     */
    protected function key()
    {
        return new \ApiAxle\Api\Key($this->config);
    }
    
    /**
     * Get the client that we use (internally) for communicating with ApiAxle
     * about keyrings.
     * 
     * @return \ApiAxle\Api\Keyring
     */
    protected function keyring()
    {
        return new \ApiAxle\Api\Keyring($this->config);
    }
}
