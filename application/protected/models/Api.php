<?php
namespace Sil\DevPortal\models;

use Sil\DevPortal\components\ApiAxle\Client as ApiAxleClient;
use Sil\DevPortal\models\ApiVisibilityDomain;
use Sil\DevPortal\models\ApiVisibilityUser;
use Sil\DevPortal\models\Event;
use Sil\DevPortal\models\Key;
use Sil\DevPortal\models\User;

/**
 * The followings are the available model relations (defined in the parent
 * class, re-documented here after proper 'use' statements to reflect the fixes
 * implemented by FixRelationsClassPathsTrait):
 * @property User $owner
 * @property ApiVisibilityDomain[] $apiVisibilityDomains
 * @property ApiVisibilityUser[] $apiVisibilityUsers
 * @property Event[] $events
 * @property Key[] $keys
 * 
 * Additional model relations (defined here, not in base class):
 * @property int $approvedKeyCount
 * @property int $pendingKeyCount
 */
class Api extends \ApiBase
{
    use \Sil\DevPortal\components\FixRelationsClassPathsTrait;
    use \Sil\DevPortal\components\FormatModelErrorsTrait;
    use \Sil\DevPortal\components\ModelFindByPkTrait;
    use \Sil\DevPortal\components\CreateOrUpdateInApiAxleTrait;
    use \Sil\DevPortal\components\RepopulateApiAxleTrait;
    
    CONST APPROVAL_TYPE_AUTO = 'auto';
    CONST APPROVAL_TYPE_OWNER = 'owner';
    
    CONST PROTOCOL_HTTP = 'http';
    CONST PROTOCOL_HTTPS = 'https';
    
    CONST STRICT_SSL_TRUE = 1;
    CONST STRICT_SSL_FALSE = 0;
    
    CONST REGEX_ENDPOINT = '/^(?=.{1,255}$)[0-9A-Za-z](?:(?:[0-9A-Za-z]|-){0,61}[0-9A-Za-z])?(?:\.[0-9A-Za-z](?:(?:[0-9A-Za-z]|-){0,61}[0-9A-Za-z])?)*\.?$/';
    CONST REGEX_PATH = '/^\/[a-zA-Z0-9\-\.\/_]{0,}$/';
    
    CONST REQUIRE_SIGNATURES_YES = 'yes';
    CONST REQUIRE_SIGNATURES_NO = 'no';
    
    CONST SIGNATURE_WINDOW_MAX = 10;
    
    CONST VISIBILITY_INVITATION = 'invitation';
    CONST VISIBILITY_PUBLIC = 'public';
    
