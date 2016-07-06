<?php

use ApiAxle\Api\Api as AxleApi;

/**
 * Additional model relations (defined here, not in base class):
 * @property int $approvedKeyCount
 * @property int $pendingKeyCount
 */
class Api extends ApiBase
{
    use Sil\DevPortal\components\ModelFindByPkTrait;
    
    CONST APPROVAL_TYPE_AUTO = 'auto';
    CONST APPROVAL_TYPE_OWNER = 'owner';
    
    CONST PROTOCOL_HTTP = 'http';
    CONST PROTOCOL_HTTPS = 'https';
    
    CONST STRICT_SSL_TRUE = 1;
    CONST STRICT_SSL_FALSE = 0;
    
    CONST REGEX_ENDPOINT = '/^(?=.{1,255}$)[0-9A-Za-z](?:(?:[0-9A-Za-z]|-){0,61}[0-9A-Za-z])?(?:\.[0-9A-Za-z](?:(?:[0-9A-Za-z]|-){0,61}[0-9A-Za-z])?)*\.?$/';
    CONST REGEX_PATH = '/^\/[a-zA-Z0-9\-\.\/_]{0,}$/';
    
    CONST VISIBILITY_INVITATION = 'invitation';
    CONST VISIBILITY_PUBLIC = 'public';
    
