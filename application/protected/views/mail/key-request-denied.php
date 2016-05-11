<?php
/* @var $keyRequest KeyRequest */

// Make sure we have retrieved the 'updated' time from the database.
$keyRequest->refresh();
?>
<p>
    Hello <?php echo $keyRequest->user->first_name; ?>,
</p>
<p>
    Your request for a key to the <?php echo CHtml::encode(
        $keyRequest->api->display_name
    ); ?> API was denied. To see the details of your request, <a href="<?php
    echo \Yii::app()->createAbsoluteUrl('/key-request/details/', array(
        'id' => $keyRequest->key_request_id,
    )); ?>">click here</a>.
</p>
<p>
    <?php
    echo sprintf(
        'Key requested %s <br />Key request denied %s by %s',
        Utils::getFriendlyDate($keyRequest->created),
        Utils::getFriendlyDate($keyRequest->updated),
        CHtml::encode($keyRequest->processedBy->display_name)
    );
    ?>
</p>
