<?php
/* @var $api Api */
/* @var $apiVisibilityUser \ApiVisibilityUser */
/* @var $invitedByUser User */
/* @var $inviteeEmailAddress string */
/* @var $this Controller */
?>
<p>
    <?= \CHtml::encode($invitedByUser->getDisplayName()); ?> has invited you to
    see the "<?= \CHtml::encode($api->display_name); ?>" API on the
    <?= \CHtml::encode(\Yii::app()->name); ?>. To go there now, visit the
    following URL: <br />
    <?php
    $escapedUrl = \CHtml::encode($this->createAbsoluteUrl('/api/details', array(
        'code' => $api->code,
    )));
    echo sprintf(
        '<a href="%s">%s</a>',
        $escapedUrl,
        $escapedUrl
    ); ?>
</p>
<p>
    NOTE: You will need to log in to the <?= \CHtml::encode(\Yii::app()->name); ?>
    using this email address (<?= \CHtml::encode($inviteeEmailAddress); ?>) for
    your permission to view this API to take effect.
</p>
