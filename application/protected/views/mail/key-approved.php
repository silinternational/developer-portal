<?php
/* @var $api \Sil\DevPortal\models\Api */
/* @var $key \Sil\DevPortal\models\Key */
/* @var $user \Sil\DevPortal\models\User */
?>
<p>
    <?= \CHtml::encode($user->display_name); ?>,
</p>
<p>
    The key to the <?= \CHtml::encode($api->display_name); ?> API that you
    requested has been approved. You can view the key details on the 
    <?= \CHtml::encode(\Yii::app()->name); ?>.
</p>
<p>
    <strong><a href="<?= \Yii::app()->createAbsoluteUrl('/key/mine/'); ?>"
               title="View My Keys">View My Keys</a></strong>
</p>
<p>
    Key approved at <?= date(Yii::app()->params['friendlyDateFormat']); ?><br />
    Key approved by <?= \CHtml::encode(\Yii::app()->user->user->display_name); ?>
</p>