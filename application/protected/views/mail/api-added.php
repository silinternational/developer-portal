<?php
/* @var $addedByUser \Sil\DevPortal\models\User */
/* @var $api \Sil\DevPortal\models\Api */
?>
<p>A new API has been added to the API Developer Portal. </p>
<p><?= sprintf(
    '%s%s added an API named "%s" (%s). <a href="%s">Click here</a> for more '
    . 'information.',
    CHtml::encode($addedByUser ? $addedByUser->display_name : 'Someone'),
    CHtml::encode($addedByUser ? ' (' . $addedByUser->email . ')' : ''),
    CHtml::encode($api->display_name),
    CHtml::encode($api->getInternalApiEndpoint()),
    \Yii::app()->createAbsoluteUrl('/api/details/', array(
        'code' => $api->code,
    ))
); ?></p>
<p>
    API Added <?php echo date(Yii::app()->params['friendlyDateFormat']); ?>
</p>
