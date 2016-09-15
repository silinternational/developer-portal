<?php
namespace Sil\DevPortal\components;

use Sil\DevPortal\models\Key;

/**
 * Trait to add various functions related to Keys dependent upon an invitation
 * (such as an ApiVisibilityUser or ApiVisibilityDomain) in order to exist.
 *
 * @author Matt Henderson
 */
trait DependentKeysTrait
{
    /**
     * Get an HTML list representing the list of dependent Keys.
     * 
     * @return string An HTML list of links to the dependent Keys.
     */
    public function getLinksToDependentKeysAsHtmlList()
    {
        $dependentKeys = $this->getDependentKeys();
        $listItemLinksToDependentKeys = array();
        foreach ($dependentKeys as $key) {
            if ($key->user === null) {
                $userDisplayName = '(USER NOT FOUND)';
            } else {
                $userDisplayName = $key->user->getDisplayName();
            }
            $listItemLinksToDependentKeys[] = sprintf(
                '<li><a href="%s">%s</a> (%s)</li>',
                \Yii::app()->createUrl('/key/details', array(
                    'id' => $key->key_id,
                )),
                \CHtml::encode($userDisplayName),
                $key->getStyledStatusHtml()
            );
        }
        return '<ul>' . implode(' ', $listItemLinksToDependentKeys) . '</ul>';
    }
    
    /**
     * Determine whether there are any Keys that depend on this
     * ApiVisibilityDomain. See getDependentKeys() for more information.
     *
     * @return boolean
     */
    public function hasDependentKey()
    {
        return (count($this->getDependentKeys()) > 0);
    }
    
    /**
     * Get the list of Keys (active or pending) where the owner of the Key can
     * only see that Api because of this invitation.
     *
     * @return Key[] The list of keys.
     */
    abstract public function getDependentKeys();
}
