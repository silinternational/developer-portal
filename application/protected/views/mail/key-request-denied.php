<?php
/* @var $key Key */

// Make sure we have retrieved the 'updated' time from the database.
$key->refresh(); /** @todo Move this refresh() call out of the view file. */
?>
<p>
    Hello <?php echo $key->user->first_name; ?>,
</p>
<p>
    Your request for a key to the <?php echo CHtml::encode(
        $key->api->display_name
    ); ?> API was denied. To see the details of your request, <a href="<?php
    echo \Yii::app()->createAbsoluteUrl('/key/details/', array(
        'id' => $key->key_id,
    )); ?>">click here</a>.
</p>
<p>
    <?php
    echo sprintf(
        'Key requested %s <br />Key request denied %s by %s',
        Utils::getFriendlyDate($key->created),
        Utils::getFriendlyDate($key->updated),
        CHtml::encode($key->processedBy->display_name)
    );
    ?>
</p>
