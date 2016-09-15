<?php
/* @var $this \Sil\DevPortal\controllers\KeyController */
/* @var $activeKeysDataProvider CDataProvider */
/* @var $nonActiveKeysDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'My Keys',
);

// Set the page title and subtitle.
$this->pageTitle = 'My Keys';

?>
<div class="row">
    <div class="span12">
        <h3>Active Keys</h3>
        <?php
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $activeKeysDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => 'CHtml::encode($data->api->display_name)',
                    'urlExpression' => 'Yii::app()->createUrl(' .
                                           '"/api/details/", ' .
                                           'array("code" => $data->api->code)' .
                                       ')',
                    'header' => 'API'
                ),
                array(
                    'name' => 'domain',
                    'header' => 'Domain',
                    'htmlOptions' => array(
                        'style' => 'text-decoration: underline;',
                    ),
                ),
                array('name' => 'purpose', 'header' => 'Purpose'),
                array('name' => 'value', 'header' => 'Key'),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right;'),
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
        ?>
    </div>
</div>
<div class="row">
    <div class="span12">
        <h3>Requested Keys</h3>
        <?php
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $nonActiveKeysDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => 'CHtml::encode($data->api->display_name)',
                    'urlExpression' => 'Yii::app()->createUrl(' .
                                           '"/api/details/", ' .
                                           'array("code" => $data->api->code)' .
                                       ')',
                    'header' => 'API'
                ),
                array(
                    'name' => 'status',
                    'header' => 'Status',
                    'type' => 'raw',
                    'value' => '$data->getStyledStatusHtml()',
                ),
                array(
                    'name' => 'requested_on',
                    'header' => 'Requested',
                    'value' => 'Utils::getShortDate($data->requested_on)',
                ),
                array(
                    'name' => 'domain',
                    'header' => 'Domain',
                    'htmlOptions' => array(
                        'style' => 'text-decoration: underline;',
                    ),
                ),
                array('name' => 'purpose', 'header' => 'Purpose'),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right;'),
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
