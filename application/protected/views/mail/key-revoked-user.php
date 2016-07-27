<?php
/* @var $api Api */
/* @var $key Key */
/* @var $keyOwner User */
?>
<p>
    Hello <?= \CHtml::encode($keyOwner->first_name) ?>,
</p>
<p>
    Your key to the <?php echo \CHtml::encode($api->display_name); ?> API has been
    revoked.
</p>
<p><?= sprintf(
    '<a href="%s">Click here to see the key\'s details</a>',
    $this->createAbsoluteUrl('/key/details/', array(
        'id' => $key->key_id,
    ))
); ?></p>
<p>
    Request submitted at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?>
</p>
