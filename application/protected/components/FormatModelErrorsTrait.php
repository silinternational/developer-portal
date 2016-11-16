<?php
namespace Sil\DevPortal\components;

/**
 * Trait to add utility functions for getting a model's errors formatted for
 * various contexts.
 *
 * @author Matt Henderson
 */
trait FormatModelErrorsTrait
{
    /**
     * Get any attribute errors as a flat HTML list (properly escaped). If
     * there are no errors, an empty HTML unordered list will be returned.
     * 
     * @return string An HTML string representing the list of errors.
     */
    public function getErrorsAsFlatHtmlList()
    {
        $modelErrors = $this->getErrors();
        $htmlListItems = array();
        
        foreach ($modelErrors as $attributeName => $attributeErrors) {
            foreach ($attributeErrors as $attributeError) {
                $htmlListItems[] = sprintf(
                    '<li>%s</li>',
                    \CHtml::encode($attributeError)
                );
            }
        }
        
        return '<ul>' . implode(' ', $htmlListItems) . '</ul>';
    }
    
    /**
     * Get any attribute errors as a flat, plain-text list (as a string). If
     * there are no errors, an empty string will be returned.
     * 
     * @return string The text of the errors.
     */
    public function getErrorsAsFlatTextList()
    {
        $modelErrors = $this->getErrors();
        $textListItems = array();
        
        foreach ($modelErrors as $attributeName => $attributeErrors) {
            foreach ($attributeErrors as $attributeError) {
                $textListItems[] = ' * ' . $attributeError . PHP_EOL;
            }
        }
        
        return implode('', $textListItems);
    }
    
    /**
     * Get any attribute errors as a heirarchical plain-text list (grouped by
     * attribute).
     * 
     * @return string The text of the errors.
     */
    public function getErrorsForConsole()
    {
        $modelErrors = $this->getErrors();
        $output = '';
        
        foreach ($modelErrors as $attributeName => $attributeErrors) {
            $output .= ' * ' . $attributeName . PHP_EOL;
            foreach ($attributeErrors as $attributeError) {
                $output .= '   - ' . $attributeError . PHP_EOL;
            }
        }
        
        return $output;
    }
    
    abstract public function getErrors($attribute = null);
}
