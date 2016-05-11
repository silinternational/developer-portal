<?php
/* @var $this ApiController */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'Request Key',
);

$this->pageTitle = 'Request Key';

?>
<dl class="dl-horizontal">
    <dt>API</dt>
    <dd>
        <?php
        echo sprintf(
            '<a href="%s">%s</a>',
            $this->createUrl('/api/details/', array('code' => $api->code)),
            CHtml::encode($api->display_name . ' (' . $api->code . ')')
        );
        ?>
    </dd>
</dl>
<div class="row">
    <div class="span11 offset1">
        <?php
        
        /** @var BootActiveForm $form */
        $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'inlineErrors' => true,
        ));
        
        echo $form->errorSummary($model);
        
        // Show the necessary input fields.
        ?>
        <label>
            <p>What do you intend to use this API for? </p>
            <?php echo $form->textArea($model,'purpose'); ?>
        </label>
        <label>
            <p>What url/domain do you plan to use this API on? </p>
            <?php echo $form->textField($model,'domain'); ?>
        </label>
        <?php
        
        // Show the submit button.
        ?><div><?php
        $this->widget('bootstrap.widgets.TbButton', array(
            'buttonType' => 'submit',
            'icon' => 'off white',
            'label' => 'Request',
            'type' => 'primary'
        ));
        ?></div><?php
        
        // End the form.
        $this->endWidget();
        
        ?>
    </div>
</div>
