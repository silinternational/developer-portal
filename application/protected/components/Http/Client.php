<?php
namespace Sil\DevPortal\components\Http;

use Stringy\StaticStringy as SS;

/**
 * A simple wrapper class around whichever version of Guzzle we are actually
 * using behind the scenes.
 */
class Client
{
    protected static function getActualRequestHeadersFromDebugText($debugText)
    {
        $fullRequest = '';
        $lines = explode("\n", $debugText);
        $line = array_shift($lines);
        
        // Find the beginning of the request section.
        while ($line !== null) {
            if (SS::startsWith($line, '> ')) {
                $fullRequest .= substr($line, 2);
                break;
            }
            $line = array_shift($lines);
        }
        $line = array_shift($lines);
        
        // Collect lines until the end of the request section.
        while ($line !== null) {
            if (SS::startsWith($line, '* ') || SS::startsWith($line, '< ')) {
                break;
            }
            $fullRequest .= $line;
            $line = array_shift($lines);
        }
        
        return $fullRequest;
    }
    
    /**
     * Send the specified request and get the response.
     * 
     * @param string $method The request method to use (GET, POST, etc.).
     * @param string $url The URL to request.
     * @param array $params (Optional:) Any custom parameters to include,
     *     each of which should have a 'name', 'value', and 'type' key (where
     *     type is either 'form', 'header', or 'query').
     * @return Response An object representing the response.
     */
    public static function request($method, $url, $params = [])
    {
        // Create a single dimension parameter array from parameters
        // submitted, divided by form and header parameters.
        $paramsForm = [];
        $paramsHeader = [];
        $paramsQuery = [];
        if ($params && is_array($params)) {
            foreach ($params as $param) {
                if (isset($param['name']) && isset($param['value']) 
                        && $param['name'] != '' && $param['value'] != ''
                        && !is_null($param['name']) && !is_null($param['value'])) {
                    
                    // Determine if parameter is supposed to be form based or header
                    if (isset($param['type']) && $param['type'] == 'form') {
                        $paramsForm[$param['name']] = $param['value'];
                    } elseif (isset($param['type']) && $param['type'] == 'header') {
                        $paramsHeader[$param['name']] = $param['value'];
                    } elseif (isset($param['type']) && $param['type'] == 'query') {
                        $paramsQuery[$param['name']] = $param['value'];
                    }
                }
            }
        }
        
        // If GET request, merge paramsForm into paramsQuery.
        if ($method == 'GET') {
            $paramsQuery = \CMap::mergeArray($paramsQuery, $paramsForm);
            $paramsForm = null;
            $requestBody = null;
        } else {
            $requestBody = http_build_query($paramsForm);
        }
        
        // Append the query string parameters to the URL.
        if ( ! empty($paramsQuery)) {
            list($urlMinusFragment, ) = explode('#', $url);
            $urlMinusFragment .= SS::contains($url, '?') ? '&' : '?';
            $paramsQueryPairs = [];
            foreach ($paramsQuery as $name => $value) {
                $paramsQueryPairs[] = rawurlencode($name) . '=' . rawurlencode($value);
            }
            $urlMinusFragment .= implode('&', $paramsQueryPairs);
            $url = $urlMinusFragment;
        }
        
        $guzzleClient = new \GuzzleHttp\Client();
        $debugStream = fopen('php://temp', 'w+');
        $guzzleRequest = new \GuzzleHttp\Psr7\Request(
            $method,
            $url,
            $paramsHeader,
            $requestBody
        );
        $response = $guzzleClient->send($guzzleRequest, [
            'debug' => $debugStream,
            'form_params' => $paramsForm,
            'headers' => $paramsHeader,
            'query' => $paramsQuery,
            'http_errors' => false,
            'verify' => \Yii::app()->params['apiaxle']['ssl_verifypeer'],
        ]);
        rewind($debugStream);
        $debugText = stream_get_contents($debugStream);
        fclose($debugStream);
        
        // Get the response headers and body.
        $responseHeadersFormatter = new \GuzzleHttp\MessageFormatter('{res_headers}');
        $responseHeaders = $responseHeadersFormatter->format($guzzleRequest, $response);
        $responseBodyFormatter = new \GuzzleHttp\MessageFormatter('{res_body}');
        $responseBody = $responseBodyFormatter->format($guzzleRequest, $response);
        
        // Get the content type.
        $responseContentTypes = $response->getHeader('Content-Type');
        $responseContentType = end($responseContentTypes);
        
        /* Get the raw request that was sent to the API.
         * 
         * NOTE: Just getting the raw request from the Guzzle request
         *       object leaves out several headers (user agent, content
         *       type, content length).
         */
        $requestHeaders = self::getActualRequestHeadersFromDebugText($debugText);
        $requestBodyFormatter = new \GuzzleHttp\MessageFormatter('{req_body}');
        $requestBody = $requestBodyFormatter->format($guzzleRequest);
        $rawApiRequest = trim($requestHeaders . $requestBody);
        
        return new Response(
            $responseContentType,
            $responseHeaders,
            $responseBody,
            $guzzleRequest->getUri(),
            $rawApiRequest,
            $debugText
        );
    }
}
