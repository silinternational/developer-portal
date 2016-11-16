<?php
namespace Sil\DevPortal\components\ApiAxle;

class ItemInfo
{
    protected $data;
    protected $name;
    
    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }
    
    public function __toString()
    {
        return var_export([
            'name' => $this->getName(),
            'data' => $this->getData()
        ], true);
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Create (and return) an instance of the current class from the given
     * information.
     * 
     * @param ItemInfo $itemInfo The information to use.
     * @return \static
     */
    public static function from($itemInfo)
    {
        return new static($itemInfo->getName(), $itemInfo->getData());
    }
}