    public function afterDelete()
    {
        parent::afterDelete();
        
        $nameOfCurrentUser = \Yii::app()->user->getDisplayName();
        Event::log(sprintf(
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
            
            Event::log(sprintf(
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
            $mailer = \Utils::getMailer();
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
                    \CLogger::LEVEL_WARNING
                );
            }
        } else {
            
            Event::log(sprintf(
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
                    \CLogger::LEVEL_WARNING
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
    
    /**
     * Get the top few (PUBLIC) popular APIs, ordered by the number of
     * active keys.
     * 
     * @return Api[] A short list of APIs.
     */
    public static function getPopularApis()
    {
        // Get all of the public APIs.
        $publicApis = self::model()->findAllByAttributes(array(
            'visibility' => self::VISIBILITY_PUBLIC,
        ));
        
        // Sort them by popularity (= the number of approved keys).
        usort($publicApis, function($firstApi, $secondApi) {
            return ($firstApi->approvedKeyCount < $secondApi->approvedKeyCount);
        });
        
        // Return the top few results.
        return array_slice($publicApis, 0, 3);
    }
    
    public static function getProtocols()
    {
        return array(
            self::PROTOCOL_HTTP => 'HTTP',
            self::PROTOCOL_HTTPS => 'HTTPS'
        );
    }
    
    public static function getRequireSignatureOptions()
    {
        return array(
            self::REQUIRE_SIGNATURES_YES => 'Yes',
            self::REQUIRE_SIGNATURES_NO => 'No',
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
            'embedded_docs_url' => 'Embedded Docs URL',
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
    
    public function getAdditionalHeadersArray()
    {
        $array = [];
        $rawPairs = explode('&', $this->additional_headers);
        foreach ($rawPairs as $rawPair) {
            if (empty($rawPair)) {
                continue;
            }
            $keyValue = explode('=', $rawPair, 2);
            if (count($keyValue) > 0) {
                $key = $keyValue[0];
                $value = (count($keyValue) > 1 ? $keyValue[1] : null);
                $array[rawurldecode($key)] = rawurldecode($value);
            }
        }
        return $array;
    }
    
    /**
     * Get the domain name for the API proxy. Throws an exception if unable to
     * do so.
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
        $apiAxleEndpointDomain = parse_url(
            \Yii::app()->params['apiaxle']['endpoint'],
            PHP_URL_HOST
        );
        if ( ! empty($apiAxleEndpointDomain)) {
            return str_replace('apiaxle.', '', $apiAxleEndpointDomain);
        }
        throw new \Exception(
            'We could not figure out the API proxy domain name.',
            1420751158
        );
    }
    
    /**
     * Get the protocol (e.g. 'https') for the API proxy.
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
        $proxyProtocol = parse_url(
            \Yii::app()->params['apiaxle']['endpoint'],
            PHP_URL_SCHEME
        );
        return (empty($proxyProtocol) ? 'https' : $proxyProtocol);
    }
    
    protected function getDataForApiAxle()
    {
        return [
            'endPoint' => $this->endpoint,
            'defaultPath' => $this->default_path ?: '/',
            'protocol' => $this->protocol,
            'strictSSL' => $this->strict_ssl ? true : false,
            'endPointTimeout' => !is_null($this->endpoint_timeout) 
                    ? (int)$this->endpoint_timeout : 2,
            'additionalHeaders' => $this->additional_headers ?: '',
            'tokenSkewProtectionCount' => (int)$this->signature_window,
        ];
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
            '%s://%s.%s/',
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
    
    public function getFriendlyId()
    {
        return $this->code;
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
     * Generate the HTML for the invited-domains count badge.
     * 
     * @param string|null $hoverTitle (Optional:) Text to show on hover (if
     *     any).
     * @param string|null $linkTarget (Optional:) If desired, the URL to make
     *     this badge a hyperlink to.
     * @return string The resulting HTML.
     */
    public function getInvitedDomainsCountBadgeHtml(
        $hoverTitle = null,
        $linkTarget = null
    ) {
        $count = count($this->apiVisibilityDomains);
        
        return self::generateBadgeHtml(
            $count,
            ($count ? 'badge-info' : null),
            $hoverTitle,
            $linkTarget
        );
    }
    
    /**
     * Generate the HTML for the invited-users count badge.
     * 
     * @param string|null $hoverTitle (Optional:) Text to show on hover (if
     *     any).
     * @param string|null $linkTarget (Optional:) If desired, the URL to make
     *     this badge a hyperlink to.
     * @return string The resulting HTML.
     */
    public function getInvitedUsersCountBadgeHtml(
        $hoverTitle = null,
        $linkTarget = null
    ) {
        $count = count($this->apiVisibilityUsers);
        
        return self::generateBadgeHtml(
            $count,
            ($count ? 'badge-info' : null),
            $hoverTitle,
            $linkTarget
        );
    }

    public function getRequestKeyText()
    {
        return ($this->requiresApproval() ? 'Request' : 'Get') . ' Key';
    }
    
    public function getRequiresSignatureText()
    {
        $options = self::getRequireSignatureOptions();
        return $this->requiresSignature() ?
            $options[self::REQUIRE_SIGNATURES_YES] :
            $options[self::REQUIRE_SIGNATURES_NO];
    }
    
    public function getSignatureWindowHtml()
    {
        return '+/- ' . (int)$this->signature_window . ' seconds';
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
            '%s://<span class="%s">%s</span>.%s/',
            \CHtml::encode($this->getApiProxyProtocol()),
            \CHtml::encode($cssClass),
            \CHtml::encode($this->code),
            \CHtml::encode($this->getApiProxyDomain())
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
     * @param int $rewindBy (Optional:) How many intervals to "back up"
     *     the starting point by. Used for getting older data.
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
        $includeCurrentInterval = true,
        $rewindBy = 0

    ) {
        // Get the ApiAxle Api object for this Api model.
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        
        // Get the starting timestamp for the data we care about.
        $timeStart = \UsageStats::getTimeStart(
            $granularity,
            $includeCurrentInterval,
            $rewindBy
        );
        
        // Retrieve the stats from ApiAxle.
        $axleStats = $apiAxle->getApiStats($this->code, $timeStart, $granularity);
        
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
        $successfulUsage = \UsageStats::combineUsageCategoryArrays(
            $dataByCategory['uncached'],
            $dataByCategory['cached']
        );
        $usage = \UsageStats::combineUsageCategoryArrays(
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
            array(
                'display_name',
                'unique',
                'caseSensitive' => false,
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
                'value' => new \CDbExpression('NOW()'),
                'setOnEmpty' => false,
                'on' => 'update',
            ),
            array(
                'created,updated',
                'default',
                'value' => new \CDbExpression('NOW()'),
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
            array(
                'require_signature',
                'in',
                'allowEmpty' => false,
                'range' => array_keys(self::getRequireSignatureOptions()),
                'strict' => true,
                'message' => 'Please choose one of the provided options ('
                . implode('/', array_keys(self::getRequireSignatureOptions()))
                . ') for whether calls to this API will require a signature.'
            ),
            array('embedded_docs_url', 'filter', 'filter' => 'trim'),
            array(
                'embedded_docs_url',
                'url',
                'pattern' => '/^https:\/\/docs\.google\.com\/document\/d\/[-A-Z0-9_]+\/pub\?embedded=true/i',
                'message' => 'That does not look like a valid Google Doc embedding URL. '
                . 'Please check the example and try again.'
            ),
            array(
                'signature_window',
                'numerical',
                'allowEmpty' => false,
                'integerOnly' => true,
                'min' => 0,
                'max' => self::SIGNATURE_WINDOW_MAX,
                'tooSmall' => 'The time window for valid signatures cannot be negative.',
                'tooBig' => sprintf(
                    'The time window for valid signatures cannot be more than +/-%s seconds.',
                    self::SIGNATURE_WINDOW_MAX
                ),
            ),
        ), parent::rules());
    }
    
    public function relations()
    {
        return array_merge($this->fixRelationsClassPaths(parent::relations()), array(
            'approvedKeyCount' => array(
                self::STAT,
                '\Sil\DevPortal\models\Key',
                'api_id',
                'condition' => 'status = :status',
                'params' => array(':status' => Key::STATUS_APPROVED),
            ),
            'pendingKeyCount' => array(
                self::STAT,
                '\Sil\DevPortal\models\Key',
                'api_id',
                'condition' => 'status = :status',
                'params' => array(':status' => Key::STATUS_PENDING),
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
         * If the call to ApiAxle fails, the save will not go through.
         */
        return $this->createOrUpdateInApiAxle();
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
                    . 'email domains are allowed to see this API: %s%s',
                    PHP_EOL,
                    $apiVisibilityDomain->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        foreach ($this->apiVisibilityUsers as $apiVisibilityUser) {
            if ( ! $apiVisibilityUser->delete()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this API because we were not able to finish deleting the records of what '
                    . 'users are allowed to see this API: %s%s',
                    PHP_EOL,
                    $apiVisibilityUser->getErrorsAsFlatTextList()
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
                    'We could not delete this API because we were not able to finish deleting its inactive keys: %s%s',
                    PHP_EOL,
                    $key->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        foreach ($this->events as $event) {
            $event->api_id = null;
            if ( ! $event->save()) {
                $this->addError('api_id', sprintf(
                    'We could not delete this API because we were not able to finish updating the related event '
                    . 'records: %s%s',
                    PHP_EOL,
                    $event->getErrorsAsFlatTextList()
                ));
                return false;
            }
        }
        
        global $ENABLE_AXLE;
        if(isset($ENABLE_AXLE) && !$ENABLE_AXLE){
            return true;
        }
        
        $apiAxle = new ApiAxleClient(\Yii::app()->params['apiaxle']);
        try{
            $apiAxle->deleteApi($this->code);
            return true;
        } catch (\Exception $e) {
            $this->addError('code',$e->getMessage());
            return false;
        }
    }
    
    protected function createInApiAxle(ApiAxleClient $apiAxle)
    {
        $apiAxle->createApi($this->code, $this->getDataForApiAxle());
    }
    
    protected function existsInApiAxle()
    {
        return $this->getApiAxleClient()->apiExists($this->code);
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
            $titleAttrHtml = ' title="' . \CHtml::encode($hoverTitle) . '"';
        }
           
        // Assemble the HTML for the badge itself.
        $badgeHtml = sprintf(
            '<span class="badge%s"%s>%s</span>',
            ($extraCssClass !== null ? ' ' . $extraCssClass : ''),
            $titleAttrHtml,
            \CHtml::encode($badgeValue)
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
    
    public function hasTerms()
    {
        return ( ! empty($this->terms));
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
        $apis = self::model()->findAllByAttributes(array(
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
        if ($this->isPubliclyVisible()) {
            return true;
        }
        
        // If the user is a guest, they can only see public APIs.
        if ( ! ($user instanceof User)) {
            return false;
        }
        
        return $user->isIndividuallyInvitedToSeeApi($this) ||
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
     * @return boolean
     */
    public function requiresSignature()
    {
        /* Compare against the No value so that, if there is some weird
         * mismatch or unexpected value, it defaults to requiring signature.  */
        return ($this->require_signature !== self::REQUIRE_SIGNATURES_NO);
    }
    
    public function requiresApproval()
    {
        return ($this->approval_type !== self::APPROVAL_TYPE_AUTO);
    }
    
    protected function shouldExistInApiAxle()
    {
        return true;
    }
    
    protected function updateInApiAxle(ApiAxleClient $apiAxle)
    {
        $apiAxle->updateApi($this->code, $this->getDataForApiAxle());
    }
    
    /**
     * Update the rate limits (queries per second, queries per day) of any of
     * this Api's Keys that do not already have the correct rate limits.
     * 
     * @throws \Exception
     */
    public function updateKeysRateLimitsToMatch()
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
                    . 'Api\'s rate limits: %s%s',
                    $key->key_id,
                    PHP_EOL,
                    $key->getErrorsAsFlatTextList()
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
        if (strval($this->owner_id) !== '') {

            // Try to get the User model for the specified owner.
            $owner = $this->owner;
            if ( ! ($owner instanceof User)) {
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
