<?php
/* @var $this \Sil\DevPortal\controllers\KeyController */
/* @var $activeKeysDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'Active Keys',
);

$this->pageTitle = 'Active Keys';
$this->pageSubtitle = 'All active API keys';

$this->widget('bootstrap.widgets.TbGridView', array(
    'type' => 'striped hover',
    'dataProvider' => $activeKeysDataProvider,
    'template' => '{items}{pager}',
    'columns' => array(
        array('name' => 'user.display_name', 'header' => 'User'),
        array('name' => 'api.display_name', 'header' => 'API'),
        array('name' => 'purpose', 'header' => 'Purpose'),
        array('name' => 'queries_second',
              'header' => '<span title="Queries Per Second">QPS</span>'),
        array('name' => 'queries_day',
              'header' => '<span title="Queries Per Day">QPD</span>'),
        array('name' => 'value', 'header' => 'Key'),
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
