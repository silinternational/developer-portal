<?php
/* @var $this \ApiController */
/* @var $api \Api */
/* @var $apiVisibilityUser \ApiVisibilityUser */
/* @var $currentUser \User */
/* @var $hasDependentKey boolean */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'Uninvite User',
);

$this->pageTitle = 'Uninvite User';

?>
<div class="row">
    <div class="span12">
        <dl class="dl-horizontal">
            <dt>API</dt>
            <dd>
                <?php
                if ($api->isVisibleToUser($currentUser)) {
                    echo sprintf(
                        '<a href="%s">%s</a>',
                        $this->createUrl('/api/details/', array(
                            'code' => $api->code,
                        )),
                        \CHtml::encode($api->display_name)
                    );
                } else {
                    \CHtml::encode($api->display_name);
                }
                ?>&nbsp;
            </dd>

            <dt>User</dt>
            <dd><?= \CHtml::encode($apiVisibilityUser->getInviteeDisplayText()); ?>&nbsp;</dd>
        </dl>
    </div>
</div>
<?php if ( ! $hasDependentKey): ?>
    <div class="row">
        <div class="span11 offset1">
            <?php $form = $this->beginWidget('CActiveForm'); ?>

            <p>Do you really want to remove this person's permission to see this API?</p>
            <ul class="inline">
                <li>
                    <?php

                    $this->widget('bootstrap.widgets.TbButton', array(
                        'buttonType' => 'link',
                        'icon' => 'ban-circle',
                        'label' => 'NO - Cancel',
                        'url' => $this->createUrl(
                            '/api/invited-users/',
                            array('code' => $api->code)
                        ),
                    ));

                    ?>
                </li>
                <li>
                    <?php

                    $this->widget('bootstrap.widgets.TbButton', array(
                        'buttonType' => 'submit',
                        'icon' => 'remove',
                        'label' => 'YES - Uninvite',
                        'type' => 'danger'
                    ));

                    ?>
                </li>
            </ul>

            <?php $this->endWidget(); ?>
        </div>
    </div>
<?php endif; ?>
