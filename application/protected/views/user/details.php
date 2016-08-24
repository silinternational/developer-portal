<?php
/* @var $this UserController */
/* @var $apisDataProvider CDataProvider */
/* @var $keysDataProvider CDataProvider */
/* @var $user User */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Users' => array('/user/'),
    $user->display_name,
);

$this->pageTitle = 'User Details';

?>
<div class="row">
    <div class="span6">
        
        <dl class="dl-horizontal well well-small">
            <dt>Display Name</dt>
            <dd><?php echo CHtml::encode($user->display_name); ?>&nbsp;</dd>
            <dt>Email</dt>
            <dd><?php echo CHtml::encode($user->email); ?>&nbsp;</dd>
            <dt>Role</dt>
            <dd><?php echo CHtml::encode(User::getRoleString($user->role)); ?>&nbsp;</dd>
            <dt>Status</dt>
            <dd><?php echo CHtml::encode(User::getStatusString($user->status)); ?>&nbsp;</dd>
        </dl>

    </div>
    <div class="span4 offset2">

        <h3>Actions</h3>
        <dl>
            <dd>
                <a href="<?php echo $this->createUrl('/user/edit/', array(
                                   'id' => $user->user_id,
                               )); ?>" class="nowrap space-after-icon">
                    <i class="icon-pencil"></i>Edit User
                </a>
            </dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="span6">
        
        <h3>APIs</h3>
        <?php 
        
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $apisDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'display_name',
                    'header' => 'Name',
                ),
                array(
                    'name' => 'brief_description',
                    'header' => 'Description',
                ),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right;'),
                    'links' => array(
                        array(
                            'icon' => 'list',
                            'text' => 'Details',
                            'urlPattern' => '/api/details/:code',
                        ),
                    ),
                ),
            ),
        ));

        ?>
    </div>
    <div class="span6">
        
        <h3>API Keys</h3>
        <?php 
        
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $keysDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'api.display_name',
                    'header' => 'API',
                ),
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
