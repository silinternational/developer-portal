<?php
/* @var $this KeyRequestController */
/* @var $key Key */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Key Details' => array(
        '/key/details/',
        'id' => $key->key_id,
    ),
    'Delete Key',
);

$this->pageTitle = 'Delete Key';

?>
<div class="row">
    <div class="span12">
        <dl class="dl-horizontal">
            <dt>API</dt>
            <dd>
                <?php echo sprintf(
                    '<a href="%s">%s</a>',
                    $this->createUrl('/api/details/', array(
                        'code' => $key->api->code,
                    )),
                    CHtml::encode(
                        $key->api->display_name . ' (' . $key->api->code . ')'
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
                            'id' => $key->user_id,
                        )),
                        CHtml::encode($key->user->display_name)
                    );
                } else {
                    echo CHtml::encode($key->user->display_name);
                }
                ?>
            </dd>

            <dt>Purpose</dt>
            <dd>
                <?php
                echo CHtml::encode($key->purpose);
                ?>&nbsp;
            </dd>

            <dt>Domain</dt>
            <dd>
                <?php
                echo CHtml::encode($key->domain);
                ?>&nbsp;
            </dd>

            <dt>Status</dt>
            <dd>
                <?php
                echo $key->getStyledStatusHtml();
                ?>&nbsp;
            </dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <p>Do you really want to delete this key? </p>
        <ul class="inline">
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'NO - Cancel',
                    'url' => $this->createUrl(
                        '/key/details/',
                        array('id' => $key->key_id)
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
