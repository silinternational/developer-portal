<?php
namespace Sil\DevPortal\components\ApiAxle;

use ApiAxle\Api\Api as ApiAxleApi;
use ApiAxle\Api\Key as ApiAxleKey;
use ApiAxle\Api\Keyring as ApiAxleKeyring;
use Sil\DevPortal\components\ApiAxle\ItemInfo;

class Client extends BaseClient
{
    /**
     * @param string $apiName The code name of the API in question.
     * @param array $data
     * @return ApiInfo
     */
    public function createApi($apiName, $data)
    {
        $apiAxleApi = $this->api()->create($apiName, $data);
        return new ApiInfo(
            $apiAxleApi->getName(),
            $apiAxleApi->getData()
        );
    }
    
    /**
     * @param string $keyValue
     * @param array $data
     * @return KeyInfo
     */
    public function createKey($keyValue, $data)
    {
        $apiAxleKey = $this->key()->create($keyValue, $data);
        return new KeyInfo(
            $apiAxleKey->getKey(),
            $apiAxleKey->getData()
        );
    }
    
    /**
     * @param string $keyringName
     * @return KeyringInfo
     */
    public function createKeyring($keyringName)
    {
        $apiAxleKeyring = $this->keyring()->create($keyringName);
        return new KeyringInfo(
            $apiAxleKeyring->getName(),
            null
        );
    }
    
    /**
     * @param string $apiName The code name of the API in question.
     * @return boolean
     */
    public function deleteApi($apiName)
    {
        return $this->api()->delete($apiName);
    }
    
    /**
     * @param string $keyValue
     * @return boolean
     */
    public function deleteKey($keyValue)
    {
        return $this->key()->delete($keyValue);
    }
    
    /**
     * @param string $keyringName
     * @return boolean
     */
    public function deleteKeyring($keyringName)
    {
        return $this->keyring()->delete($keyringName);
    }
    
    /**
     * @param string $apiName The code name of the API in question.
     * @return ApiInfo
     */
    public function getApiInfo($apiName)
    {
        return ApiInfo::from($this->getInfo($this->api(), $apiName));
    }
    
    /**
     * Get usage statistics for the specified API.
     * 
     * @param string $apiName The code name of the API in question.
     * @param integer $timeStart A Unix timestamp.
     * @param string $granularity The desired granularity (e.g. - 'second',
     *     'minute', 'hour', or 'day').
     * @return \stdClass The stats data.
     */
    public function getApiStats($apiName, $timeStart, $granularity)
    {
        $apiAxleApi = $this->api()->get($apiName);
        return $apiAxleApi->getStats($timeStart, false, $granularity, 'false');
    }
    
    /**
     * Get the info about something in ApiAxle. What it is depends on what type
     * of internal client for ApiAxle was provided.
     * 
     * @param ApiAxleApi|ApiAxleKey|ApiAxleKeyring $apiAxle The internal client
     *     to use for communicating with ApiAxle.
     * @param string $name The name of the thing to be retrieved.
     * @return ItemInfo The information returned by ApiAxle about that thing.
     */
    protected function getInfo($apiAxle, $name)
    {
        $result = $apiAxle->get($name);
        return new ItemInfo(
            $result->getName(),
            $result->getData()
        );
    }
    
    /**
     * @param string $keyValue
     * @return KeyInfo
     */
    public function getKeyInfo($keyValue)
    {
        return KeyInfo::from($this->getInfo($this->key(), $keyValue));
    }
    
    /**
     * @param string $keyringName
     * @return KeyringInfo
     */
    public function keyringExists($keyringName)
    {
        $apiAxleKeyring = $this->keyring()->get($keyringName);
        return !empty($apiAxleKeyring->getCreatedAt());
    }
    