    public function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        \Event::log(sprintf(
            'The "%s" API (%s, ID %s) was deleted%s.',
            $this->display_name,
            $this->code,
            $this->api_id,
            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
        ));
    }
    
    public function afterSave()
    {
        parent::afterSave();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        
        // If this is a new API...
        if ($this->isNewRecord) {
            
            \Event::log(sprintf(
                'The "%s" API (%s, ID %s) was created%s.',
                $this->display_name,
                $this->code,
                $this->api_id,
                (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
            ), $this->api_id);
            
            // If we are NOT in an environment where we should send email
            // notifications, skip the rest of this.
            if (\Yii::app()->params['mail'] === false) {
                return;
            }

            // If we do NOT have an adminEmail address in the config params,
            // skip the rest of this.
            if ( ! isset(\Yii::app()->params['adminEmail'])) {
                return;
            }

            // Try to send a notification email.
            $mailer = Utils::getMailer();
            $mailer->setView('api-added');
            $mailer->setTo(\Yii::app()->params['adminEmail']);
            $mailer->setSubject(sprintf(
                'An API was added: %s',
                $this->display_name
            ));
            if (isset(\Yii::app()->params['mail']['bcc'])) {
                $mailer->setBcc(\Yii::app()->params['mail']['bcc']);
            }
            $mailer->setData(array(
                'api' => $this,
                'addedByUser' => \Yii::app()->user->user,
            ));

            // If unable to send the email, record the failure.
            if ( ! $mailer->send()) {
                \Yii::log(
                    'Unable to send API-added email: '
                    . $mailer->ErrorInfo,
                    CLogger::LEVEL_WARNING
                );
            }
        } else {
            
            \Event::log(sprintf(
                'The "%s" API (%s, ID %s) was updated%s.',
                $this->display_name,
                $this->code,
                $this->api_id,
                (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
            ), $this->api_id);
            
            try {
                $this->updateKeysRateLimitsToMatch();
            } catch (\Exception $e) {
                \Yii::log(
                    "Unable to update this Api's keys' rate limits to match "
                    . "its current rate limits: "
                    . $e->getMessage(),
                    CLogger::LEVEL_WARNING
                );
            }
        }
    }
    
    /**
     * Get the user-friendly description of this Api's approval type.
     * 
     * @return string|null The description of the approval type (if available).
     */
    public function getApprovalTypeDescription()
    {
        // Get the descriptions of the various approval types.
        $approvalTypeDescriptions = self::getApprovalTypes();
        
        // Return the description for this Api's approval type (if set).
        if ($this->approval_type === null) {
            return null;
        } elseif ( ! isset($approvalTypeDescriptions[$this->approval_type])) {
            return 'UNKNOWN APPROVAL TYPE';
        } else {
            return $approvalTypeDescriptions[$this->approval_type];
        }
    }
    
    /**
     * Get the list of approval type contants along with the user-friendly
     * versions of them (for use in drop-down lists, etc.).
     * @return array<string,string> An array where each key is an approval type
     *     constant and each value is the corresponding user-friendly version
     *     of it.
     */
    public static function getApprovalTypes()
    {
        return array(
            self::APPROVAL_TYPE_AUTO => 'Automatically Approved',
            self::APPROVAL_TYPE_OWNER => 'API Owner Approved',
        );
    }
    
    public static function getProtocols()
    {
        return array(
            self::PROTOCOL_HTTP => 'HTTP',
            self::PROTOCOL_HTTPS => 'HTTPS'
        );
    }
    
    public static function getStrictSsls()
    {
        return array(
            self::STRICT_SSL_TRUE => 'True',
            self::STRICT_SSL_FALSE => 'False',
        );
    }
    
    /**
     * @return array<string,string> Customized attribute labels (name => label),
     *     overriding any that did not autogenerate to the desired value in the
     *     base class.
     */
    public function attributeLabels() {
        return \CMap::mergeArray(parent::attributeLabels(), array(
            'queries_second' => 'Queries per Second',
            'queries_day' => 'Queries per Day',
            'protocol' => 'Endpoint Protocol',
            'strict_ssl' => 'Endpoint Strict SSL',
            'endpoint_timeout' => 'Endpoint Timeout',
        ));
    }
    
    /**
     * Generate the HTML for the active key count badge.
     * 
     * @param string|null $hoverTitle (Optional:) Text to show on hover (if
     *     any).
     * @param string|null $linkTarget (Optional:) If desired, the URL to make
     *     this badge a hyperlink to.
     * @return string The resulting HTML.
     */
    public function getActiveKeyCountBadgeHtml(
        $hoverTitle = null,
        $linkTarget = null
    ) {
        // Get the active key count (to avoid retrieving it multiple times).
        $count = $this->approvedKeyCount;
        
        // Generate and return the HTML for a badge, highlighting it if the
        // pending key count is non-zero.
        return self::generateBadgeHtml(
            $count,
            ($count ? 'badge-info' : null),
            $hoverTitle,
            $linkTarget
        );
    }
    
    /**
     * Get the apiProxyDomain config data. Throws an exception if it has not
     * been set.
     * 
     * NOTE: This is primarily defined in order to make mocking it in unit tests
     *       easier, which is also why it is NOT a static function (which would
     *       prevent mocking).
     * 
     * @return string The API proxy domain.
     * @throws Exception
     */
    public function getApiProxyDomain()
    {
        if (isset(\Yii::app()->params['apiProxyDomain'])) {
            return \Yii::app()->params['apiProxyDomain'];
        } else {
            throw new Exception(
                'The API proxy domain has not been defined in the config data.',
                1420751158
            );
        }
    }
    
    /**
     * Get the apiProxyProtocol config data, returning a default value if that
     * has not been set.
     * 
     * NOTE: This is primarily defined in order to make mocking it in unit tests
     *       easier, which is also why it is NOT a static function (which would
     *       prevent mocking).
     * 
     * @return string The API proxy protocol.
     * @throws Exception
     */
    public function getApiProxyProtocol()
    {
        if (isset(\Yii::app()->params['apiProxyProtocol'])) {
            return \Yii::app()->params['apiProxyProtocol'];
        } else {
            return 'https';
        }
    }
    
    /**
     * Generate the HTML for the pending key count badge.
     * 
     * @param string|null $hoverTitle (Optional:) Text to show on hover (if
     *     any).
     * @return string The resulting HTML.
     */
    public function getPendingKeyCountBadgeHtml(
        $hoverTitle = null,
        $linkTarget = null
    ) {
        // Get the pending key count (to avoid retrieving it multiple times).
        $count = $this->pendingKeyCount;
        
        // Generate and return the HTML for a badge, highlighting it if the
        // pending key count is non-zero.
        return self::generateBadgeHtml(
            $count,
            ($count ? 'badge-important' : null),
            $hoverTitle,
            $linkTarget
        );
    }
    
    /**
     * Get the public URL for this API.
     * 
     * @return string The (absolute) URL.
     */
    public function getPublicUrl()
    {
        return sprintf(
            '%s://%s%s/',
            $this->getApiProxyProtocol(),
            $this->code,
            $this->getApiProxyDomain()
        );
    }
    
    /**
     * Get a list containing the email address (if available) of each user that
     * has a key to this Api.
     * 
     * @return array The list of email addresses (without duplicates).
     */
    public function getEmailAddressesOfUsersWithActiveKeys()
    {
        $emailAddresses = array();
        foreach ($this->keys as $key) {
            if ($key->user && $key->user->email) {
                if ( ! in_array($key->user->email, $emailAddresses)) {
                    $emailAddresses[] = $key->user->email;
                }
            }
        }
        return $emailAddresses;
    }
    
    /**
     * Get the internal endpoint for this Api.
     * 
     * @return string The (absolute) URL.
     */
    public function getInternalApiEndpoint()
    {
        return sprintf(
            '%s://%s%s',
            $this->protocol,
            $this->endpoint,
            ($this->default_path ?: '/')
        );
    }
    
    /**
     * Get the public URL for this API as an HTML string that adds an HTML
     * element around the Api's code, with the given CSS class.
     * 
     * @param string $cssClass The CSS class to use for the Api's code. Defaults
     *     to 'bold'.
     * @return string HTML of the (absolute) URL.
     */
    public function getStyledPublicUrlHtml($cssClass = 'bold')
    {
        return sprintf(
            '%s://<span class="%s">%s</span>%s/',
            CHtml::encode($this->getApiProxyProtocol()),
            CHtml::encode($cssClass),
            CHtml::encode($this->code),
            CHtml::encode($this->getApiProxyDomain())
        );
    }
    
    /**
     * Get usage data for this Api.
     * 
     * @param string $granularity The time interval (e.g. - 'second', 'minute',
     *     'hour', 'day') by which the data should be grouped.
     * @param boolean $includeCurrentInterval (Optional:) Whether to include the
     *     current time interval, even though we only have incomplete data for
     *     it. Defaults to true.
     * @return array A hash with timestamps (in $granularity intervals) as keys,
     *     and arrays of http_response_code(or error_name) => num_hits as
     *     values.  
     *     EXAMPLE: 
     *     <pre>
     *     array(
     *         1416340920 => array(200 => 2),
     *         1416340980 => array(200 => 4),
     *         1416341520 => array(200 => 1),
     *     )
     *     </pre>
     */
    public function getUsage(
        $granularity = 'minute',
        $includeCurrentInterval = true
    ) {
        // Get the ApiAxle Api object for this Api model.
        $axleApi = new AxleApi(Yii::app()->params['apiaxle'], $this->code);
        
        // Get the starting timestamp for the data we care about.
        $timeStart = \UsageStats::getTimeStart(
            $granularity,
            $includeCurrentInterval
        );
        
        // Retrieve the stats from ApiAxle.
        $axleStats = $axleApi->getStats($timeStart, false, $granularity, 'false');
        
        // Reformat the data for easier use.
        $dataByCategory = array();
        foreach ($axleStats as $category => $categoryStats) {
            $tempCategoryData = array();
            foreach ($categoryStats as $responseCode => $timeData) {
                if (count($timeData) <= 0) {
                    continue;
                }
                $tempResponseCodeData = array();
                foreach ($timeData as $timestamp => $numHits) {
                    $tempResponseCodeData[$timestamp] = $numHits;
                }
                if (count($tempResponseCodeData) > 0) {
                    $tempCategoryData[$responseCode] = $tempResponseCodeData;
                }
            }
            $dataByCategory[$category] = $tempCategoryData;
        }
        
        // Sum the cached and uncached hits, then sum that with the errors.
        $successfulUsage = UsageStats::combineUsageCategoryArrays(
            $dataByCategory['uncached'],
            $dataByCategory['cached']
        );
        $usage = UsageStats::combineUsageCategoryArrays(
            $successfulUsage,
            $dataByCategory['error']
        );
        
        // Return the resulting data.
        return $usage;
    }
    
    /**
     * Get the user-friendly description of this Api's visibility.
     * 
     * @return string|null The description of the visibility (if available).
     */
    public function getVisibilityDescription()
    {
        // Get the descriptions of the various access types.
        $visibilityDescriptions = self::getVisibilityDescriptions();
        
        // Return the description for this Api's visibility.
        if ( ! isset($visibilityDescriptions[$this->visibility])) {
            return 'UNKNOWN VISIBILITY';
        } else {
            return $visibilityDescriptions[$this->visibility];
        }
    }
    
    /**
     * Get the list of visibility descriptions, indexed by the visibility
     * constant values.
     * 
     * @return array<string,string>
     */
    public static function getVisibilityDescriptions()
    {
        return array(
            self::VISIBILITY_INVITATION => 'By Invitation Only',
            self::VISIBILITY_PUBLIC => 'Publicly Available',
        );
    }
    
    public function rules()
    {
        return \CMap::mergeArray(array(
            array('code', 'unique'),
            array(
                'code',
                'match',
                'allowEmpty' => false,
                'pattern' => '/^([a-z0-9]{1}[a-z0-9\-]{1,}[a-z0-9]{1})$/',
                'message' => 'The API code must only be (lowercase) letters '
                . 'and numbers. It may contain hyphens, but not at the '
                . 'beginning or end.',
            ),
            array('owner_id', 'validateOwnerId'),
            array(
                'endpoint',
                'match',
                'allowEmpty' => false,
                'pattern' => self::REGEX_ENDPOINT,
                'message' => 'Endpoint must be the domain only, no protocol or '
                . 'path should be included. (ex: sub.domain.com)',
            ),
            array(
                'default_path',
                'match',
                'allowEmpty' => true,
                'pattern' => self::REGEX_PATH,
                'message' => 'Default Path must begin with a / and should not '
                . 'include any query string parameters. (ex: /example/path)',
            ),
            array('endpoint', 'isUniqueEndpointDefaultPathCombo'),
            array(
                'updated',
                'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'update',
            ),
            array(
                'created,updated',
                'default',
                'value' => new CDbExpression('NOW()'),
                'setOnEmpty' => true,
                'on' => 'insert',
            ),
            array('code', 'unsafe', 'on' => 'update'),
            array(
                'protocol',
                'default',
                'value' => 'http',
                'setOnEmpty' => true,
                'on' => 'insert',
            ),
            array(
                'strict_ssl',
                'default',
                'value' => 1,
                'setOnEmpty' => true,
                'on' => 'insert',
            ),
            array(
                'endpoint_timeout',
                'numerical',
                'integerOnly' => true,
                'min' => 2,
                'max' => 900,
            ),
            array(
                'queries_second, queries_day',
                'numerical',
                'integerOnly' => true,
                'min' => 1,
                'max' => 1000000000,
            ),
        ), parent::rules());
    }
    
    public function relations()
    {
        return array_merge(parent::relations(), array(
            'approvedKeyCount' => array(
                self::STAT,
                'Key',
                'api_id',
                'condition' => 'status = :status',
                'params' => array(':status' => \Key::STATUS_APPROVED),
            ),
            'pendingKeyCount' => array(
                self::STAT,
                'Key',
                'api_id',
                'condition' => 'status = :status',
                'params' => array(':status' => \Key::STATUS_PENDING),
            ),
        ));
    }
    
    public function beforeSave()
    {
        parent::beforeSave();
        
        global $ENABLE_AXLE;
        if(isset($ENABLE_AXLE) && !$ENABLE_AXLE){
            return true;
        }
        
        /**
         * Before saving an Api object, we need to provision it on ApiAxle,
         * or update it of course.
         * 
         * If the call to ApiAxle failes, the save will not go through
         */
        
        $apiData = array(
            'endPoint' => $this->endpoint,
            'defaultPath' => $this->default_path ?: '/',
            'protocol' => $this->protocol,
            'strictSSL' => $this->strict_ssl ? true : false,
            'endPointTimeout' => !is_null($this->endpoint_timeout) 
                    ? (int)$this->endpoint_timeout : 2,
        );
        
        $axleApi = new AxleApi(Yii::app()->params['apiaxle']);
        if ($this->getIsNewRecord()) {
            try {
                $axleApi->create($this->code, $apiData);
                return true;
            } catch (\Exception $e) {
                $this->addError(
                    'code',
                    'Failed to create new API on the proxy: ' . $e->getMessage()
                );
                return false;
            }
        } else {
            try {
                $axleApi->get($this->code);
                $axleApi->update($apiData);
                return true;
            } catch (\Exception $e) {
                
                // If the Api was not found, try recreating it.
                $notFoundMessage = sprintf(
                    'API returned error: Api \'%s\' not found.',
                    $this->code
                );
                if (($e->getCode() == 201) && ($notFoundMessage === $e->getMessage())) {
                    try {
                        $axleApi->create($this->code, $apiData);
                        \Event::log(sprintf(
                            'The "%s" API (%s, ID %s) was re-added to ApiAxle%s.',
                            $this->display_name,
                            $this->code,
                            $this->api_id,
                            (is_null($nameOfCurrentUser) ? '' : ' by ' . $nameOfCurrentUser)
                        ), $this->api_id);
                        return true;
                    } catch (\Exception $e) {
                        $this->addError(
                            'code',
                            'Failed to recreate API on the proxy: ' . $e->getMessage()
                        );
                        return false;
                    }
                }

                $this->addError(
                    'code',
                    'Failed to update API on the proxy: ' . $e->getMessage()
                );
                return false;
            }
        }
    }
    
    protected function beforeDelete()
    {
        if ( ! parent::beforeDelete()) {
            return false;
        }
        
        if ($this->approvedKeyCount > 0) {
            /* NOTE: We are intentionally doing a loose comparison (==) for the
             * key count because Yii returns integers as strings to be able to
             * handle large integer values.  */
            $this->addError('api_id', sprintf(
                'There %s still %s active %s to this API. Before you can delete this API, you must revoke all of '
                . 'its active (aka. approved) keys.',
                ($this->approvedKeyCount == 1 ? 'is' : 'are'),
                $this->approvedKeyCount,
                ($this->approvedKeyCount == 1 ? 'key' : 'keys')
            ));
            return false;
        }
        
        foreach ($this->apiVisibilityDomains as $apiVisibilityDomain) {
            if ( ! $apiVisibilityDomain->delete()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this API because we were not able to finish deleting the records of what '
                    . 'email domains are allowed to see this API: %s',
                    print_r($apiVisibilityDomain->getErrors(), true)
                ));
                return false;
            }
        }
        
        foreach ($this->apiVisibilityUsers as $apiVisibilityUser) {
            if ( ! $apiVisibilityUser->delete()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this API because we were not able to finish deleting the records of what '
                    . 'users are allowed to see this API: %s',
                    print_r($apiVisibilityUser->getErrors(), true)
                ));
                return false;
            }
        }
        
        /* NOTE: The approved/active key count check (above) confirms that, by
         *       this point, we know there are no active keys. That is why the
         *       error message below refers to deleting the inactive keys.  */
        foreach ($this->keys as $key) {
            if ( ! $key->delete()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this API because we were not able to finish deleting its inactive keys: %s',
                    print_r($key->getErrors(), true)
                ));
                return false;
            }
        }
        
        foreach ($this->events as $event) {
            $event->api_id = null;
            if ( ! $event->save()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this API because we were not able to finish updating the related event '
                    . 'records: %s',
                    print_r($event->getErrors(), true)
                ));
                return false;
            }
        }
        
        global $ENABLE_AXLE;
        if(isset($ENABLE_AXLE) && !$ENABLE_AXLE){
            return true;
        }
        
        $axleApi = new AxleApi(Yii::app()->params['apiaxle']);
        try{
            $axleApi->delete($this->code);
            return true;
        } catch (\Exception $e) {
            $this->addError('code',$e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate HTML for a badge (such as for a key count).
     * 
     * @param number $badgeValue The value to show in the badge.
     * @param string|null $extraCssClass (Optional:) Any additional CSS
     *     class(es) to include on the HTML element.
     * @param string|null $hoverTitle (Optional:) Text to show on hover (if
     *     any).
     * @param string|null $linkTarget (Optional:) If desired, the URL to make
     *     this badge a hyperlink to.
     * @return string The resulting HTML.
     */
    public static function generateBadgeHtml(
        $badgeValue,
        $extraCssClass = null,
        $hoverTitle = null,
        $linkTarget = null
    ) {
        // Assemble the title attribute HTML string (if applicable).
        if ($hoverTitle === null) {
            $titleAttrHtml = '';
        } else {
            $titleAttrHtml = ' title="' . CHtml::encode($hoverTitle) . '"';
        }
           
        // Assemble the HTML for the badge itself.
        $badgeHtml = sprintf(
            '<span class="badge%s"%s>%s</span>',
            ($extraCssClass !== null ? ' ' . $extraCssClass : ''),
            $titleAttrHtml,
            CHtml::encode($badgeValue)
        );
        
        // If given a URL to link to...
        if ($linkTarget !== null) {
            
            // Wrap the badge in a link tag before returning it.
            return sprintf(
                '<a href="%s">%s</a>',
                $linkTarget,
                $badgeHtml
            );
        } else {
            
            // Otherwise just return the badge HTML.
            return $badgeHtml;
        }
    }
    
    public function isPubliclyVisible()
    {
        return ($this->visibility === self::VISIBILITY_PUBLIC);
    }
    
    /**
     * Confirm that this Api's endpoint and default_path are a unique
     * combination.
     * 
     * @param string $attribute The name of the attribute to be validated.
     * @param array $params Options specified in the validation rule.
     */
    public function isUniqueEndpointDefaultPathCombo($attribute, $params)
    {
        // Get the list of all Apis that have this endpoint and default_path.
        $apis = \Api::model()->findAllByAttributes(array(
            'endpoint' => $this->endpoint,
            'default_path' => $this->default_path,
        ));
        
        // If any of those are NOT this Api...
        foreach ($apis as $api) {
            if ($api->api_id !== $this->api_id) {
                
                // Then the validation fails.
                $this->addError(
                    $attribute,
                    'The given endpoint and default path are already in use by '
                    . 'the "' . $api->display_name . '" API.'
                );
                
                // Also add an error on the default_path attribute (to highlight
                // it as having an error), but don't put any additional message.
                $this->addError('default_path', '');
                
                // Go ahead and break out of the loop (since we found an error).
                break;
            }
        }
    }
    
    /**
     * Indicate whether this API should be visible to the given User. Note that
     * this is a User model, not a Yii CWebUser.
     * 
     * @param User $user The User (model) whose permissions need to be checked.
     * @return boolean Whether the API should be visible to that User.
     */
    public function isVisibleToUser($user)
    {
        // If the user is a guest, they can't see any APIs.
        if ( ! ($user instanceof \User)) {
            return false;
        }
        
        return $this->isPubliclyVisible() ||
               $user->isIndividuallyInvitedToSeeApi($this) ||
               $user->isInvitedByDomainToSeeApi($this) ||
               $user->isAdmin() ||
               $user->isOwnerOfApi($this);
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Api the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    /**
     * Update the rate limits (queries per second, queries per day) of any of
     * this Api's Keys that do not already have the correct rate limits.
     * 
     * @throws \Exception
     */
    protected function updateKeysRateLimitsToMatch()
    {
        foreach ($this->keys as $key) {
            if (($key->queries_day === $this->queries_day) &&
                ($key->queries_second === $this->queries_second)) {
                continue;
            }
            
            $key->queries_day = $this->queries_day;
            $key->queries_second = $this->queries_second;
            if ( ! $key->save()) {
                throw new \Exception(sprintf(
                    'Failed to update Key %s\'s rate limits to match '
                    . 'Api\'s rate limits: %s',
                    $key->key_id,
                    print_r($key->getErrors(), true)
                ), 1467836607);
            }
        }
    }
    
    /**
     * @param string $attribute the name of the attribute to be validated
     * @param array $params options specified in the validation rule
     */
    public function validateEndpoint($attribute, $params) {

            // If the endpoint DOES seem to contain a protocal...
            if(!preg_match(self::REGEX_ENDPOINT, $this->endpoint)){
                    // Indicate that it is NOT valid.
                    $this->addError('endpoint', 'API Endpoint should only be '
                            . 'the host name and should not include protocol '
                            . 'or path.');
            }
    }
    
    /**
     * Validate that the specified owner_id is an acceptable value.
     * 
     * @param string $attribute The name of the attribute to be validated.
     * @param array $params The options specified in the validation rule.
     */
    public function validateOwnerId($attribute, $params)
    {
        // If they selected anyone...
        if (($this->owner_id !== null) && ($this->owner_id !== '')) {

            // Try to get the User model for the specified owner.
            $owner = $this->owner;
            if ( ! ($owner instanceof \User)) {
                $this->addError(
                    $attribute,
                    'Please pick a real user to be the owner.'
                );
            } else {

                // Make sure they selected someone with owner privileges.
                if ( ! $owner->hasOwnerPrivileges()) {
                    $this->addError(
                        $attribute,
                        'You may only specify users with API Owner privileges '
                        . 'as the owner.'
                    );
                }
            }
        }
    }
}
