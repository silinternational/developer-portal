<?php
/* @var $this ApiController */
/* @var $key Key */

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
        
        echo $form->errorSummary($key);
        
        // Show the necessary input fields.
        ?>
        <label>
            <p>What do you intend to use this API for? </p>
            <?php echo $form->textArea($key, 'purpose'); ?>
        </label>
        <label>
            <p>What url/domain do you plan to use this API on? </p>
            <?php echo $form->textField($key, 'domain'); ?>
        </label>
        <p class="muted">
            <b>Note:</b> The owner of this API will be able to see your name and
            email address if you request a key.
        </p>
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
