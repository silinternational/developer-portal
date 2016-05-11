<?php
/* @var $this KeyRequestController */
/* @var $keyRequestDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Pending Key Requests',
);

$this->pageTitle = 'Pending Key Requests';

?>
<div class="row">
    <div class="span12">
        <?php 

        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $keyRequestDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'user_id',
                    'header' => 'User',
                    'value' => 'CHtml::encode($data->user->display_name)',
                    'visible' => \Yii::app()->user->hasOwnerPrivileges(),
                ),
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => 'CHtml::encode($data->api->display_name)',
                    'urlExpression' => 'Yii::app()->createUrl(' .
                                           '"/api/details/", ' .
                                           'array("code" => $data->api->code)' .
                                       ')',
                    'header' => 'API'
                ),
                array('name' => 'created',
                      'header' => 'Requested',
                      'value' => 'Utils::getShortDate($data->created)'),
                array('name' => 'domain', 'header' => 'Domain'),
                array('name' => 'purpose', 'header' => 'Purpose'),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right;'),
                    'links' => array(
                        array(
                            'icon' => 'list',
                            'text' => 'Details',
                            'urlPattern' => '/key-request/details/:key_request_id',
                        ),
                    ),
                ),
            ),
        )); 
        
        ?>
    </div>
</div>
