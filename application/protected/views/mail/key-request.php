<?php
/* @var $owner User */
/* @var $api Api */
/* @var $keyRequest KeyRequest */
/* @var $requestingUser User */
?>
<p>
    Hello <?php echo ($owner ? $owner->first_name : 'API Developer Portal administrator'); ?>,
</p>
<p>
    <?php echo $requestingUser->display_name; ?> has requested access to the 
    <?php echo $api->display_name ?> API. 
    <?php
    echo sprintf(
        '<a href="%s" title="%s">Click here</a> to view and approve or reject '
        . 'this request.',
        \Yii::app()->createAbsoluteUrl('/key-request/details/', array(
            'id' => $keyRequest->key_request_id,
        )),
        'View key request'
    );
    ?>
</p>
<p>
    Request submitted at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?>
</p>