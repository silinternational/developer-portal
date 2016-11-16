<?php
namespace Sil\DevPortal\components\Http;

/**
 * A collection of parameters (of various types) for an HTTP call.
 */
class ParamsCollection
{
    const TYPE_FORM = 'form';
    const TYPE_HEADER = 'header';
    const TYPE_QUERY = 'query';
    
    protected $data;
    
    public function __construct() {
        $this->data = [
            self::TYPE_FORM => [],
            self::TYPE_HEADER => [],
            self::TYPE_QUERY => [],
        ];
    }
    
    public function addParam($type, $name, $value)
    {
        if ( ! self::isValidType($type)) {
            throw new \InvalidArgumentException(sprintf(
                'The given type (%s) is not a valid option.',
                $type
            ), 1476281936);
        }
        
        if (empty($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The given name (%s) is insufficient.',
                var_export($name, true)
            ), 1476281937);
        }
        
        $this->data[$type][$name] = $value;
    }
    
    public function getFormParams()
    {
        return $this->data[self::TYPE_FORM];
    }
    
    public function getHeaderParams()
    {
        return $this->data[self::TYPE_HEADER];
    }
    
    public function getQueryParams()
    {
        return $this->data[self::TYPE_QUERY];
    }
    
    public static function isValidType($type)
    {
        return in_array($type, [
            self::TYPE_FORM,
            self::TYPE_HEADER,
            self::TYPE_QUERY,
        ]);
    }
}
