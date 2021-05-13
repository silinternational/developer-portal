<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $api \Sil\DevPortal\models\Api */
/* @var $usageStats UsageStats */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('/api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'API Usage By Key',
);

$this->pageTitle = 'API Usage By Key';

?>
<dl class="dl-horizontal">
    <dt><?php echo CHtml::encode($api->code); ?></dt>
    <dd><?php echo CHtml::encode($api->display_name); ?></dd>
</dl>
<div class="row">
    <div class="span12">
        <?= $usageStats->generateChartHtml(); ?>
    </div>
</div>
