<h1>User Identity from Yii::app()->user</h1>
<pre>
<?php echo print_r(Yii::app()->user, true); ?>
</pre>
<hr />
Role: <?php echo Yii::app()->user->getRole(); ?>
<hr />
<h1>User Model from Yii::app()->user->user</h1>
<pre>
<?php echo print_r(Yii::app()->user->user, true); ?>
</pre>
<hr />
<h1>Access Groups from Yii::app()->user->accessGroups</h1>
<pre>
<?php echo print_r(Yii::app()->user->accessGroups,true); ?>
</pre>