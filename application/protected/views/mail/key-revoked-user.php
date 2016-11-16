<?php
/* @var $api \Sil\DevPortal\models\Api */
/* @var $key \Sil\DevPortal\models\Key */
/* @var $keyOwner \Sil\DevPortal\models\User */
?>
<p>
    <?= \CHtml::encode($keyOwner->display_name) ?>,
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
