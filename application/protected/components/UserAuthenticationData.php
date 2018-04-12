<?php
namespace Sil\DevPortal\components;

/**
 * A collection of information about a user, as returned by an authentication
 * provider.
 */
class UserAuthenticationData
{
    private $authProvider;
    private $authProviderUserIdentifier;
    
    private $emailAddress;
    
    private $firstName;
    private $lastName;
    private $displayName;

    private $authProviderAccessGroups;
    private $uuid;

    /**
     * Constructor.
     *
     * @param string $authProvider The authentication provider (e.g. 'SAML',
     *     'Google', etc.).
     * @param string $authProviderUserIdentifier The authentication provider's
     *     unique identifier for this user.
     * @param string $emailAddress The user's email address.
     * @param string $firstName The user's first name.
     * @param string $lastName The user's last name.
     * @param string $displayName The user's display name.
     * @param string|null $uuid (Optional:) The UUID to use for in conjuction
     *     with web analytics.
     * @param array|null $authProviderAccessGroups (Optional:) The list of
     *     access groups (from the auth. provider) that the user belongs to.
     */
    public function __construct(
        $authProvider,
        $authProviderUserIdentifier,
        $emailAddress,
        $firstName,
        $lastName,
        $displayName,
        $uuid = null,
        $authProviderAccessGroups = array()
    ) {
        $this->authProvider = $authProvider;
        $this->authProviderUserIdentifier = $authProviderUserIdentifier;

        $this->emailAddress = $emailAddress;

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->displayName = $displayName;
        
        $this->uuid = $uuid;
        $this->authProviderAccessGroups = $authProviderAccessGroups;
    }
    
    /**
     * @return array
     */
    public function getAccessGroups()
    {
        return $this->authProviderAccessGroups;
    }
    
    public function getAuthProvider()
    {
        return $this->authProvider;
    }
    
    public function getAuthProviderUserIdentifier()
    {
        return $this->authProviderUserIdentifier;
    }
    
    public function getDisplayName()
    {
        return $this->displayName;
    }
    
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }
    
    public function getFirstName()
    {
        return $this->firstName;
    }
    
    public function getLastName()
    {
        return $this->lastName;
    }
    
    public function getUuid()
    {
        return $this->uuid;
    }
}
