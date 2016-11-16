<?php

return array(
    
    'elements' => array(
        'name' => array(
            'type' => 'text',
            'htmlOptions' => array(
                'style' => 'width: 90%;'
            ),
            'maxlength' => 255,
        ),
        'markdown_content' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Use <a href="' .
                          'http://en.wikipedia.org/wiki/Markdown" target="' .
                          '_blank">Markdown</a> syntax to format the ' .
                          'content.',
                'style' => 'height: 35ex; width: 90%;'
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
