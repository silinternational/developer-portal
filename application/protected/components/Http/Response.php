<?php
namespace Sil\DevPortal\components\Http;

/**
 * A simple wrapper class for handling responses to HTTP requests.
 */
class Response
{
    protected $body;
    protected $contentType;
    protected $debugText;
    protected $headers;
    protected $rawRequest;
    protected $requestedUrl;
    
    /**
     * Create a Response object.
     * 
     * @param string|null $contentType The content type of the response.
     * @param string|null $headers The raw response headers.
     * @param string|null $body The raw response body.
     * @param string|null $requestedUrl The requested URL.
     * @param string|null $rawRequest The full raw request.
     * @param string|null $debugText Any related debug text.
     */
    public function __construct(
        $contentType = null,
        $headers = null,
        $body = null,
        $requestedUrl = null,
        $rawRequest = null,
        $debugText = null
    ) {
        $this->body = $body;
        $this->contentType = $contentType;
        $this->debugText = $debugText;
        $this->headers = $headers;
        $this->rawRequest = $rawRequest;
        $this->requestedUrl = $requestedUrl;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function getContentType()
    {
        return $this->contentType;
    }
    
    public function getDebugText()
    {
        return $this->debugText;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Attempt to pretty print the response body. If not successful, the body
     * will be returned as-is.
     * 
     * @return string
     */
    public function getPrettyPrintedBody()
    {
        $responseBody = $this->body;
        if ($this->isXml()) {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            if ($dom->loadXML($responseBody)) {
                $asString = $dom->saveXML();
                if ($asString) {
                    $responseBody = $asString;
                }
            }
        } elseif ($this->isJson()) {
            $responseBody = \Utils::pretty_json($responseBody);
        }
        return $responseBody;
    }
    
    public function getRawRequest()
    {
        return $this->rawRequest;
    }
    
    public function getRequestedUrl()
    {
        return $this->requestedUrl;
    }
    
    public function isJson()
    {
        return (substr_count($this->contentType, 'json') > 0);
    }
    
    public function isXml()
    {
        return (substr_count($this->contentType, 'xml') > 0);
    }
}
