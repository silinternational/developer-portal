<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\components\UserAuthenticationData;

class SamlUserIdentity extends UserIdentity
{
    /** @var \SimpleSAML_Auth_Simple */
    protected $auth;
    
    protected $config;
    
    public function __construct() 
    {
        $this->config = \Yii::app()->params['saml'];
        $this->auth = new \SimpleSAML_Auth_Simple($this->config['default-sp']);
    }
    
    /**
     * Detemine whether the named auth. provider is the one for whom we can
     * trust the email address as a fallback identifier in cases where we lack
     * an auth_provider_user_identifier. This is just for handling user records
     * from our original auth. provider, before we started supporting multiple
     * (and recording auth_provider_user_identifier values).
     * 
     * @param string $authProviderName
     * @return bool
     */
    protected function canTrustEmailAsFallbackIdFor($authProviderName)
    {
        if ($authProviderName === null) {
            return false;
        }
        
        $trustedAuthProviderName = \Yii::app()->params['saml']['trustEmailAsFallbackIdFor'];
        if ($trustedAuthProviderName === null) {
            return false;
        }
        
        return ($authProviderName === $trustedAuthProviderName);
    }
    
    /**
     * Extract the actual group names (uppercased) from the given list of
     * access groups as returned by the IdP.
     * 
     * EXAMPLE
     * - (input:) ['a=GROUP_name,b=stuff', 'a=OTHER-GROUP,b=random']
     * - (output:) ['GROUP_NAME', 'OTHER-GROUP']
     * 
     * @param array $accessGroupList The list of raw access group data.
     * @return array
     */
    public function extractAccessGroups($accessGroupList)
    {
        $accessGroups = array();
        foreach ($accessGroupList as $rawGroupString) {
            $groupNameValuePairString = explode(',', $rawGroupString);
            $groupNameAndValue = explode('=', $groupNameValuePairString[0]);
            $accessGroups[] = strtoupper($groupNameAndValue[1]);
        }
        return $accessGroups;
    }
    
    /**
     * Find (and return) the User record that corresponds to the user data
     * provided by the authentication service. If no such user is found, null is
     * returned.
     * 
     * For logins from the auth. provider whose email addresses we are willing
     * to trust as a fallback identifier, allow matching on email address if we
     * have not yet recorded the authentication provider's user identifier to
     * the database.
     * 
     * @param \Sil\DevPortal\components\UserAuthenticationData $userAuthData
     * @return \User|null
     */
    protected function findUserRecord($userAuthData)
    {
        $user = parent::findUserRecord($userAuthData);
        
        if ($user === null) {
            $authProvider = $userAuthData->getAuthProvider();
            if ($this->canTrustEmailAsFallbackIdFor($authProvider)) {
            
                /* @var $user \User */
                $user = \User::model()->findByAttributes(array(
                    'auth_provider' => $authProvider,
                    'auth_provider_user_identifier' => null,
                    'email' => $userAuthData->getEmailAddress(),
                ));
            }
        }
        
        return $user;
    }
    
    /**
     * Get the idp value (from the config) for the auth source in use.
     * 
     * @return string
     */
    protected function getAuthSourceIdpEntityId()
    {
        // Get an object representing the auth source.
        $samlAuthSource = $this->auth->getAuthSource();
        
        // Next get the metadata as a configuration object.
        $authSourceMetadata = $samlAuthSource->getMetadata();
        
        // Return idp name as retrieved from the metadata configuration object.
        return $authSourceMetadata->getValue('idp');
    }

    public function getAuthType()
    {
        return 'saml';
    }
    
    public function getLoginUrl($return = null)
    {
        return $this->auth->getLoginURL($return);
    }
    
    public function getLogoutUrl()
    {
        /*
         * NOTE: This is something of a hack solution because the default
         *       functionality of simpleSAMLphp did not work as expected.
         */
        
        // Get the IdP entity ID of the auth source currently in use.
        $idpEntityId = $this->getAuthSourceIdpEntityId();
        
        // Get an object representing the auth source.
        $samlAuthSource = $this->auth->getAuthSource();
        
        // Get the IdP metadata from auth source object.
        $idpMetadata = $samlAuthSource->getIdPMetadata($idpEntityId);

        // Get the SingleLogoutUrl from IdP configuration and append the
        // ReturnTo url.
        $logoutUrl = $idpMetadata->getValue('SingleLogoutService')
            . '?ReturnTo=' . rawurlencode(\Yii::app()->createAbsoluteUrl('/'));
        
        return $logoutUrl;
        
        // This should work to get a logout url directly from simple saml php,
        // but for whatever reason it never redirects the user to the IdP to
        // kill the IdP session.
        //return $this->auth->getLogoutURL($return);
    }
    
