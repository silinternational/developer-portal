<?php

// Bring in a reference to the parent class.
Yii::import('zii.widgets.grid.CGridColumn');

/**
 * Class for easily outputting the desired collection of action links in a
 * CGridView instance.
 *
 * @author Matt Henderson
 */
class ActionLinksColumn extends CGridColumn
{
    /**
     * @var array the HTML options for the data cell tags.
     */
    public $htmlOptions = array();
    
    /**
     * Whether to show this as a dropdown menu (ture) or as a series of links
     * (false). Defaults to false.
     * @var boolean
     */
    public $dropdown = false;
    
    protected $dropdownPreHtml = '<div class="btn-group pull-right"><button class="btn btn-small dropdown-toggle" data-toggle="dropdown">Actions <span class="caret"></span></button><ul class="dropdown-menu">';
    protected $dropdownPostHtml = '</ul></div>';
    
    /**
     * List of desired links, each an associative array with a 'icon', 'text',
     * and 'urlPattern' values. 
     * 
     * The 'icon' refers to a Bootstrap icon, minus the "icon-" prefix. It will
     * NOT be HTML-encoded.
     * 
     * The 'text' is the text to display as a link. It will NOT be HTML-encoded.
     * 
     * The 'urlPattern' will be searched for ':key' placeholders (similar to the
     *     routing settings for Yii), replacing them with corresponding values
     *     from the $data variable. The result will be passed to createUrl
     *     before being used as an actual href value.
     * 
     * EXAMPLE:
     * array(
     *     array(
     *         'icon' => 'list',
     *         'text' => 'Details',
     *         'urlPattern' => 'api/details/:code',
     *     )
     * )
     * 
     * @var array
     */
    public $links;
    
    /**
     * The HTML to use between each link. Defaults to a basic visual separator.
     * Only use if NOT showing links as a dropdown menu.
     * 
     * @var string
     */
    public $separatorHtml = ' | ';
    
    /**
     * @param integer $row
     * @param mixed $data
     */
    protected function renderDataCellContent($row, $data)
    {
        $linksAsHtml = array();
        foreach($this->links as $link) {
            
            // Get the urlPattern for this link.
            $urlPat = $link['urlPattern'];
            
            // Make the indicated substitutions in the urlPattern.
            foreach($data as $key => $value) {
                
                // Set up the placeholder string to look for.
                $placeholder = ':' . $key;
                
                // If a placeholder exists in the urlPattern for this key...
                if (strpos($urlPat, $placeholder) !== FALSE) {
                    
                    // Replace the data (URL-encoding it appropriately).
                    $urlPat = str_replace($placeholder, 
                                          rawurlencode($value), 
                                          $urlPat);
                }
            }
            
            // Assemble the actual HTML.
            if ($this->dropdown) {
                $linksAsHtml[] = sprintf(
                    '<li><a href="%s" class="nowrap space-after-icon">' .
                      '<i class="icon-%s"></i>%s' .
                    '</a></li>',
                    $this->grid->controller->createUrl($urlPat),
                    $link['icon'],
                    $link['text']
                );
            } else {
                $linksAsHtml[] = sprintf(
                    '<a class="nowrap space-after-icon" href="%s">' .
                    '<i class="icon-%s"></i>%s</a>',
                    $this->grid->controller->createUrl($urlPat),
                    $link['icon'],
                    $link['text']
                );
            }
        }
        
        // Combine all of the links and return the resulting HTML.
        if ($this->dropdown) {
            echo $this->dropdownPreHtml .
                 implode('', $linksAsHtml) .
                 $this->dropdownPostHtml;
        } else {
            echo implode($this->separatorHtml, $linksAsHtml);
        }
    }
}
