<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $api \Sil\DevPortal\models\Api */
/* @var $usageStats UsageStats */
/* @var $summary array<string,int> */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('/api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'API Usage',
);

$this->pageTitle = 'API Usage';

?>
<dl class="dl-horizontal">
    <dt><?php echo CHtml::encode($api->code); ?></dt>
    <dd><?php echo CHtml::encode($api->display_name); ?></dd>
</dl>
<div class="row">
    <div class="span10 offset1">
        <p>
            Below is your daily and monthly usage information for this API.
        </p>
    </div>
</div>
<div class="row">
    <div class="span8 offset1">
        <h3>Daily</h3>
        <?= $usageStats->generateChartHtml(); ?>
    </div>
    <div class="span2">
        <h3>Monthly</h3>
        <table class="table table-striped">
            <?php foreach ($summary as $month => $total): ?>
            <tr>
                <th><?= \CHtml::encode($month); ?></th>
                <td><?= \CHtml::encode($total); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
