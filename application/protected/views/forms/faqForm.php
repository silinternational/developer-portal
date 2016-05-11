<?php

/*
 * @property string $question
 * @property string $answer
 * @property integer $order
 */
return array(
    
    'elements' => array(
        'question' => array(
            'type' => 'text',
            'htmlOptions' => array(
                'style' => 'width: 90%;'
            ),
            'maxlength' => 255,
        ),
        'answer' => array(
            'htmlOptions' => array(
                'hint' => '<i class="icon-info-sign"></i> Use <a href="' .
                          'http://en.wikipedia.org/wiki/Markdown" target="' .
                          '_blank">Markdown</a> syntax to format the ' .
                          'answer.',
                'style' => 'height: 35ex; width: 90%;'
            ),
            'type' => 'textarea',
        ),
        '<hr />',
        'order' => array(
            'type' => 'text',
            'htmlOptions' => array(
                'maxlength' => 6,
                'placeholder' => '#',
                'style' => 'width: 7ex;',
                'title' => 'lower = earlier, higher = later'
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
