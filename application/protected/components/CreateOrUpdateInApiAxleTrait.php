<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\models\Event;

trait CreateOrUpdateInApiAxleTrait
{
    abstract public function addError($attribute, $error);
    
    /**
     * Make sure ApiAxle has an up-to-date record for this (if applicable).
     * 
     * @param ApiAxleClient|null $apiAxle (Optional:) The ApiAxleClient to use.
     *     If not provided, one will be created.
     * @return boolean Whether we were successful. If not, check this object's
     *     errors.
     */
    public function createOrUpdateInApiAxle($apiAxle = null)
    {
        if ( ! $this->shouldExistInApiAxle($apiAxle)) {
            return true;
        }
        
        if ($apiAxle === null) {
            $apiAxle = $this->getApiAxleClient();
        }
        
        if ($this->getIsNewRecord()) {
            try {
                $this->createInApiAxle($apiAxle);
                return true;
            } catch (\Exception $e) {
                $this->addError('code', sprintf(
                    'Error creating %s in ApiAxle: %s',
                    $this->getShortClassName(),
                    $e->getMessage()
                ));
                return false;
            }
        }
        
        // If it's not a new record, it should already exist in ApiAxle.
        if ( ! $this->existsInApiAxle($apiAxle)) {
            try {
                $this->createInApiAxle($apiAxle);
                Event::log(sprintf(
                    'Re-added %s (ID %s) to ApiAxle.',
                    $this->getShortClassName(),
                    $this->getPrimaryKey()
                ));
                return true;
            } catch (\Exception $e) {
                $this->addError('code', sprintf(
                    'Error re-adding %s (ID %s) to ApiAxle: %s',
                    $this->getShortClassName(),
                    $this->getPrimaryKey(),
                    $e->getMessage()
                ));
                return false;
            }
        }
        
        try {
            $this->updateInApiAxle($apiAxle);
            return true;
        } catch (\Exception $e) {
            $this->addError('code', sprintf(
                'Error updating %s in ApiAxle: %s',
                $this->getShortClassName(),
                $e->getMessage()
            ));
            return false;
        }
    }
    
    /**
     * Create a record for this in ApiAxle. Throws an exception if unsuccessful
     * for some reason.
     * 
     * @param ApiAxleClient $apiAxle The client to use for interacting with
     *     ApiAxle.
     * @throws \Exception
     */
    abstract protected function createInApiAxle(ApiAxleClient $apiAxle);
    
    /**
     * Indicate whether a record for this exists in ApiAxle.
     * 
     * @param ApiAxleClient $apiAxle The client to use for interacting with
     *     ApiAxle.
     * @return boolean
     */
    abstract protected function existsInApiAxle(ApiAxleClient $apiAxle);
    
    /**
     * Get a client for interacting with ApiAxle.
     * 
     * @return ApiAxleClient
     */
    protected function getApiAxleClient()
    {
        return new ApiAxleClient(\Yii::app()->params['apiaxle']);
    }
    
    abstract public function getIsNewRecord();
    
    abstract public function getPrimaryKey();
    
    protected function getShortClassName()
    {
        return trim(substr(__CLASS__, (int)strrpos(__CLASS__, '\\')), '\\');
    }
    
    /**
     * Whether a record for this should exist in ApiAxle, but doesn't. For
     * example, all Apis should exist in ApiAxle, but only approved Keys
     * should exist in ApiAxle.
     * 
     * @param ApiAxleClient $apiAxle The client to use for interacting with
     *     ApiAxle.
     * @return boolean
     */
    abstract protected function shouldExistInApiAxle(ApiAxleClient $apiAxle);
    
    /**
     * Update the record for this in ApiAxle. Throws an exception if
     * unsuccessful for some reason.
     * 
     * @param ApiAxleClient $apiAxle The client to use for interacting with
     *     ApiAxle.
     * @throws \Exception
     */
    abstract protected function updateInApiAxle(ApiAxleClient $apiAxle);
}
