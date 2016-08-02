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
