<?php
/* @var $this \Sil\DevPortal\controllers\SystemController */

$this->pageTitle = 'Resync ApiAxle';
$this->breadcrumbs += array(
    'System' => array('/system/'),
    $this->pageTitle,
);

?>
</div>
<div class="row">
    <div class="span12 text-center">
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <p>
            Do you really want to resync the list of APIs and keys from the
            database to ApiAxle?
        </p>
        <ul class="inline">
            <li>
                <?php
                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'submit',
                    'icon' => 'remove',
                    'label' => 'YES - Resync ApiAxle',
                    'type' => 'danger'
                ));
                ?>
            </li>
        </ul>

        <?php $this->endWidget(); ?>
    </div>
</div>
