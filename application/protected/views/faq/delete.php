<?php
/* @var $this FaqController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'FAQs' => array('/faq/'),
    $form->model->question => array(
        '/faq/details/',
        'id' => $form->model->faq_id,
    ),
    'Delete FAQ',
);

$this->pageTitle = 'Delete FAQ';

?>
<div class="row">
    <div class="span7 offset2">

        <h3 class="text-error">Are you sure?</h3>
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <span class="help-block control-group error">
            <strong class="control-label">
                <span style="text-decoration: underline;">WARNING</span>:
                This will completely delete this FAQ.
            </strong>
        </span>
        <div class="row">
            <div class="span2 offset1">
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'NO - Cancel',
                    'url' => $this->createUrl('/faq/details/', array(
                        'id' => $faq->faq_id,
                    )),
                ));

                ?>
            </div>
            <div class="span1">&nbsp;</div>
            <div class="span2">
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'submit',
                    'icon' => 'remove',
                    'label' => 'YES - Delete',
                    'type' => 'danger'
                ));

                ?>
            </div>
        </div>

        <?php $this->endWidget(); ?>
    </div>
</div>
<div class="row">
    <div class="span7 offset1">

        <h3>FAQ Info</h3>
        <dl class="dl-horizontal">
            <dt>&nbsp;Question</dt>
            <dd><?php echo CHtml::encode($faq->question); ?>&nbsp;</dd>

            <dt>&nbsp;Answer</dt>
            <dd>
                <div class="well">
                    <?php

                    $this->beginWidget('CMarkdown',
                                       array('purifyOutput' => false));
                    echo $faq->answer;
                    $this->endWidget();

                    ?>
                </div>
            </dd>

            <dt>&nbsp;Order</dt>
            <dd><?php echo CHtml::encode($faq->order); ?>&nbsp;</dd>

            <dt>&nbsp;Created</dt>
            <dd><?php echo Utils::getFriendlyDate($faq->created); ?>&nbsp;</dd>

            <dt>&nbsp;Updated</dt>
            <dd><?php echo Utils::getFriendlyDate($faq->updated); ?>&nbsp;</dd>
        </dl>
    </div>
</div>



