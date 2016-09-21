<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $invitedUsersDataProvider CDataProvider */
/* @var $api Api */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'Invited Users',
);

$this->pageTitle = 'Invited Users';
$this->pageSubtitle = 'People who have been individually invited to see this API';
?>
<?php if ($api->isPubliclyVisible()): ?>
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Note</strong>
        This is a publicly visible API, so no invitations are necessary.
    </div>
<?php endif; ?>
<div class="row">
    <div class="span12">
        <?php 

        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $invitedUsersDataProvider,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'invitedUser.display_name',
                    'header' => 'User',
                    'value' => '$data->getInviteeDisplayText()',
                ),
                array(
                    'name' => 'created',
                    'header' => 'Invited',
                    'value' => 'Utils::getShortDateTime($data->created)'
                ),
                array(
                    'header' => 'Invited By',
                    'value' => 'is_null($data->invitedByUser) ? '
                    . '(UNKNOWN) : '
                    . 'sprintf('
                    .   '"%s (%s)",'
                    .   '$data->invitedByUser->email,'
                    .   '$data->invitedByUser->getDisplayName()'
                    . ');'
                ),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right'),
                    'links' => array(
                        array(
                            'icon' => 'remove',
                            'text' => 'Uninvite',
                            'urlPattern' => '/api/cancel-user-invitation/:api_visibility_user_id',
                        ),
                    ),
                ),
            ),
        )); 
        
        ?>
    </div>
</div>
