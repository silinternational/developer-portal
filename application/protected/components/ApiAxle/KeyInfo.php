<?php
namespace Sil\DevPortal\components\ApiAxle;

class KeyInfo extends ItemInfo
{
    public function __construct($keyValue, $data)
    {
        parent::__construct($keyValue, $data);
    }
    
    public function getKeyValue()
    {
        return $this->getName();
    }
}
