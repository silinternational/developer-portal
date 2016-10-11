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
    
    public function __construct($data = [])
    {
        $this->body = isset($data['body']) ? $data['body'] : null;
        $this->contentType = isset($data['contentType']) ? $data['contentType'] : null;
        $this->debugText = isset($data['debugText']) ? $data['debugText'] : null;
        $this->headers = isset($data['headers']) ? $data['headers'] : [];
        $this->rawRequest = isset($data['rawRequest']) ? $data['rawRequest'] : null;
        $this->requestedUrl = isset($data['requestedUrl']) ? $data['requestedUrl'] : null;
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
