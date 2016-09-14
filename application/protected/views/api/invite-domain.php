<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $api \Sil\DevPortal\models\Api */
/* @var $apiVisibilityDomain \Sil\DevPortal\models\ApiVisibilityDomain */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $api->display_name => array(
        '/api/details/',
        'code' => $api->code,
    ),
    'Invite Domain',
);

$this->pageTitle = 'Invite Domain';

?>
<div class="row">
    <div class="span112">
        <?php
        
        /** @var \TbActiveForm $form */
        $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'inlineErrors' => true,
        ));
        
        echo $form->errorSummary($apiVisibilityDomain);
        
        // Show the necessary input fields.
        ?>
        <label>
            <p>
                Enter a domain name to allow everyone with an email address
                ending with that domain name to see the
                "<?= \CHtml::encode($api->display_name); ?>" API:
            </p>
            <?= $form->textField($apiVisibilityDomain, 'domain'); ?>
        </label>
        <?php
        
        // Show the submit button.
        ?><div><?php
        $this->widget('bootstrap.widgets.TbButton', array(
            'buttonType' => 'submit',
            'icon' => 'globe white',
            'label' => 'Invite',
            'type' => 'primary'
        ));
        ?></div><?php
        
        // End the form.
        $this->endWidget();
        
        ?>
    </div>
</div>
