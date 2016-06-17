<?php
/* @var $this ApiController */
/* @var $activeKeys Key[] */
/* @var $api Api */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('dashboard/'),
    'APIs' => array('api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'Active Keys',
);

$this->pageTitle = 'Active Keys';
$this->pageSubtitle = 'Current Keys for this API';

?>
<div class="row">
    <div class="span12">
        <?php 

        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $activeKeys,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'user.display_name',
                    'header' => 'User',
                    'visible' => ( ! \Yii::app()->user->checkAccess('admin')),
                ),
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => 'CHtml::encode($data->user->display_name)',
                    'urlExpression' => 'Yii::app()->createUrl('
                                         . '"/user/details/", '
                                         . 'array("id" => $data->user_id)'
                                     . ')',
                    'header' => 'User',
                    'visible' => \Yii::app()->user->checkAccess('admin'),
                ),
                array(
                    'header' => 'Created',
                    'value' => 'Utils::getShortDate($data->created)'
                ),
                array(
                    'header' => 'Domain',
                    'value' => '($data->keyRequest ? $data->keyRequest->domain : "UNKNOWN")'
                ),
                array(
                    'header' => 'Purpose',
                    'value' => '($data->keyRequest ? $data->keyRequest->purpose : "UNKNOWN")'
                ),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right'),
                    'links' => array(
                        array(
                            'icon' => 'list',
                            'text' => 'Details',
                            'urlPattern' => '/key/details/:key_id',
                        ),
                    ),
                ),
            ),
        )); 
        
        ?>
    </div>
</div>
<?php

if ($api->approvedKeyCount > 0) {
    echo sprintf(
        '<a href="%s" class="btn space-after-icon"><i class="icon-%s"></i>%s</a>',
        sprintf(
            'mailto:%s?subject=%s&bcc=%s',
            CHtml::encode(\Yii::app()->user->user->email),
            CHtml::encode($api->display_name . ' API'),
            CHtml::encode(implode(
                ',',
                $api->getEmailAddressesOfUsersWithActiveKeys()
            ))
        ),
        'envelope',
        'Email Users With Keys'
    );
}
