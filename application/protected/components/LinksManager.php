<?php

use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\Key;
use Sil\DevPortal\models\User;

class LinksManager extends CComponent
{
    const BUTTON_SIZE_LARGE = 'large';
    const BUTTON_SIZE_MEDIUM = 'medium';
    const BUTTON_SIZE_SMALL = 'small';
    const BUTTON_SIZE_MINI = 'mini';
    
    /**
     * Generate the HTML for the Actions dropdown menu (if there are any
     * ActionLinks given).
     * 
     * @param ActionLink[] $actionLinks The list of ActionLinks representing the
     *     links to include.
     * @param string $buttonSize (Optional:) A constant representing the desired
     *     size of the 'Actions' button. Defaults to
     *     LinksManager::BUTTON_SIZE_MEDIUM.
     * @return string The resulting HTML (if any).
     */
    public static function generateActionsDropdownHtml(
        $actionLinks,
        $buttonSize = self::BUTTON_SIZE_MEDIUM
    ) {
        // If no ActionLinks were given, then no HTML needs to be generated.
        if ( ! Utils::isArrayWithContent($actionLinks)) {
            return '';
        }
        
        // Assemble the HTML for the entries in the dropdown menu.
        $entriesHtml = '';
        foreach ($actionLinks as $actionLink) {
            
            $entriesHtml .= sprintf(
                '<li>%s</li>',
                $actionLink->getAsHtml()
            );
        }
        
        // Get the CSS to use for the desired button size.
        switch ($buttonSize) {
            case self::BUTTON_SIZE_LARGE:
                $buttonSizeCssString = ' btn-large';
                break;

            case self::BUTTON_SIZE_SMALL:
                $buttonSizeCssString = ' btn-small';
                break;

            case self::BUTTON_SIZE_MEDIUM:
                $buttonSizeCssString = ''; // Nothing needed: this is the default.
                break;

            case self::BUTTON_SIZE_MINI:
                $buttonSizeCssString = ' btn-mini';
                break;

            default:
                throw new Exception(
                    'Unknown button size passed to LinksManager.',
                    1423241139
                );
        }
        
        // Return the resulting HTML.
        return sprintf(
            '<div class="btn-group pull-right">'
            . '<button class="btn%s dropdown-toggle" data-toggle="dropdown">'
            . 'Actions <span class="caret"></span>'
            . '</button>'
            . '<ul class="dropdown-menu pull-right">%s</ul>'
            . '</div>',
            $buttonSizeCssString,
            $entriesHtml
        );
    }
    
    /**
     * Get the list of 'Actions' links that should for shown on the API details
     * page for the given Api and User.
     * 
     * @param Api $api The API whose details are being viewed.
     * @param User $user The User viewing the page.
     * @return ActionLink[] The list of ActionLinks representing the links to
     *     include.
     */
    public static function getApiDetailsActionLinksForUser($api, $user)
    {
        // If lacking either the Api or the User, return an empty array.
        if ( ! ($api instanceof Api)) {
            return array();
        } elseif ( ! ($user instanceof User)) {
            return array();
        }
        
        // Set up an array to hold the list of links.
        $actionLinks = array();
        
        if ($user->hasActiveKeyToApi($api)) {
            foreach ($user->keys as $key) {
                if ($key->api_id === $api->api_id) {
                    $actionLinks[] = new ActionLink(array(
                        '/key/details/',
                        'id' => $key->key_id,
                    ), 'View Key Details', 'list');
                }
            }
        } else {
            $actionLinks[] = new ActionLink(array(
                '/api/request-key/',
                'code' => $api->code,
            ), $api->getRequestKeyText(), 'off');
        }
        
        if ($user->canSeeKeysForApi($api)) {
            $actionLinks[] = new ActionLink(array(
                '/api/active-keys/',
                'code' => $api->code,
            ), 'Show Active Keys', 'ok-sign');
            
            $actionLinks[] = new ActionLink(array(
                '/api/pending-keys/',
                'code' => $api->code,
            ), 'Show Pending Keys', 'question-sign');
        }
        
        if ( ! $api->isPubliclyVisible()) {
            if ($user->canInviteUserToSeeApi($api)) {
                $actionLinks[] = new ActionLink(array(
                    '/api/invite-user/',
                    'code' => $api->code,
                ), 'Invite User', 'user');
            }
            
            if ($user->canInviteDomainToSeeApi($api)) {
                $actionLinks[] = new ActionLink(array(
                    '/api/invite-domain/',
                    'code' => $api->code,
                ), 'Invite Users in Domain', 'globe');
            }
        }
        
        if ($user->hasAdminPrivilegesForApi($api)) {
            
            $actionLinks[] = new ActionLink(
                array(
                    '/api/usage/',
                    'code' => $api->code,
                ),
                'See API Usage',
                'signal'
            );
            
            $actionLinks[] = new ActionLink(
                array(
                    '/api/usage-by-key/',
                    'code' => $api->code,
                ),
                'See API Usage By Key',
                'signal'
            );
            
            if ($api->approvedKeyCount > 0) {
                $actionLinks[] = new ActionLink(sprintf(
                    'mailto:%s?subject=%s&bcc=%s',
                    CHtml::encode($user->email),
                    CHtml::encode($api->display_name . ' API'),
                    CHtml::encode(implode(
                        ',',
                        $api->getEmailAddressesOfUsersWithActiveKeys()
                    ))
                ), 'Email Users With Keys', 'envelope');
            }
            
            $actionLinks[] = new ActionLink(array(
                '/api/edit/',
                'code' => $api->code,
            ), 'Edit API', 'pencil');
        
            $actionLinks[] = new ActionLink(array(
                '/api/delete/',
                'code' => $api->code,
            ), 'Delete API', 'remove');
        }
        
        return $actionLinks;
    }
    
