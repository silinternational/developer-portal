<?php
namespace Sil\DevPortal\components\ApiAxle;

use ApiAxle\Api\Api as ApiAxleApi;
use ApiAxle\Api\Key as ApiAxleKey;
use ApiAxle\Api\Keyring as ApiAxleKeyring;
use Sil\DevPortal\components\ApiAxle\ItemInfo;
use Sil\DevPortal\components\Exception\NotFoundException;

class Client extends BaseClient
{
    /**
     * @param string $apiName The code name of the API in question.
     * @param array $data
     * @return ApiInfo
     */
    public function createApi($apiName, $data)
    {
        $data['id'] = $apiName;
        $response = $this->api()->create($data);
        return new ApiInfo(
            $apiName,
            $this->getDataFromResponse($response)
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
        $response = $this->api()->delete([
            'id' => $apiName,
        ]);
        return $this->getDataFromResponse($response);
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
        $response = $this->api()->get([
            'id' => $apiName,
        ]);
        return new ApiInfo(
            $apiName,
            $this->getDataFromResponse($response)
        );
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
        $response = $this->api()->getStats([
            'id' => $apiName,
            'from' => $timeStart,
            'granularity' => $granularity,
            'format_timeseries' => false,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    protected function getDataFromResponse($response)
    {
        $statusCode = $response['statusCode'];
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new Exception(sprintf(
                'Unexpected status code (%s) in response: %s',
                $statusCode,
                var_export($response, true)
            ), 1477334316);
        }
        return $response['results'];
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
     * Get usage statistics for the specified key.
     * 
     * @param string $keyValue The value of the Key whose stats are desired.
     * @param integer $timeStart A Unix timestamp.
     * @param string $granularity The desired granularity (e.g. - 'second',
     *     'minute', 'hour', or 'day').
     * @return \stdClass The stats data.
     */
    public function getKeyStats($keyValue, $timeStart, $granularity)
    {
        $apiAxleKey = $this->key()->get($keyValue);
        return $apiAxleKey->getStats($timeStart, false, $granularity, 'false');
    }
    
    /**
     * @param string $keyringName
     * @return KeyringInfo
     */
    public function keyringExists($keyringName)
    {
        try {
            $apiAxleKeyring = $this->keyring()->get($keyringName);
            return !empty($apiAxleKeyring->getCreatedAt());
        } catch (\Exception $e) {
            if (preg_match('/API returned error: Keyring \'[^\']+\' not found./', $e->getMessage()) === 0) {
                return false;
            }
            throw $e;
        }
    }
    
    /**
     * Link the specified key with the specified API (in ApiAxle).
     * 
     * @param string $keyValue The value of the key to link.
     * @param string $apiName The code name of the API to link it to.
     */
    public function linkKeyToApi($keyValue, $apiName)
    {
        $this->api()->linkKey([
            'id' => $apiName,
            'key' => $keyValue,
        ]);
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
     * @return string[] The list of API code names.
     */
    public function listApis($fromIndex, $toIndex)
    {
        $response = $this->api()->list([
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
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
     * @return string[] The list of key values (aka. key identifiers).
     */
    public function listKeysForApi($apiName, $fromIndex = 0, $toIndex = 100)
    {
        $response = $this->api()->listKeys([
            'id' => $apiName,
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
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
     * @throws NotFoundException
     * @throws \Exception
     */
    public function updateApi($apiName, $data)
    {
        try {
            $data['id'] = $apiName;
            $response = $this->api()->update($data);
            $responseData = $this->getDataFromResponse($response);
            return new ApiInfo(
                $apiName,
                $responseData['new']
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Api "%s" not found.',
                    $apiName
                ), 1477414149, $e);
            }
            throw $e;
        }
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
