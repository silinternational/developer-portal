<?php
/* @var $owner User */
/* @var $api Api */
/* @var $keyRequest KeyRequest */
/* @var $requestingUser User */
?>
<p>
    Hello <?php echo ($owner ? CHtml::encode($owner->first_name) : 'API Developer Portal administrator'); ?>,
</p>
<p>
    <?php echo sprintf(
        'The request that %s made %s for access to the %s API has been '
        . 'deleted (i.e. - the key request has been deleted). The key '
        . 'request\'s status was "%s".',
        CHtml::encode($requestingUser->display_name),
        Utils::getFriendlyDate($keyRequest->created),
        CHtml::encode($api->display_name),
        $keyRequest->status
    );
    ?>
</p>
<p>
    Request deleted <?php echo Utils::getFriendlyDate('now'); ?> by <?php
    echo CHtml::encode(Yii::app()->user->user->display_name); ?>.
</p>
