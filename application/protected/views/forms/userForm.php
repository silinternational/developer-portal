<?php

use Sil\DevPortal\models\User;

return array(
    
    'elements' => array(
        'role' => array(
            'type' => 'dropdownlist',
            'data' => (
                array('' => '-- Select one: --') +
                User::getRoles()
            ),
        ),
        'status' => array(
            'type' => 'dropdownlist',
            'data' => (
                array('' => '-- Select one: --') +
                User::getStatuses()
            ),
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
