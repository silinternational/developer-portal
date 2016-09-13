<?php
/* @var $this SiteTextController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Site Texts' => array('/site-text/'),
    $form->model->name => array(
        '/site-text/details/',
        'id' => $form->model->site_text_id,
    ),
    'Delete Site Text',
);

$this->pageTitle = 'Delete Site Text';

?>
<div class="row">
    <div class="span7 offset2">

        <h3 class="text-error">Are you sure?</h3>
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <span class="help-block control-group error">
            <strong class="control-label">
                <span style="text-decoration: underline;">WARNING</span>:
                This will completely delete this Site Text.
            </strong>
        </span>
        <div class="row">
            <div class="span2 offset1">
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'NO - Cancel',
                    'url' => $this->createUrl('/site-text/details/', array(
                        'id' => $siteText->site_text_id,
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

        <h3>Site Text Info</h3>
        <dl class="dl-horizontal">
            <dt>&nbsp;Name</dt>
            <dd><?php echo CHtml::encode($siteText->name); ?>&nbsp;</dd>

            <dt>&nbsp;Content</dt>
            <dd>
                <div class="well">
                    <?php

                    $this->beginWidget('CMarkdown',
                                       array('purifyOutput' => false));
                    echo $siteText->markdown_content;
                    $this->endWidget();

                    ?>
                </div>
            </dd>

            <dt>&nbsp;Order</dt>
            <dd><?php echo CHtml::encode($siteText->order); ?>&nbsp;</dd>

            <dt>&nbsp;Created</dt>
            <dd><?php echo Utils::getFriendlyDate($siteText->created); ?>&nbsp;</dd>

            <dt>&nbsp;Updated</dt>
            <dd><?php echo Utils::getFriendlyDate($siteText->updated); ?>&nbsp;</dd>
        </dl>
    </div>
</div>



