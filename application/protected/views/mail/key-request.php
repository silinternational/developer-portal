<?php
/* @var $owner \Sil\DevPortal\models\User */
/* @var $api \Sil\DevPortal\models\Api */
/* @var $key \Sil\DevPortal\models\Key */
/* @var $requestingUser \Sil\DevPortal\models\User */
?>
<p>
    <?php echo ($owner ? CHtml::encode($owner->display_name) : 'API Developer Portal administrator'); ?>,
</p>
<p>
    <?php echo CHtml::encode($requestingUser->display_name); ?> has requested access to the 
    <?php echo CHtml::encode($api->display_name); ?> API. 
    <?php
    echo sprintf(
        '<a href="%s" title="%s">Click here</a> to view and approve or reject '
        . 'this request.',
        \Yii::app()->createAbsoluteUrl('/key/details/', array(
            'id' => $key->key_id,
        )),
        'View key request'
    );
    ?>
</p>
<p>
    Request submitted at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?>
</p>
