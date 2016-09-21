<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $activeKeysDataProvider CDataProvider */
/* @var $api \Sil\DevPortal\models\Api */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
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
            'dataProvider' => $activeKeysDataProvider,
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
                    'header' => 'Granted on',
                    'value' => 'Utils::getShortDate($data->processed_on)'
                ),
                array(
                    'header' => 'Domain',
                    'value' => '$data->domain'
                ),
                array(
                    'header' => 'Purpose',
                    'value' => '$data->purpose'
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
