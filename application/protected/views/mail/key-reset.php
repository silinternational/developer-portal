<p>
    Hello <?php echo $key->user->first_name; ?>,
</p>
<p>
    Your API key for the <?php echo $api->display_name ?> API has been reset.
    If you did not request this or believe this to be an error we recommend that 
    you login and reset the key again to be sure your key has not been compromised.
</p>
<p>
    <strong>
        <a href="<?php echo Yii::app()->createAbsoluteUrl('/key/mine/'); ?>" 
           title="Manage My Keys">
            Manage My Keys
        </a>
    </strong>
</p>
<p>
    Key reset at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?><br />
    Key reset by user <?php echo Yii::app()->user->user->display_name; ?>
</p>