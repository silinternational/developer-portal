<?php
namespace Sil\DevPortal\components;

class LoginOption
{
    protected $authType;
    protected  $authProvider;
    protected  $displayName;
    
    public function __construct(
        $authType,
        $authProvider = null,
        $displayName = null
    ) {
        $this->authType = $authType;
        $this->authProvider = $authProvider;
        $this->displayName = $displayName ?: $authProvider;
    }
    
    /**
     * Get the display name for this login option.
     * 
     * @return string
     */
    public function getDisplayName()
    {
        return trim($this->displayName);
    }
    
    /**
     * Get the label HTML (with logo, if available).
     * 
     * @param bool $useLightLogo Whether to use the light version of the logo.
     * @return string
     */
    public function getLabelHtml($useLightLogo = false)
    {
        return sprintf(
            '%sLogin with %s',
            $this->getLogoHtml($useLightLogo),
            \CHtml::encode($this->getDisplayName())
        );
    }
    
    /**
     * Get the full link HTML (with logo, if available).
     * 
     * @param string $extraCssClassString (Optional:) Any additional CSS class
     *     string content that you want.
     * @param bool $useLightLogo Whether to use the light version of the logo.
     * @return string
     */
    public function getLinkHtml($extraCssClassString = '', $useLightLogo = false)
    {
        return sprintf(
            '<a href="%s" class="%s">%s</a>',
            \CHtml::encode($this->getUrl()),
            \CHtml::encode($extraCssClassString),
            $this->getLabelHtml($useLightLogo)
        );
    }
    
    /**
     * Get the HTML for showing this login option's logo (if any).
     * 
     * @param bool $useLightLogo Whether to use the light version of the logo.
     * @return string The HTML, or an empty string if unavailable.
     */
    public function getLogoHtml($useLightLogo = false)
    {
        if ( ! $this->hasLogoFile($useLightLogo)) {
            return '';
        }
        
        return sprintf(
            '<img src="%s" class="login-logo" aria-hidden="true" />',
            \CHtml::encode($this->getUrlPathToLogo($useLightLogo))
        );
    }
    
    /**
     * Get the (relative) URL for logging in with this login option.
     * 
     * @return string
     */
    public function getUrl()
    {
        return \Yii::app()->createUrl('auth/login', $this->getUrlParams());
    }
    
    protected function getUrlParams()
    {
        $urlParams = ['authType' => $this->authType];
        if ( ! empty($this->authProvider)) {
            $urlParams['providerSlug'] = AuthManager::slugify($this->authProvider);
        }
        return $urlParams;
    }
    
    /**
     * Get the relative URL path to the logo for this login option (if
     * available), starting with a slash.
     * 
     * @param bool $useLightLogo Whether to use the light version of the logo.
     * @return string|null The URL path, or null if not available.
     */
    protected function getUrlPathToLogo($useLightLogo = false)
    {
        if ($this->hasAuthProvider()) {
            $providerSlug = AuthManager::slugify($this->authProvider);

            return sprintf(
                '/img/login-marks/%s%s.png',
                $providerSlug,
                ($useLightLogo ? '-light' : '')
            );
        }
        return null;
    }
    
    protected function hasAuthProvider()
    {
        return ! empty($this->authProvider);
    }
    
    /**
     * Whether there is a logo file available for this login option.
     * 
     * @param bool $useLightLogo Whether to use the light version of the logo.
     * @return boolean
     */
    public function hasLogoFile($useLightLogo = false)
    {
        $urlPathToLogo = $this->getUrlPathToLogo($useLightLogo);
        if (empty($urlPathToLogo)) {
            return false;
        }
        
        return file_exists(__DIR__ . '/../../public' . $urlPathToLogo);
    }
}
