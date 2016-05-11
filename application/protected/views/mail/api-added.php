<?php
/* @var $addedByUser User */
/* @var $api Api */
?>
<p>A new API has been added to the API Developer Portal. </p>
<p><?php echo sprintf(
    '%s%s added an API named "%s". <a href="%s">Click here</a> for more '
    . 'information.',
    CHtml::encode($addedByUser ? $addedByUser->display_name : 'Someone'),
    ($addedByUser ? ' (' . $addedByUser->email . ')' : ''),
    CHtml::encode($api->display_name),
    \Yii::app()->createAbsoluteUrl('/api/details/', array(
        'code' => $api->code,
    ))
); ?>
</p>
<p>
    API Added <?php echo date(Yii::app()->params['friendlyDateFormat']); ?>
</p>
