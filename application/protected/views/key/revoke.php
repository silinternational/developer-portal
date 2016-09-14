<?php
/* @var $this \Sil\DevPortal\controllers\KeyController */
/* @var $key \Key */
/* @var $currentUser \User */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Keys' => array('/key/'),
    'Revoke Key',
);

$this->pageTitle = 'Revoke Key';

?>
<div class="row">
    <div class="span12">
        <?php
        $this->renderPartial('//partials/key-info', array(
            'key' => $key,
            'currentUser' => $currentUser,
        ));
        ?>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <p>Do you really want to revoke this key? </p>
        <ul class="inline">
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'NO - Cancel',
                    'url' => $this->createUrl(
                        '/key/details/',
                        array('id' => (int)$key->key_id)
                    ),
                ));

                ?>
            </li>
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'submit',
                    'icon' => 'remove',
                    'label' => 'YES - Revoke',
                    'type' => 'danger'
                ));

                ?>
            </li>
        </ul>

        <?php $this->endWidget(); ?>
    </div>
</div>
