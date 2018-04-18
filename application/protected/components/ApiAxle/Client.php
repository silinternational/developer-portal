<?php
namespace Sil\DevPortal\components\ApiAxle;

use Sil\DevPortal\components\Exception\NotFoundException;

class Client extends BaseClient
{
    /**
     * @param string $apiName
     * @return bool
     * @throws \Exception
     */
    public function apiExists($apiName)
    {
        return ($this->getApiInfo($apiName) !== null);
    }
    
    /**
     * @param string $apiName The code name of the API in question.
     * @param array $data
     * @return ApiInfo
     */
    public function createApi($apiName, $data)
    {
        try {
            $data['id'] = $apiName;
            $response = $this->api()->create($data);
            return new ApiInfo($apiName, $this->getDataFromResponse($response));
        } catch (\Exception $e) {
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * @param string $keyValue
     * @param array $data
     * @return KeyInfo
     */
    public function createKey($keyValue, $data)
    {
        try {
            $data['id'] = $keyValue;
            $response = $this->key()->create($data);
            return new KeyInfo(
                $keyValue,
                $this->getDataFromResponse($response)
            );
        } catch (\Exception $e) {
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * @param string $keyringName
     * @return KeyringInfo
     * @throws \Exception
     */
    public function createKeyring($keyringName)
    {
        try {
            $response = $this->keyring()->create(['id' => $keyringName]);
            return new KeyringInfo(
                $keyringName,
                $this->getDataFromResponse($response)
            );
        } catch (\Exception $e) {
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * @param string $apiName The code name of the API in question.
     * @return boolean
     */
    public function deleteApi($apiName)
    {
        $response = $this->api()->delete(['id' => $apiName]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Delete the specified key from ApiAxle. If unable to do so for some
     * reason, an exception will be thrown.
     * 
     * @param string $keyValue
     * @throws NotFoundException
     * @throws \Exception
     */
    public function deleteKey($keyValue)
    {
        try {
            $response = $this->key()->delete(['id' => $keyValue]);
            $successfullyDeleted = $this->getDataFromResponse($response);
            if ( ! $successfullyDeleted) {
                throw new \Exception(
                    'We could not delete that key from ApiAxle for some reason.',
                    1478118797
                );
            }
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'That key (%s%s) was not found.',
                    substr($keyValue, 0, 12),
                    ((strlen($keyValue) > 12) ? '...' : '')
                ), 1477584689, $e);
            }
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * @param string $keyringName
     * @return boolean
     */
    public function deleteKeyring($keyringName)
    {
        $response = $this->keyring()->delete(['id' => $keyringName]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Get the information about the specified API (or null if no such API was
     * found).
     * 
     * @param string $apiName The code name of the API in question.
     * @return ApiInfo|null
     */
    public function getApiInfo($apiName)
    {
        try {
            $response = $this->api()->get(['id' => $apiName]);
            return new ApiInfo(
                $apiName,
                $this->getDataFromResponse($response)
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * Get usage statistics for the specified API.
     * 
     * @param string $apiName The code name of the API in question.
     * @param integer $timeStart A Unix timestamp.
     * @param string $granularity The desired granularity (e.g. - 'second',
     *     'minute', 'hour', or 'day').
     * @param integer|null $timeEnd (Optional:) A Unix timestamp for an end date.
     * @return array The stats data.
     */
    public function getApiStats($apiName, $timeStart, $granularity, $timeEnd = null)
    {
        $parameters = [
            'id' => $apiName,
            'from' => $timeStart,
            'granularity' => $granularity,
            'format_timeseries' => false,
        ];
        if ($timeEnd !== null) {
            $parameters['to'] = $timeEnd;
        }
        $response = $this->api()->getStats($parameters);
        return $this->getDataFromResponse($response);
    }
    
    protected function getDataFromResponse($response)
    {
        $statusCode = $response['statusCode'];
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Exception(sprintf(
                'Unexpected status code (%s) in response: %s',
                $statusCode,
                var_export($response, true)
            ), 1477334316);
        }
        return $response['results'];
    }
    
    protected function getErrorMessageFromGuzzleException(
        \GuzzleHttp\Exception\RequestException $exception
    ) {
        if ($exception->hasResponse() && $exception->getResponse()->getBody()) {
            return $exception->getResponse()->getBody()->getContents();
        } else {
            return $exception->getMessage();
        }
    }
    
    /**
     * Get the information about the specified key (or null if no such key was
     * found).
     * 
     * @param string $keyValue
     * @return KeyInfo|null
     * @throws \Exception
     */
    public function getKeyInfo($keyValue)
    {
        try {
            $response = $this->key()->get(['id' => $keyValue]);
            return new KeyInfo($keyValue, $this->getDataFromResponse($response));
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * Get usage statistics for the specified key.
     * 
     * @param string $keyValue The value of the Key whose stats are desired.
     * @param integer $timeStart A Unix timestamp.
     * @param string $granularity The desired granularity (e.g. - 'second',
     *     'minute', 'hour', or 'day').
     * @return array The stats data.
     */
    public function getKeyStats($keyValue, $timeStart, $granularity)
    {
        $response = $this->key()->getStats([
            'id' => $keyValue,
            'from' => $timeStart,
            'granularity' => $granularity,
            'format_timeseries' => false,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * @param string $keyValue
     * @return bool
     * @throws \Exception
     */
    public function keyExists($keyValue)
    {
        return ($this->getKeyInfo($keyValue) !== null);
    }
    
    /**
     * @param string $keyringName
     * @return bool
     * @throws \Exception
     */
    public function keyringExists($keyringName)
    {
        try {
            $this->keyring()->get(['id' => $keyringName]);
            return true;
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * Link the specified key with the specified API (in ApiAxle).
     * 
     * @param string $keyValue The value of the key to link.
     * @param string $apiName The code name of the API to link it to.
     * @throws NotFoundException
     * @throws \Exception
     */
    public function linkKeyToApi($keyValue, $apiName)
    {
        try {
            $this->api()->linkKey([
                'id' => $apiName,
                'key' => $keyValue,
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Either the key (%s) or the API (%s) was not found.',
                    $keyValue,
                    $apiName
                ), 1477428053, $e);
            }
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * Link the specified key with the specified keyring (in ApiAxle).
     * 
     * @param string $keyValue
     * @param string $keyringName
     * @throws NotFoundException
     * @throws \Exception
     */
    public function linkKeyToKeyring($keyValue, $keyringName)
    {
        try {
            $this->keyring()->linkKey([
                'id' => $keyringName,
                'key' => $keyValue,
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Either the key (%s) or the keyring (%s) was not found.',
                    $keyValue,
                    $keyringName
                ), 1477419154, $e);
            }
            $this->throwWithBetterMessage($e);
        }
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
     * Get a list of existing keyrings.
     * 
     * @param int $fromIndex Integer for the index of the first keyring you want
     *     to see. Starts at zero.
     * @param int $toIndex Integer for the index of the last keyring you want to
     *     see. Starts at zero.
     * @return string[] The list of keyring identifiers.
     */
    public function listKeyrings($fromIndex, $toIndex)
    {
        $response = $this->keyring()->list([
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Get a list of existing keys.
     * 
     * @param int $fromIndex Integer for the index of the first key you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last key you want to
     *     see. Starts at zero.
     * @return string[] The list of key values (aka. key identifiers).
     */
    public function listKeys($fromIndex, $toIndex)
    {
        $response = $this->key()->list([
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Get a list of existing keys linked to the specified keyring.
     * 
     * @param int $fromIndex Integer for the index of the first key you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last key you want to
     *     see. Starts at zero.
     * @return string[] The list of key values (aka. key identifiers).
     */
    public function listKeysOnKeyring($keyringId, $fromIndex = 0, $toIndex = 100)
    {
        $response = $this->keyring()->listKeys([
            'id' => $keyringId,
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Get a list of existing keys for the specified API.
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
    
    protected function throwWithBetterMessage($exception)
    {
        if ($exception instanceof \GuzzleHttp\Exception\RequestException) {
            throw new \Exception(
                $this->getErrorMessageFromGuzzleException($exception),
                $exception->getCode(),
                $exception
            );
        }
        throw $exception;
    }
    
    /**
     * Unlink the specified key from the specified API (in ApiAxle).
     * 
     * @param string $keyValue The value of the key to unlink.
     * @param string $apiName The code name of the API to unlink it from.
     */
    public function unlinkKeyFromApi($keyValue, $apiName)
    {
        try {
            $this->api()->unlinkKey([
                'id' => $apiName,
                'key' => $keyValue,
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Either the key (%s) or the API (%s) was not found.',
                    $keyValue,
                    $apiName
                ), 1478204852, $e);
            }
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * Unlink the specified key from the specified keyring (in ApiAxle).
     * 
     * @param string $keyValue
     * @param string $keyringName
     * @throws NotFoundException
     * @throws \Exception
     */
    public function unlinkKeyFromKeyring($keyValue, $keyringName)
    {
        try {
            $response = $this->keyring()->unlinkKey([
                'id' => $keyringName,
                'key' => $keyValue,
            ]);
            return $this->getDataFromResponse($response);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Either the key (%s) or the keyring (%s) was not found.',
                    $keyValue,
                    $keyringName
                ), 1478205187, $e);
            }
            $this->throwWithBetterMessage($e);
        }
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
            $this->throwWithBetterMessage($e);
        }
    }
    
    /**
     * @param string $keyValue
     * @param array $data
     * @return KeyInfo
     * @throws NotFoundException
     * @throws \Exception
     */
    public function updateKey($keyValue, $data)
    {
        try {
            $data['id'] = $keyValue;
            $response = $this->key()->update($data);
            $responseData = $this->getDataFromResponse($response);
            return new KeyInfo(
                $keyValue,
                $responseData['new']
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Key "%s" not found.',
                    $keyValue
                ), 1477419988, $e);
            }
            $this->throwWithBetterMessage($e);
        }
    }
}
