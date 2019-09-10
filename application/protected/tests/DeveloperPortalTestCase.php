<?php

use Sil\DevPortal\tests\DbTestCase;

/**
 * Class to add some useful helper functions.
 * @author Matt Henderson
 */
class DeveloperPortalTestCase extends DbTestCase
{
    /**
     * Check to confirm that all the values in an array are unique.
     * @param array $a The array whose values should be checked for uniqueness.
     * @param boolean $ignoreCase Whether to ignore case when comparing (true
     *     by default), meaning that 'active' and 'Active' would be considered
     *     non-unique entries.
     * @param array $a The array whose values should be checked for uniqueness.
     * @return boolean Whether they are all unique (ignoring case).
     */
    public static function ArrayValuesAreUnique($a, $ignoreCase = true)
    {
        // Set up a list to hold the values we've found so far.
        $found = array();

        // For value in the given array...
        foreach ($a as $value) {
            
            // If told to ignore case...
            if ($ignoreCase) {
                
                // Convert the value to uppercase (to remove case differences).
                $value = mb_strtoupper($value, 'UTF-8');
            }
            
            // If this value is NOT unique, indicate that.
            if (isset($found[$value])) return false;
            
            // Assuming we had NOT yet seen this value, add it to the list of
            // the values we've found so far (as the key in our array of found
            // values, using something non-null as the corresponding value so
            // that is_set() will return true).
            $found[$value] = true;
        }
        
        // If we reach this point, all the values were unique.
        return true;
    }
    
    /**
     * Confirm that an expected relationship is set up between the class being
     * tested and another class.
     * 
     * @param mixed $classInstance An instance of the class being tested.
     *     Example: An instance of an Api object.
     * @param string $relationName The name of the expected relationship.
     *     Example: 'keys'.
     * @param string $relatedClassName The name of the related class.
     *     Example: '\Sil\DevPortal\models\Key'.
     */
	protected function assertClassHasRelation($classInstance, $relationName,
        $relatedClassName)
    {
        // Get the relations defined for the given class instance.
        $relations = $classInstance->relations();
        
        // Make sure the given relation name is in that list.
        $this->assertArrayHasKey($relationName, $relations,
            'No way found to retrieve "' . $relationName . '" from a ' .
            get_class($classInstance) . ' instance');
        
        // Make sure that the given relation name says it is defining a
        // relationship to the expected class.
        $this->assertEquals($relatedClassName, $relations[$relationName][1],
            'Unexpected class name given for ' . $relationName . ' relation');
    }
    
    protected static function getConstantsWithPrefix($className, $constPrefix)
    {
        // Get the list of all of the constants for the named class.
        $refl = new ReflectionClass($className);
        $allClassConstants = $refl->getConstants();
        
        // Figure out the length of the string prefixing the constants that we
        // care about at the moment.
        $prefixLen = strlen($constPrefix);
        
        // Extract a list of the relevant constants.
        $relevantConstants = array();
        foreach ($allClassConstants as $constantName => $constantValue) {
            
            // If this is a constant that we currently care about...
            if (substr($constantName, 0, $prefixLen) == $constPrefix) {
                
                // Add it to the list of relevant constants.
                $relevantConstants[$constantName] = $constantValue;
            }
        }
        
        // Return the resulting list.
        return $relevantConstants;
    }
    
    protected function confirmConstantsDiffer($className, $constPrefix,
            $constsWithFriendlyVersions = null)
    {
        // Get the list of the relevant constants (i.e. - those with the given
        // prefix).
        $relevantConstants = self::getConstantsWithPrefix(
            $className,
            $constPrefix
        );
        
        // Make sure they're all unique (ignoring case, since these are often
        // used as database values, and databases are often set to be
        // case-insensitive).
        $this->assertTrue($this->ArrayValuesAreUnique($relevantConstants),
                'Found non-unique constant value: ' .
                print_r($relevantConstants, true));
        
        // If a list of constants-with-user-friendly-versions was suppplied...
        if (!is_null($constsWithFriendlyVersions)) {
        
            // Make sure we have the same number of entries in each array (to
            // confirm that all of the constants are being returned by the
            // function for use by drop-down lists, etc.).
            $this->assertEquals(count($relevantConstants),
                    count($constsWithFriendlyVersions),
                    'Found different number of "' . $constPrefix . '..." ' .
                    'constants than expected.');

            // Make sure the user-friendly versions of the constants are also
            // all unique, making sure that the difference is more than just one
            // of upper/lowercase.
            $this->assertTrue(
                    $this->ArrayValuesAreUnique($constsWithFriendlyVersions),
                    'Found non-unique "' . $constPrefix . '..." constant ' .
                    'user-friendly text: ' . 
                    print_r($constsWithFriendlyVersions, true));
        }
    }
    
    public static function getModelErrorsForConsole($modelErrors)
    {
        // Begin assembling the string.
        $output = '';
        
        // For each of the entries in the list of the model's errors...
        foreach ($modelErrors as $attribute => $attrErrors) {
            
            // Show the attribute name.
            $output .= ' * ' . $attribute . PHP_EOL;
            
            // For each of the errors for that attribute...
            foreach ($attrErrors as $error) {

                // Show the error.
                $output .= '   - ' . $error . PHP_EOL;
            }
        }
        
        // Return the result.
        return $output;
    }
}
