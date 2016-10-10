<?php

use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\User;

return array(
    
    'elements' => array(
        'code' => array(
            'type' => 'text',
            'htmlOptions' => array(
                'hint' => '<i class="icon-warning-sign"></i> Once an API ' .
                          'has been added, its code name cannot be changed, ' .
                          'so be sure you enter it correctly.',
                'placeholder' => 'example-api-name',
                'autofocus' => 'autofocus',
            ),
            'maxlength' => 32,
        ),
        'display_name' => array(
            'type' => 'text',
            'maxlength' => 64,
            'htmlOptions' => array(
                'placeholder' => 'Example API Name',
            ),
        ),
        'brief_description' => array(
            'type' => 'text',
            'maxlength' => 255,
            'htmlOptions' => array(
                'class' => 'input-xxlarge',
                'hint' => '<i class="icon-info-sign"></i> Enter a one-' .
                          'sentence summary of the purpose of this API.',
            ),
        ),
        'owner_id' => array(
            'type' => 'dropdownlist',
            'data' => CMap::mergeArray(
                array('' => '-- none --'),
                CHtml::encodeArray(CHtml::listData(
                    User::model()->findAllByAttributes(array(
                        'role' => array(User::ROLE_OWNER, User::ROLE_ADMIN),
                    )),
                    'user_id',
                    function($user) {
                        return $user->display_name . ' (' . $user->email . ')';
                    }
                ))
            ),
            'visible' => \Yii::app()->user->checkAccess('admin'),
        ),
        'endpoint' => array(
            'htmlOptions' => array(
                'placeholder' => 'your-domain.com',
                'class' => 'input-xlarge',
            ),
            'type' => 'text',
        ),
        'default_path' => array(
            'htmlOptions' => array(
                'placeholder' => '/api/path',
                'class' => 'input-xlarge',
            ),
            'type' => 'text',
        ),
        'endpoint_timeout' => array(
            'htmlOptions' => array(
                'placeholder' => '10',
                'class' => 'input-mini',
            ),
            'type' => 'text',
        ),
        'protocol' => array(
            'type' => 'radiobuttonlist_inline',
            'data' => Api::getProtocols()
        ),
        'strict_ssl' => array(
            'type' => 'radiobuttonlist_inline',
            'data' => Api::getStrictSsls()
        ),
        'require_signature' => array(
            'type' => 'radiobuttonlist_inline',
            'data' => Api::getRequireSignatureOptions(),
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> This controls '
                . 'whether calls to this API require a signature (and thus '
                . 'whether keys to this API will have a secret value).',
            ),
        ),
        'queries_second' => array(
            'htmlOptions' => array(
                'placeholder' => '3',
                'class' => 'input-mini',
            ),
            'type' => 'text',
        ),
        'queries_day' => array(
            'htmlOptions' => array(
                'placeholder' => '1000',
                'class' => 'input-mini',
            ),
            'type' => 'text',
        ),
        'visibility' => array(
            'type' => 'dropdownlist',
            'data' => Api::getVisibilityDescriptions(),
        ),
        'approval_type' => array(
            'type' => 'dropdownlist',
            'data' => Api::getApprovalTypes(),
        ),
        'technical_support' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Enter a website, ' .
                          'email address, phone number, or some other way ' .
                          'for people to get technical support for this API.',
                'class' => 'input-xxlarge',
            ),
            'type' => 'text',
        ),
        'customer_support' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Enter a website, ' .
                          'email address, phone number, or some other way ' .
                          'for people to get customer support for this API.',
                'class' => 'input-xxlarge',
            ),
            'type' => 'text',
        ),
        'how_to_get' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Use <a href="' .
                          'http://en.wikipedia.org/wiki/Markdown" target="' .
                          '_blank">Markdown</a> syntax to format your ' .
                          'instructions to users about how to get access to ' .
                          'this API.',
                'style' => 'height: 25ex; width: 90%;'
            ),
            'type' => 'textarea',
        ),
        'embedded_docs_url' => array(
            'type' => 'text',
            'maxlength' => 255,
            'htmlOptions' => array(
                'class' => 'input-xxlarge',
                'hint' => '<i class="icon-info-sign"></i> If you would like ' .
                          'to embed a Google Doc as the documentation for this ' .
                          'API, enter that URL here. <br />Example: <code>' .
                          'https://docs.google.com/document/d/.../pub?embedded=true' .
                          '</code>',
            ),
        ),
        'documentation' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Use <a href="' .
                          'http://en.wikipedia.org/wiki/Markdown" target="' .
                          '_blank">Markdown</a> syntax to format your ' .
                          'documentation.',
                'style' => 'height: 25ex; width: 90%;'
            ),
            'type' => 'textarea',
        ),
        'terms' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-lock"></i> If you provide '
                . 'terms, anyone requesting a new key will be required to '
                . 'accept these Terms. <br />'
                . '<i class="icon-info-sign"></i> Use <a href="'
                . 'http://en.wikipedia.org/wiki/Markdown" target="_blank">'
                . 'Markdown</a> syntax to format your API\'s Terms of Use (if '
                . 'applicable).',
                'style' => 'height: 25ex; width: 90%;',
            ),
            'type' => 'textarea',
        ),
    ),
    
    'buttons' => array(
        'submit' => array(
            'buttonType' => 'submit',
            'icon' => 'ok white',
            'label' => 'Save',
            'type' => 'primary'
        ),
    ),
);
