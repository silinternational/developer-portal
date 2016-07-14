<?php
/* @var $this \ApiController */
/* @var $api \Api */
/* @var $apiVisibilityUser \ApiVisibilityUser */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $api->display_name => array(
        '/api/details/',
        'code' => $api->code,
    ),
    'Invite User',
);

$this->pageTitle = 'Invite User';

?>
<div class="row">
    <div class="span112">
        <?php
        
        /** @var \TbActiveForm $form */
        $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'inlineErrors' => true,
        ));
        
        echo $form->errorSummary($apiVisibilityUser);
        
        // Show the necessary input fields.
        ?>
        <label>
            <p>
                Enter someone's email address to invite them to see the
                "<?= \CHtml::encode($api->display_name); ?>" API:
            </p>
            <?= $form->textField($apiVisibilityUser, 'invited_user_email'); ?>
        </label>
        <?php
        
        // Show the submit button.
        ?><div><?php
        $this->widget('bootstrap.widgets.TbButton', array(
            'buttonType' => 'submit',
            'icon' => 'envelope white',
            'label' => 'Invite',
            'type' => 'primary'
        ));
        ?></div><?php
        
        // End the form.
        $this->endWidget();
        
        ?>
    </div>
</div>