    /**
     * Link the specified key with the specified API (in ApiAxle).
     * 
     * @param string $keyValue The value of the key to link.
     * @param string $apiName The code name of the API to link it to.
     */
    public function linkKeyToApi($keyValue, $apiName)
    {
        $apiAxleApi = $this->api()->get($apiName);
        $apiAxleKey = $this->key()->get($keyValue);
        
        $apiAxleApi->linkKey($apiAxleKey);
    }
    
    /**
     * Link the specified key with the specified keyring (in ApiAxle).
     * 
     * @param string $keyValue
     * @param string $keyringName
     */
    public function linkKeyToKeyring($keyValue, $keyringName)
    {
        $apiAxleKeyring = $this->keyring()->get($keyringName);
        $apiAxleKey = $this->key()->get($keyValue);
        
        $apiAxleKeyring->linkKey($apiAxleKey);
    }
    
    /**
     * Get a list of existing APIs.
     * 
     * @param int $fromIndex Integer for the index of the first API you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last API you want to
     *     see. Starts at zero.
     * @return ApiInfo[]
     */
    public function listApis($fromIndex, $toIndex)
    {
        $list = [];
        $apiAxleApiList = $this->api()->getList($fromIndex, $toIndex);
        foreach ($apiAxleApiList as $apiAxleApi) {
            /* @var $apiAxleApi ApiAxleApi */
            $list[] = new ApiInfo($apiAxleApi->getName(), $apiAxleApi->getData());
        }
        return $list;
    }
    
    /**
     * Get a list of existing keys.
     * 
     * @param int $fromIndex Integer for the index of the first API you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last API you want to
     *     see. Starts at zero.
     * @return KeyInfo[]
     */
    public function listKeys($fromIndex, $toIndex)
    {
        $list = [];
        $apiAxleKeyList = $this->key()->getList($fromIndex, $toIndex);
        foreach ($apiAxleKeyList as $apiAxleKey) {
            /* @var $apiAxleKey ApiAxleKey */
            $list[] = new KeyInfo($apiAxleKey->getKey(), $apiAxleKey->getData());
        }
        return $list;
    }
    
    /**
     * Get a list of existing keys.
     * 
     * @param string $apiName The code name of the API whose keys are desired.
     * @param int $fromIndex Integer for the index of the first API you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last API you want to
     *     see. Starts at zero.
     * @return KeyInfo[]
     */
    public function listKeysForApi($apiName, $fromIndex = 0, $toIndex = 100)
    {
        $list = [];
        $apiAxleApi = $this->api()->get($apiName);
        $apiAxleKeyList = $apiAxleApi->getKeyList($fromIndex, $toIndex);
        foreach ($apiAxleKeyList as $apiAxleKey) {
            /* @var $apiAxleKey ApiAxleKey */
            $list[] = new KeyInfo($apiAxleKey->getKey(), $apiAxleKey->getData());
        }
        return $list;
    }
    
    /**
     * Update an object in ApiAxle. What it is depends on what type of internal
     * client for ApiAxle was provided.
     *
     * @param ApiAxleApi|ApiAxleKey $apiAxle The internal client to use for
     *     communicating with ApiAxle.
     * @param string $name The name of the thing to be retrieved.
     * @param array $data The new data.
     * @return ItemInfo The information returned by ApiAxle about the updated
     *     object.
     */
    protected function update($apiAxle, $name, $data)
    {
        $result = $apiAxle->get($name);
        $result->update($data);
        return new ItemInfo(
            $result->getName(),
            $result->getData()
        );
    }
    
    /**
     * @param string $apiName
     * @param array $data
     * @return ApiInfo
     */
    public function updateApi($apiName, $data)
    {
        return ApiInfo::from($this->update($this->api(), $apiName, $data));
    }
    
    /**
     * @param string $keyValue
     * @param array $data
     * @return KeyInfo
     */
    public function updateKey($keyValue, $data)
    {
        return KeyInfo::from($this->update($this->key(), $keyValue, $data));
    }
    
    // NOTE: There is no update function for ApiAxle\Api\Keyring.
}
