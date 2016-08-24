<?php
/* @var $this EventController */
/* @var $eventDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Events',
);

// Set the page title.
$this->pageTitle = "Event Log";

?>
<div class="row">
    <div class="span12">
        <?php
        
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $eventDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'created',
                    'header' => 'Date',
                    'value' => '\Utils::getShortDateTime($data->created)',
                    'htmlOptions' => array(
                        'style' => 'white-space: nowrap;',
                    ),
                ),
                array(
                    'name' => 'description',
                    'header' => 'Event',
                    'type' => 'raw',
                    'value' => '\CHtml::encode($data["description"])',
                ),
            ),
        )); 

        ?>
    </div>
</div>
