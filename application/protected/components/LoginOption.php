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
     * @return string
     */
    public function getLabelHtml()
    {
        return sprintf(
            '%sLogin with %s',
            $this->getLogoHtml(),
            \CHtml::encode($this->getDisplayName())
        );
    }
    
    /**
     * Get the full link HTML (with logo, if available).
     * 
     * @param string $extraCssClassString (Optional:) Any additional CSS class
     *     string content that you want.
     * @return string
     */
    public function getLinkHtml($extraCssClassString = '')
    {
        return sprintf(
            '<a href="%s" class="btn btn-success login-logo-button %s">%s</a>',
            \CHtml::encode($this->getUrl()),
            \CHtml::encode($extraCssClassString),
            $this->getLabelHtml()
        );
    }
    
    /**
     * Get the HTML for showing this login option's logo (if any).
     * 
     * @return string The HTML, or an empty string if unavailable.
     */
    public function getLogoHtml()
    {
        if ( ! $this->hasLogoFile()) {
            return '';
        }
        
        return sprintf(
            '<img src="%s" class="login-logo" aria-hidden="true" />',
            \CHtml::encode($this->getUrlPathToLogo())
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
     * @return string|null The URL path, or null if not available.
     */
    protected function getUrlPathToLogo()
    {
        if ($this->hasAuthProvider()) {
            $providerSlug = AuthManager::slugify($this->authProvider);

            return sprintf(
                '/img/login-marks/%s.png',
                $providerSlug
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
     * @return boolean
     */
    public function hasLogoFile()
    {
        $urlPathToLogo = $this->getUrlPathToLogo();
        if (empty($urlPathToLogo)) {
            return false;
        }
        
        return file_exists(__DIR__ . '/../../public' . $urlPathToLogo);
    }
}
