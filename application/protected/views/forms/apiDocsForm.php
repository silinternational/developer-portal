<?php

return array(
    
    'elements' => array(
        'documentation' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Use <a href="' .
                          'http://en.wikipedia.org/wiki/Markdown" target="' .
                          '_blank">Markdown</a> syntax to format your ' .
                          'documentation.',
                'style' => 'height: 50ex; width: 90%;'
            ),
            'type' => 'textarea',
        )
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
