<p>
    Hello <?php echo $key->user->first_name; ?>,
</p>
<p>
    A new key has been created for you to use with the 
    <?php echo $api->display_name ?> API. You can view the key details on the
    <?php echo CHtml::encode(Yii::app()->name); ?>.
</p>
<p>
    <strong><a href="<?php echo Yii::app()->createAbsoluteUrl('/key/mine/'); ?>"
       title="View My Keys">View My Keys</a></strong>
</p>
<p>
    Key created at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?><br />
    Key created by <?php echo Yii::app()->user->user->display_name; ?>
</p>