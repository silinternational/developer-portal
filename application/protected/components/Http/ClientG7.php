<?php
namespace Sil\DevPortal\components\Http;

/**
 * A simple wrapper class around Guzzle (version 7).
 */
class ClientG7 extends AbstractClient
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
        $guzzleRequest = new \GuzzleHttp\Psr7\Request(
            $method,
            $url,
            $headerParams,
            $body
        );
        $response = $guzzleClient->send($guzzleRequest, [
            'debug' => $debugStream,
            'form_params' => $formParams,
            'headers' => $headerParams,
            'query' => $queryParams,
            'http_errors' => false,
            'verify' => $this->getSslVerifyPeerSetting(),
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
        $responseContentType = end($responseContentTypes) ?: null;
        
        /* Get the raw request that was sent to the API.
         * 
         * NOTE: Just getting the raw request from the Guzzle request
         *       object leaves out several headers (user agent, content
         *       type, content length).
         */
        $requestHeaders = $this->getActualRequestHeadersFromDebugText($debugText);
        $requestBodyFormatter = new \GuzzleHttp\MessageFormatter('{req_body}');
        $requestBody = $requestBodyFormatter->format($guzzleRequest);
        $rawApiRequest = trim($requestHeaders . $requestBody);
        
        return new Response(
            $responseContentType,
            $responseHeaders,
            $responseBody,
            (string)$guzzleRequest->getUri(),
            $rawApiRequest,
            $debugText
        );
    }
}
