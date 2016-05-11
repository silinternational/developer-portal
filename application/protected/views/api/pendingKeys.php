<?php
/* @var $this ApiController */
/* @var $pendingKeyRequests KeyRequest[] */
/* @var $api Api */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('dashboard/'),
    'APIs' => array('api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'Pending Keys',
);

$this->pageTitle = 'Pending Keys';
$this->pageSubtitle = 'Pending Key Requests for this API';

?>
<div class="row">
    <div class="span12">
        <?php 

        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $pendingKeyRequests,
            'template' => '{items}{pager}',
            //'filter' => new Key(),
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
                    //'visible' => (\Yii::app()->user->getRole() === 'admin'),
                ),
            ),
        )); 
        
        ?>
    </div>
</div>
