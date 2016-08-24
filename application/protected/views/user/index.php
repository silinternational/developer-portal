<?php
/* @var $this UserController */
/* @var $usersDataProvider CDataProvider*/

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Users',
);

$this->pageTitle = 'Users';

?>
<div class="row">
    <div class="span12">
        <?php
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $usersDataProvider,
            'rowCssClassExpression' => '('
            . '    ($data->status == \User::STATUS_ACTIVE) ? "" : "muted"'
            . ')',
            'template' => '{items}{pager}',
            'columns' => array(
                array('name' => 'first_name', 'header' => 'First name'),
                array('name' => 'last_name', 'header' => 'Last name'),
                array('name' => 'email', 'header' => 'Email'),
                array('name' => 'approvedKeyCount', 'header' => 'Keys'),
                array(
                    'name' => 'role',
                    'header' => 'Role',
                    'value' => 'User::getRoleString($data->role)',
                ),
                array(
                    'name' => 'status',
                    'header' => 'Status',
                    'value' => 'User::getStatusString($data->status)',
                ),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right'),
                    'links' => array(
                        array(
                            'icon' => 'list',
                            'text' => 'Details',
                            'urlPattern' => '/user/details/:user_id',
                        ),
                        array(
                            'icon' => 'pencil',
                            'text' => 'Edit',
                            'urlPattern' => '/user/edit/:user_id',
                        ),
                    )
                ),
            ),
        ));

        ?>
    </div>
</div>
