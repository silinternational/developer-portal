<?php
/* @var $this \Sil\DevPortal\controllers\SiteController */
/* @var $error array */

$this->breadcrumbs = array(
	'Error',
);

$this->pageTitle = 'Error ' . $code;

?>
<h2>Error <?php echo $code; ?></h2>
<div style="float: right; color: #fff;">
    <?php if (isset($errorCode)) { echo CHtml::encode('Code ' . $errorCode); } ?>
</div>
<div class="error">
    <?php echo nl2br(CHtml::encode($message)); ?>
</div>