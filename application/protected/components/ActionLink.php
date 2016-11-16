<?php

class ActionLink extends CComponent
{
    public $icon = null;
    public $text = null;
    public $url = null;
    
    /**
     * Create an ActionLink instance with the given parameters. To show it as
     * an actual link on a page, use getAsHtml().
     * 
     * @param mixed $url The URL that this link should go to. See
     *     {@link CHtml::normalizeUrl} for details.
     * @param string|null $text (Optional:) The text to show in the link.
     * @param string|null $icon (Optional:) The name of the Bootstrap 2 icon to
     *     show (if any) with this link. If not set, then no icon will be
     *     included. See http://getbootstrap.com/2.3.2/base-css.html#icons
     */
    public function __construct($url, $text = null, $icon = null)
    {
        // Record the given values.
        $this->icon = $icon;
        $this->text = $text;
        $this->url = $url;
    }
    
    /**
     * Get the HTML for this ActionLink.
     * 
     * @param string $extraCssClassesString Any additional CSS class(es) to use.
     * @return string The HTML.
     */
    public function getAsHtml($extraCssClassesString = '')
    {
        return CHtml::link(
            self::getLinkContentHtml($this->text, $this->icon),
            $this->url,
            array(
                'class' => sprintf(
                    'nowrap %s%s',
                    \CHtml::encode($extraCssClassesString),
                    (($this->text !== null) ? ' space-after-icon' : '')
                ),
            )
        );
    }
    
    /**
     * Get the HTML to include in the contents of a link based on what text is
     * given (if any) and what icon is specified (if any).
     * 
     * @param string|null $text The text to show (if any).
     * @param string|null $icon The icon to use (if any).
     * @return string The HTML to put inside of the link.
     */
    protected static function getLinkContentHtml($text, $icon)
    {
        // Get the given text (HTML encoded).
        $linkTextHtml = CHtml::encode($text);
        
        // Return it, adding an icon if applicable.
        if ($icon === null) {
            return $linkTextHtml;
        } else {
            return sprintf(
                '<i class="icon-%s"></i>%s',
                CHtml::encode($icon),
                $linkTextHtml
            );
        }
    }
}
