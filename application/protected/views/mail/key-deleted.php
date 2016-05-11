<p>
    Hello <?php echo $key->user->first_name; ?>,
</p>
<p>
    Your API key for the <?php echo $api->display_name ?> API has been deleted.
    If you did not request this or believe this to be an error we recommend that 
    you login and request the key again.
</p>
<p>
    <strong>
        <a href="<?php echo Yii::app()->createAbsoluteUrl('/api/request-key/', array('code' => $api->code)); ?>" 
           title="Request a New Key">
            Request a New Key
        </a>
    </strong>
</p>
<p>
    Key deleted at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?><br />
    Key deleted by user <?php echo Yii::app()->user->user->display_name; ?>
</p>