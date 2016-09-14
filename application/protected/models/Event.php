<?php
namespace Sil\DevPortal\models;

class Event extends \EventBase
{
    use \Sil\DevPortal\components\FixRelationsClassPathsTrait;
    use \Sil\DevPortal\components\FormatModelErrorsTrait;
    use \Sil\DevPortal\components\ModelFindByPkTrait;
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        // Overwrite any attribute labels that need manual tweaking.
        return \CMap::mergeArray(parent::attributeLabels(), array(
            'api_id' => 'API',
        ));
    }
    
    /**
     * Log an event to the database. The User ID of the current WebUser (if
     * applicable) will also be recorded as the User who caused the event.
     * 
     * @param string $description Short who-did-what style of summary describing
     *     what happened.<br>
     *     EXAMPLES:<br>
     *     'John Smith invited some_person@example.com to see the "Historical Records" API.'<br>
     *     'Jane Doe updated the cost scheme for the "World Statistics" API.'
     * @param int|null $apiId The ID of the Api (if applicable).
     * @param int|null $keyId The ID of the Key (if applicable).
     * @param int|null $affectedUserId The ID of the affected User (if
     *     applicable, such as an existing User who was invited to see an Api).
     */
    public static function log(
        $description,
        $apiId = null,
        $keyId = null,
        $affectedUserId = null
    ) {
        $event = new Event();
        try {
            $event->attributes = array(
                'api_id' => $apiId,
                'key_id' => $keyId,
                'affected_user_id' => $affectedUserId,
                'description' => $description,
            );
            $event->acting_user_id = \Yii::app()->user->getUserId();
            
            if ($event->save()) {
                \Yii::log($description, \CLogger::LEVEL_INFO);
            } else {
                \Yii::log(sprintf(
                    'Unable to log event: %s. Error: %s%s.',
                    $event->toJson(),
                    PHP_EOL,
                    $event->getErrorsAsFlatTextList()
                ), \CLogger::LEVEL_WARNING);
            }
        } catch (\Exception $exception) {
            \Yii::log(sprintf(
                'Unable to log event: %s. Exception (%s): %s.',
                $event->toJson(),
                $exception->getCode(),
                $exception->getMessage()
            ), \CLogger::LEVEL_WARNING);
        }
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Event the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array(
                /* Always automatically calculate the 'created' value for new
                 * Events, rather than letting the user specify it.  */
                'created',
                'default',
                'value' => new \CDbExpression('NOW()'),
                'setOnEmpty' => false, // setOnEmpty means "only set when empty"
                'on' => 'insert',
            ),
        ), parent::rules());
    }
    
    public function toJson()
    {
        return json_encode(array(
            'description' => $this->description,
            'created' => $this->created,
            'event_id' => $this->event_id,
            'api_id' => $this->api_id,
            'key_id' => $this->key_id,
            'acting_user_id' => $this->acting_user_id,
            'affected_user_id' => $this->affected_user_id,
        ));
    }
}
