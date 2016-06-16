<?php
/* @var $api Api */
/* @var $key Key */
/* @var $user User */
?>
<p>
    Hello <?php echo CHtml::encode($user->first_name); ?>,
</p>
<p>
    The key to the <?php echo CHtml::encode($api->display_name); ?> API that you
    requested has been approved. You can view the key details on the 
    <?php echo CHtml::encode(\Yii::app()->name); ?>.
</p>
<p>
    <strong><a href="<?php echo Yii::app()->createAbsoluteUrl('/key/mine/'); ?>"
       title="View My Keys">View My Keys</a></strong>
</p>
<p>
    Key approved at <?php echo date(Yii::app()->params['friendlyDateFormat']); ?><br />
    Key approved by <?php echo CHtml::encode(\Yii::app()->user->user->display_name); ?>
</p>