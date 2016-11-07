<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;

trait RepopulateApiAxleTrait
{
    abstract public function createOrUpdateInApiAxle($apiAxle = null);
    
    abstract public function getErrors($attribute = null);
    
    abstract public function getFriendlyId();
    
    /**
     * NOTE: PHP < 7 does not allow abstract static functions, but for a class
     *       to use this trait it needs to have a model() function... we just
     *       have no way to enforce that until we get to PHP 7.
     * abstract public static function model($className = __CLASS__);
     */
    
    public static function repopulateApiAxle(ApiAxleClient $apiAxle)
    {
        $errorsByModel = [];
        foreach (static::model()->findAll() as $model) {
            /* @var $model \CActiveRecord */
            $result = $model->createOrUpdateInApiAxle($apiAxle);
            if ( ! $result) {
                $errorsByModel[$model->getFriendlyId()] = $model->getErrors();
            }
        }
        return $errorsByModel;
    }
}