    /**
     * Get the name to use for the authentication provider.
     * 
     * @return string The name to use for the IdP.
     */
    protected function getNameOfAuthProvider()
    {
        // Get the entity ID used for the IdP in our SAML configuration.
        $idpEntityId = $this->getAuthSourceIdpEntityId();
        
        // Get the list of auth sources (names and IdP Entity IDs).
        $authSources = \Yii::app()->params['saml']['authSources'];
        
        // If we know a friendly name for that IdP, use it
        foreach ($authSources as $authProviderName => $authProvIdpEntityId) {
            if ($idpEntityId === $authProvIdpEntityId) {
                if ( ! empty($authProviderName)) {
                    return $authProviderName;
                }
                break;
            }
        }
        
        // Otherwise use the IdP Entity ID (that we already have).
        return $idpEntityId;
    }
    
    /**
     * Get the data about this user as returned by the authentication provider.
     * 
     * @return \Sil\DevPortal\components\UserAuthenticationData
     */
    public function getUserAuthData()
    {
        // If the user is NOT yet authenticated...
        if ( ! $this->auth->isAuthenticated()) {
            
            // Record that we don't know who they are.
            $this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
            
            // Send them to the SAML login page.
            \Yii::app()->controller->redirect($this->getLoginUrl());
        }
        // Otherwise...
        else {
            
            // Get an easier reference to our config data for mapping returned
            // field names to our names for those fields.
            $map = $this->config['map'];

            // Get the attributes returned by the authentication process.
            $attrs = $this->auth->getAttributes();
            $eduPersonTargetedID = $this->getValueFromSamlAttributes(
                $attrs,
                'eduPersonTargetedID'
            );
            $authProvider = $this->getNameOfAuthProvider();
            $emailAddress = $this->getValueFromSamlAttributes(
                $attrs,
                $map['emailField']
            );
            $firstName = $this->getValueFromSamlAttributes(
                $attrs,
                $map['firstNameField']
            );
            $lastName = $this->getValueFromSamlAttributes(
                $attrs,
                $map['lastNameField']
            );
            $displayName = $firstName . ' ' . $lastName;
            $uuid = $this->getValueFromSamlAttributes(
                $attrs,
                $map['uuidField']
            );
            $rawAccessGroups = $this->getValueFromSamlAttributes(
                $attrs,
                'insiteAccessGroups'
            );
            if ($rawAccessGroups !== null) {
                $accessGroups = $this->extractAccessGroups($rawAccessGroups);
            } else {
                $accessGroups = array();
            }

            return new UserAuthenticationData(
                $authProvider,
                $eduPersonTargetedID,
                $emailAddress,
                $firstName,
                $lastName,
                $displayName,
                $uuid,
                $accessGroups
            );
        }
    }
    
    /**
     * Retrieve the specified value from the given array of SAML attributes. If
     * that key has exactly one value, return it. If it has multiple return them
     * as an array. If not found, null is returned.
     * 
     * @param array $attributes The SAML attributes.
     * @param string $key The name of the desired value.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function getValueFromSamlAttributes($attributes, $key)
    {
        if ( ! is_array($attributes)) {
            throw new \InvalidArgumentException(
                'No array of SAML attributes was provided for getting the '
                . 'requested value.',
                1444933244
            );
        }
        
        if (array_key_exists($key, $attributes)) {
            $numValuesForKey = count($attributes[$key]);
            if ($numValuesForKey == 1) {
                
                // If there's only one value, return it.
                return $attributes[$key][0];
                
            } elseif ($numValuesForKey > 1) {
                
                // If there are multiple values, return the whole array.
                return $attributes[$key];
            }
        }
        
        return null;
    }
    
    /**
     * Update the corresponding User record with the applicable data from the
     * given user auth. data, only changing those fields that we decided to
     * update each time the user logs in.
     * 
     * @param UserAuthenticationData $userAuthData
     */
    protected function updateUserRecord($userAuthData)
    {
        // Get the user record.
        $user = $this->findUserRecord($userAuthData);
        
        // If the user record lacks an auth. provider user identifier, add it.
        if ( ! $user->auth_provider_user_identifier) {
            $authProviderUserIdentifier = $userAuthData->getAuthProviderUserIdentifier();
            if ($authProviderUserIdentifier) {
                $user->auth_provider_user_identifier = $authProviderUserIdentifier;

                // Try to save the change.
                if ( ! $user->save()) {
                    throw new \Exception(
                        'Failed to save this user\'s auth. provider user '
                        . 'identifier: ' . PHP_EOL
                        . $user->getErrorsAsFlatTextList(),
                        1444936447
                    );
                }
            }
        }
        
        // Let the parent class also update the more normal fields.
        parent::updateUserRecord($userAuthData);
    }
}
