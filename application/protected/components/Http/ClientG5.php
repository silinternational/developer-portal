<?php
namespace Sil\DevPortal\components\Http;

use \GuzzleHttp\Message\Response as GuzzleResponse;

/**
 * A simple wrapper class around Guzzle (version 5).
 */
class ClientG5 extends AbstractClient
{
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
    protected function sendGuzzleRequest(
        $method,
        $url,
        $formParams = [],
        $headerParams = [],
        $queryParams = [],
        $body = null
    ) {
        $guzzleClient = new \GuzzleHttp\Client();
        $debugStream = fopen('php://temp', 'w+');
        $guzzleRequest = $guzzleClient->createRequest($method, $url, [
            'debug' => $debugStream,
            'headers' => $headerParams,
            'body' => empty($formParams) ? $body : $formParams,
            'query' => $queryParams,
            'exceptions' => false,
            'verify' => \Yii::app()->params['apiaxle']['ssl_verifypeer'],
        ]);
        $response = $guzzleClient->send($guzzleRequest);
        rewind($debugStream);
        $debugText = stream_get_contents($debugStream);
        fclose($debugStream);
        
        // Get the response headers and body.
        $responseHeaders = GuzzleResponse::getStartLineAndHeaders($response);
        $responseBody = (string)$response->getBody();
        
        // Get the content type.
        $responseContentType = $response->getHeader('Content-Type');
        
        /* Get the raw request that was sent to the API.
         * 
         * NOTE: Just getting the raw request from the Guzzle request
         *       object leaves out several headers (user agent, content
         *       type, content length).
         */
        $requestHeaders = $this->getActualRequestHeadersFromDebugText($debugText);
        $requestBody = (string)$guzzleRequest->getBody();
        $rawApiRequest = trim($requestHeaders . $requestBody);
        
        return new Response(
            $responseContentType,
            $responseHeaders,
            $responseBody,
            $guzzleRequest->getUrl(),
            $rawApiRequest,
            $debugText
        );
    }
}