    /**
     * Get the list of 'Actions' links that should be shown on the Dashboard
     * for a card representing a Key.
     * 
     * @param Key $key The Key.
     * @return ActionLink[] The list of ActionLinks representing the links to
     *     include.
     */
    public static function getDashboardKeyActionLinks($key)
    {
        // If lacking the Key, return an empty array.
        if ( ! ($key instanceof Key)) {
            return array();
        }
        
        // Set up an array to hold the list of links.
        $actionLinks = array();
        
        // All a Key needs is a details link.
        $actionLinks[] = new ActionLink(
            array(
                '/key/details/',
                'id' => $key->key_id,
            ),
            'View Details',
            'list'
        );
        
        return $actionLinks;
    }
    
    /**
     * Get the list of 'Actions' links that should for shown on the Key details
     * page for the given Key and User.
     * 
     * @param Key $key The Key whose details are being viewed.
     * @param User $user The User viewing the page.
     * @return ActionLink[] The list of ActionLinks representing the links to
     *     include.
     */
    public static function getKeyDetailsActionLinksForUser($key, $user)
    {
        // If lacking either the Key or the User, return an empty array.
        if ( ! ($key instanceof Key)) {
            return array();
        } elseif ( ! ($user instanceof User)) {
            return array();
        }
        
        // Set up an array to hold the list of links.
        $actionLinks = array();
        
        if ($user->canResetKey($key)) {
            $actionLinks[] = new ActionLink(array(
                '/key/reset/',
                'id' => $key->key_id,
            ), 'Reset Key', 'refresh');
        }
        
        if ($user->canDeleteKey($key)) {
            $actionLinks[] = new ActionLink(array(
                '/key/delete/',
                'id' => $key->key_id,
            ), 'Delete ' . $key->getTypeText(), 'remove');
        }
        
        if ($user->canRevokeKey($key)) {
            $actionLinks[] = new ActionLink(array(
                '/key/revoke/',
                'id' => $key->key_id,
            ), 'Revoke Key', 'remove');
        }
        
        return $actionLinks;
    }
    
    /**
     * Generate the HTML for the given ActionLinks. If only one is given, it
     * will be shown as a single button. If multiple are given, they will be
     * shown as a drop-down list.
     * 
     * @param ActionLink[] $actionLinks The list of ActionLinks representing the
     *     buttons to include.
     * @param string $buttonSize (Optional:) A constant representing the desired
     *     size of the 'Actions' button. Defaults to
     *     LinksManager::BUTTON_SIZE_MEDIUM.
     * @return string The resulting HTML (if any).
     */
    public static function showActionLinks(
        $actionLinks,
        $buttonSize = self::BUTTON_SIZE_MEDIUM
    ) {
        if (count($actionLinks) === 1) {
            return $actionLinks[0]->getAsHtml('btn');
        } else {
            return self::generateActionsDropdownHtml($actionLinks, $buttonSize);
        }
    }
}
