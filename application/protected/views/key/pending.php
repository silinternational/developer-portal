<?php
/* @var $this \Sil\DevPortal\controllers\KeyController */
/* @var $pendingKeysDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'Pending Keys',
);

$this->pageTitle = 'Pending Keys';
$this->pageSubtitle = 'All pending API keys';

$this->widget('bootstrap.widgets.TbGridView', array(
    'type' => 'striped hover',
    'dataProvider' => $pendingKeysDataProvider,
    'template' => '{items}{pager}',
    'columns' => array(
        array('name' => 'user.display_name', 'header' => 'User'),
        array('name' => 'api.display_name', 'header' => 'API'),
        array('name' => 'purpose', 'header' => 'Purpose'),
        array('name' => 'domain', 'header' => 'Domain'),
        array(
            'name' => 'requested_on',
            'header' => 'Requested',
            'value' => '\Utils::getFriendlyDate($data->requested_on)'
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
            )
        ),
    ),
)); 
