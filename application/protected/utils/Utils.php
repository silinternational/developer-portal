<?php

class Utils {

    /* 
     * @param string that matches the name of a model.
     * @param int that should match the pk of an object
     * @throws 404 if the no match is found.
     * @return mixed instance of that model with the matching pk.
     * 
     */
    public static function findPkOr404($model, $pk) {
        $object = $model::model()->findByPk($pk);
        if (!$object) {
            throw new CHttpException(404,'invalid request'); 
        }      
        return $object;
    }          

    /**
     * Retrieve the value at the place specified in the given data array by the
     * given list of keys.
     * 
     * @param array $data The data array.
     * @param string[] $keys The list of keys specifying which piece of data to
     *     get.
     * @param mixed $default (Optional:) The default value to return if no such
     *     key exists. Defaults to null.
     * @return mixed The value at the specified place in the data array.
     * @throws InvalidArgumentException
     */
    public static function getNestedArrayValue($data, $keys, $default = null)
    {
        if ( ! is_array($data)) {
            throw new InvalidArgumentException(
                'The first parameter must be an array.',
                1461178154
            );
        }
        
        if ( ! is_array($keys)) {
            throw new InvalidArgumentException(
                'The list of keys must be an array.',
                1461178192
            );
        }
        
        /* Keep narrowing down to the desired part of the given array until we
         * find the desired entry (or discover that it doesn't exist).  */
        while (count($keys) > 0) {
            $key = array_shift($keys);
            
            if (array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        
        return $data;
    }
    
    /**
     * @param $_GET variable.
     * @param string that matches the name of a model.
     * @param string $get_param (default 'id') that matches the parameter in the url
     * @throws 404 if the GET doesn't have the parameter, 
     *         or that param value doesn't match a Pk of that model.
     * @return mixed the instance of that model with the matching pk.
     * Note that the param's value will be cast to int.
     */    
    public static function getPkOr404($get, $model, $get_param='id') {
        if (!isset($get[$get_param])) {
            throw new CHttpException(404,'invalid request');           
        }
        $id = (int)$get[$get_param];
        $object = $model::model()->findByPk($id);

        if (!$object) {
            throw new CHttpException(404,'invalid request'); 
        }      
        return $object;
    }      
    
    /**
     * Get random string of specified length. It is based on php function uniqid()
     * @param int $length [=32] the length of random string. Should be a positive integer.
     * @param string $salt salt string. default ''. It's quite safe to use default value,
     * as the latest function return will be stored and be part of the eventual salt, which means, empty $salt doesn't result in empty salt.
     * @throws InvalidArgumentException
     * @return string
     */
    public static function getRandStr($length = 32, $salt = '') {
        static $internal_salt = 'uaypiq2joi2j4fio2j24fjw5egtsrhsg5a4f;8kjcoiyad'; // this will be part of next salt.
        if(defined('APPLICATION_ENV') && APPLICATION_ENV == 'testing'){
            $str = 'test-';
        } elseif(defined('APPLICATION_ENV') && APPLICATION_ENV == 'development'){
           $str = 'dev-'; 
        } else {
            $str = '';
        }
        
        if ($length <= 0 || !is_numeric($length)) {
            throw new InvalidArgumentException('Parameter 1 should be a positive integer.');
        }

Rand_Gen_Loop:

        $internal_salt .= mt_rand(1, 10000000);
        $str .= $internal_salt = md5(uniqid($salt . $internal_salt, true)); // the string will replace $internal_salt.
        
        if(strlen($str) == $length){
            return $str;
        } elseif(strlen($str) > $length){
            return substr($str, 0, $length);
        } else {
            GOTO Rand_Gen_Loop;
        }
        
    }
    
    /**
     * Convert the given time string to a date in the friendly format (as
     * defined in our config settings). Unless it specifies a timezone (or is a
     * timestamp), it is assumed to be in UTC time. For more information, see
     * http://php.net/manual/en/datetime.construct.php
     * 
     * @param string $timeStr A string representing some point in time.
     * @return string A friendly display of that point in time.
     */
    public static function getFriendlyDate($timeStr)
    {
        // Create a DateTime object from the given string.
        $utcTimezone = new DateTimeZone('UTC');
        $dateTime = new DateTime($timeStr, $utcTimezone);
        
        // Convert it to the desired timezone.
        $targetTimezone = new DateTimeZone(date_default_timezone_get());
        $dateTime->setTimezone($targetTimezone);
        
        // Get the date format string to use.
        $friendlyDateFormat = 'F j, Y, g:ia';
        if (isset(Yii::app()->params['friendlyDateFormat'])) {
            $friendlyDateFormat = Yii::app()->params['friendlyDateFormat'];
        }   
        
        // Format the given date/time and return the resulting string.
        return $dateTime->format($friendlyDateFormat);
    }
    
    /**
     * Convert the given time string to a date in the short format (as defined
     * in our config settings). Unless it specifies a timezone (or is a
     * timestamp), it is assumed to be in UTC time. For more information, see
     * http://php.net/manual/en/datetime.construct.php
     * 
     * @param string $timeStr A string representing some point in time.
     * @return string A short display of that date.
     */
    public static function getShortDate($timeStr)
    {
        // Create a DateTime object from the given string.
        $utcTimezone = new DateTimeZone('UTC');
        $dateTime = new DateTime($timeStr, $utcTimezone);
        
        // Convert it to the desired timezone.
        $targetTimezone = new DateTimeZone(date_default_timezone_get());
        $dateTime->setTimezone($targetTimezone);
        
        // Get the date format string to use.
        $shortDateFormat = 'n/j/y';
        if (isset(Yii::app()->params['shortDateFormat'])) {
            $shortDateFormat = Yii::app()->params['shortDateFormat'];
        }
        
        // Format the given date/time and return the resulting string.
        return $dateTime->format($shortDateFormat);
    }
    
    /**
     * Convert the given time string to a date in the short date-with-time
     * format (as defined in our config settings). Unless it specifies a
     * timezone (or is a timestamp), it is assumed to be in UTC time. For more
     * information, see http://php.net/manual/en/datetime.construct.php
     * 
     * @param string $timeStr A string representing some point in time.
     * @return string A short display of that date-time.
     */
    public static function getShortDateTime($timeStr)
    {
        // Create a DateTime object from the given string.
        $utcTimezone = new DateTimeZone('UTC');
        $dateTime = new DateTime($timeStr, $utcTimezone);
        
        // Convert it to the desired timezone.
        $targetTimezone = new DateTimeZone(date_default_timezone_get());
        $dateTime->setTimezone($targetTimezone);
        
        // Get the date format string to use.
        $shortDateFormat = 'n/j/y';
        if (isset(Yii::app()->params['shortDateTimeFormat'])) {
            $shortDateFormat = Yii::app()->params['shortDateTimeFormat'];
        }
        
        // Format the given date/time and return the resulting string.
        return $dateTime->format($shortDateFormat);
    }
    
    /**
     * Function loads the protected/data/version.txt file and parses out
     * application version number and build date and returns as an indexed
     * array
     * 
     * $results = array(
     *    'version' => 'vx.x.x',
     *    'build'   => 'Thurs, 09 Jan 2014  08:59:23 -0600',
     * );
     * 
     * @return array
     */
    public static function getApplicationVersion()
    {
        $results = array(
            'version' => 'Error',
            'build' => 'Error',
        );
        if(file_exists(__DIR__.'/../data/version.txt')){
            $line = file_get_contents(__DIR__.'/../data/version.txt');
            if($line && $line != ''){
                $info = explode('|', $line);
                if(is_array($info)){
                    $results['version'] = $info[0];
                    $results['build'] = $info[1];
                }
            }
        }
        
        return $results;
    }
    
    
    public static function getMailer()
    {
        $mail = new YiiMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->IsHTML(true);
        $mail->Host = Yii::app()->params['smtp']['host'];
        $mail->Username = Yii::app()->params['smtp']['user'];
        $mail->Password = Yii::app()->params['smtp']['pass'];
        $mail->From = Yii::app()->params['smtp']['fromEmail'];
        $mail->FromName = Yii::app()->params['smtp']['fromName'];
        if (isset(Yii::app()->params['mail']['bcc'])) {
            $mail->setBcc(Yii::app()->params['mail']['bcc']);
        }
        
        return $mail;
    }

    public static function pretty_json($json) {
        return json_encode(json_decode($json), JSON_PRETTY_PRINT);
    }
}
