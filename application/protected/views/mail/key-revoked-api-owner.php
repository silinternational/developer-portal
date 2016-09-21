<?php
/* @var $apiOwner \Sil\DevPortal\models\User */
/* @var $api \Sil\DevPortal\models\Api */
/* @var $key \Sil\DevPortal\models\Key */
/* @var $keyOwner \Sil\DevPortal\models\User */
?>
<p>
    Hello <?php echo ($apiOwner ? $apiOwner->first_name : 'API Developer Portal administrator'); ?>,
</p>
<p>
    A key to the <?php echo \CHtml::encode($api->display_name); ?> API has been
    revoked. The key belongs to 
    <?php echo \CHtml::encode($keyOwner->display_name); ?>. 
</p>
<p>
    <?php
    echo sprintf(
        '<a href="%s">Click here to see the key\'s details</a>',
        \Yii::app()->createAbsoluteUrl('/key/details/', array(
            'id' => $key->key_id,
        ))
    );
    ?>
</p>
<p>
    Request submitted at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?>
</p>
