<?php

Yii::import('bootstrap.form.*');

class YbHorizForm extends TbForm
{
    public $activeForm = array(
        'class' => 'bootstrap.widgets.TbActiveForm',
        'type' => 'horizontal'
    );
    
    // Whether to show the error summary at the top of the form.
    public $showErrorSummary = true;
    
    //public function renderElements()
    //{
    //    $output='';
    //    foreach ($this->getElements() as $element) {
    //
    //        $element->layout = '<div class="control-label">{label}</div> ' .
    //                '<div class="controls">' .
    //                '{input} <span class="help-inline">{hint}</span> {error}' .
    //                '</div>';
    //        $output .= '<div class="control-group">' .
    //                       $this->renderElement($element) .
    //                   '</div>';
    //    }
    //    return $output;
    //}
}
