<?php
/* @var $this KeyRequestController */
/* @var $keyRequest KeyRequest */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Key Request Details' => array(
        '/key-request/details/',
        'id' => $keyRequest->key_request_id,
    ),
    'Delete Key Request',
);

$this->pageTitle = 'Delete Key Request';

?>
<div class="row">
    <div class="span12">
        <dl class="dl-horizontal">
            <dt>API</dt>
            <dd>
                <?php echo sprintf(
                    '<a href="%s">%s</a>',
                    $this->createUrl('/api/details/', array(
                        'code' => $keyRequest->api->code,
                    )),
                    CHtml::encode(
                        $keyRequest->api->display_name . ' (' . $keyRequest->api->code . ')'
                    )
                ); ?>
            </dd>
            
            <dt>User</dt>
            <dd>
                <?php
                if (\Yii::app()->user->checkAccess('admin')) {
                    echo sprintf(
                        '<a href="%s">%s</a>',
                        $this->createUrl('/user/details/', array(
                            'id' => $keyRequest->user_id,
                        )),
                        CHtml::encode($keyRequest->user->display_name)
                    );
                } else {
                    echo CHtml::encode($keyRequest->user->display_name);
                }
                ?>
            </dd>

            <dt>Purpose</dt>
            <dd>
                <?php
                echo CHtml::encode($keyRequest->purpose);
                ?>&nbsp;
            </dd>

            <dt>Domain</dt>
            <dd>
                <?php
                echo CHtml::encode($keyRequest->domain);
                ?>&nbsp;
            </dd>

            <dt>Status</dt>
            <dd>
                <?php
                echo $keyRequest->getStyledStatusHtml();
                ?>&nbsp;
            </dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <p>Do you really want to delete this key request? </p>
        <ul class="inline">
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'NO - Cancel',
                    'url' => $this->createUrl(
                        '/key-request/details/',
                        array('id' => $keyRequest->key_request_id)
                    ),
                ));

                ?>
            </li>
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'submit',
                    'icon' => 'remove',
                    'label' => 'YES - Delete',
                    'type' => 'danger'
                ));

                ?>
            </li>
        </ul>

        <?php $this->endWidget(); ?>
    </div>
</div>
