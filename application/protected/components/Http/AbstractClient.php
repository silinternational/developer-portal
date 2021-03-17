<?php
namespace Sil\DevPortal\components\Http;

use function Symfony\Component\String\u;

/**
 * The base class for our Guzzle wrapper classes.
 */
abstract class AbstractClient
{
    protected function getActualRequestHeadersFromDebugText($debugText)
    {
        $fullRequest = '';
        $lines = explode("\n", $debugText);
        $line = array_shift($lines);
        
        // Find the beginning of the request section.
        while ($line !== null) {
            if (u($line)->startsWith('> ')) {
                $fullRequest .= substr($line, 2);
                break;
            }
            $line = array_shift($lines);
        }
        $line = array_shift($lines);
        
        // Collect lines until the end of the request section.
        while ($line !== null) {
            if (u($line)->startsWith('* ') || u($line)->startsWith('< ')) {
                break;
            }
            $fullRequest .= $line;
            $line = array_shift($lines);
        }
        
        return $fullRequest;
    }
    
    protected function getSslVerifyPeerSetting()
    {
        return \Yii::app()->params['apiaxle']['ssl_verifypeer'];
    }
    
    /**
     * Send the specified request and get the response.
     *
     * @param string $method The request method to use (GET, POST, etc.).
     * @param string $url The URL to request.
     * @param ParamsCollection|null $paramsCollection (Optional:) Any custom
     *     parameters to include.
     * @param string|null $rawBody (Optional:) A raw request body. If provided,
     *     any "form" params are ignored. (Has no effect for "GET" requests.)
     * @return Response An object representing the response.
     */
    public function request($method, $url, $paramsCollection = null, $rawBody = null)
    {
        if ($paramsCollection === null) {
            $paramsCollection = new ParamsCollection();
        }
        $paramsForm = $paramsCollection->getFormParams();
        $paramsHeader = $paramsCollection->getHeaderParams();
        $paramsQuery = $paramsCollection->getQueryParams();
        
        // If GET request, merge paramsForm into paramsQuery.
        if ($method == 'GET') {
            $paramsQuery = \CMap::mergeArray($paramsQuery, $paramsForm);
            $paramsForm = [];
            $requestBody = null;
        } elseif (! empty($rawBody)) {
            $requestBody = $rawBody;
            $paramsForm = [];
        } else {
            $requestBody = http_build_query($paramsForm);
        }
        
        // Append the query string parameters to the URL.
        if ( ! empty($paramsQuery)) {
            list($urlMinusFragment, ) = explode('#', $url);
            $urlMinusFragment .= u($url)->containsAny('?') ? '&' : '?';
            $paramsQueryPairs = [];
            foreach ($paramsQuery as $name => $value) {
                $paramsQueryPairs[] = rawurlencode($name) . '=' . rawurlencode($value);
            }
            $urlMinusFragment .= implode('&', $paramsQueryPairs);
            $url = $urlMinusFragment;
        }
        
        return $this->sendGuzzleRequest(
            $method,
            $url,
            $paramsForm,
            $paramsHeader,
            $paramsQuery,
            $requestBody
        );
    }
    
    /**
     * Have Guzzle actually send the specified request and get the response.
     * 
     * @param string $method The request method to use (GET, POST, etc.).
     * @param string $url The URL to request.
     * @param array $formParams (Optional:) The array of form parameters (if
     *     any), as key => value pairs.
     * @param array $headerParams (Optional:) The array of headers (if any),
     *     as key => value pairs.
     * @param array $queryParams (Optional:) The array of query string
     *     parameters (if any), as key => value pairs.
     * @param string|null $body (Optional:) The request body.
     * @return Response An object representing the response.
     */
    abstract protected function sendGuzzleRequest(
        $method,
        $url,
        $formParams = [],
        $headerParams = [],
        $queryParams = [],
        $body = null
    );
}
