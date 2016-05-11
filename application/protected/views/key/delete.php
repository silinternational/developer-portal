<?php
/* @var $this KeyController */
/* @var $key Key */

// Figure out whether to say delete or revoke.
$deleteOrRevoke = $key->isOwnedBy(\Yii::app()->user->user) ? 'delete' : 'revoke';

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Keys' => array('/key/'),
    ucfirst($deleteOrRevoke) . ' Key',
);

$this->pageTitle = ucfirst($deleteOrRevoke) . ' Key';

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

            <dt>Value</dt>
            <dd><?php echo CHtml::encode($key->value); ?>&nbsp;</dd>
            
            <?php
            if ($key->keyRequest !== null) {
                ?>
                <dt>Purpose</dt>
                <dd>
                    <?php
                    echo CHtml::encode($key->keyRequest->purpose);
                    ?>&nbsp;
                </dd>

                <dt>Domain</dt>
                <dd>
                    <?php
                    echo CHtml::encode($key->keyRequest->domain);
                    ?>&nbsp;
                </dd>
                <?php
            }
            ?>
        </dl>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <p>Do you really want to <?php echo $deleteOrRevoke; ?> this key? </p>
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
                    'label' => 'YES - ' . ucfirst($deleteOrRevoke),
                    'type' => 'danger'
                ));

                ?>
            </li>
        </ul>

        <?php $this->endWidget(); ?>
    </div>
</div>
