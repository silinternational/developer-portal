<?php
/* @var $this ApiController */
/* @var $api Api */
/* @var $keyList CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $api->display_name => array('/api/details/', 'code' => $api->code),
    'Delete',
);

$this->pageTitle = 'Delete API';

?>
<div class="row">
    <div class="span7 offset2">

        <h3 class="text-error">Are you sure?</h3>
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <span class="help-block control-group error">
            <strong class="control-label">
                <span style="text-decoration: underline;">WARNING</span>:
                This will completely delete this API as well as revoke 
                any keys that have been granted to users for this API.
            </strong>
        </span>
        <div class="row">
            <div class="span2 offset1">
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'NO - Cancel',
                    'url' => $this->createUrl('/api/details/', array(
                        'code' => $api->code,
                    )),
                ));

                ?>
            </div>
            <div class="span1">&nbsp;</div>
            <div class="span2">
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'submit',
                    'icon' => 'remove',
                    'label' => 'YES - Delete',
                    'type' => 'danger'
                ));

                ?>
            </div>
        </div>

        <?php $this->endWidget(); ?>
    </div>
</div>
<div class="row">
    <div class="span7 offset1">

        <h3>API Info</h3>
        <dl class="dl-horizontal">
            <dt>Code</dt>
            <dd><?php echo CHtml::encode($api->code); ?></dd>

            <dt>Display Name</dt>
            <dd><?php echo CHtml::encode($api->display_name); ?></dd>

            <dt>Endpoint</dt>
            <dd><?php echo CHtml::encode($api->endpoint); ?></dd>

            <dt>Query rate limits</dt>
            <dd>
                <?php
                echo (int) $api->queries_second . ' per second, ' .
                     (int) $api->queries_day . ' per day';
                ?>
            </dd>

            <dt>Visibility</dt>
            <dd><?php echo CHtml::encode($api->getVisibilityDescription()); ?></dd>

            <?php if (count($api->apiVisibilityUsers) > 0): ?>
                <dt>Invited Users</dt>
                <dd><?php echo count($api->apiVisibilityUsers); ?></dd>
            <?php endif; ?>
            
            <?php if (count($api->apiVisibilityDomains) > 0): ?>
                <dt>Invited Domains</dt>
                <dd><?php echo count($api->apiVisibilityDomains); ?></dd>
            <?php endif; ?>

            <dt>Approval Type</dt>
            <dd><?php echo CHtml::encode($api->approval_type); ?></dd>

            <dt>Created</dt>
            <dd><?php echo Utils::getFriendlyDate($api->created); ?></dd>

            <dt>Updated</dt>
            <dd><?php echo Utils::getFriendlyDate($api->updated); ?></dd>
        </dl>
    </div>
</div>
<div class="row">
  <div class="span9 offset1">

    <h3>Users with Keys</h3>
    <?php 
      
      $this->widget('bootstrap.widgets.TbGridView', array(
         'type' => 'striped hover',
         'dataProvider' => $keyList,
         'template' => '{items}{pager}',
         //'filter' => new Key(),
         'columns' => array(
              array(
                  'name' => 'user.display_name',
                  'header' => 'Name',
                  'visible' => ( ! \Yii::app()->user->checkAccess('admin')),
              ),
              array(
                  'class' => 'CLinkColumn',
                  'labelExpression' => 'CHtml::encode($data->user->display_name)',
                  'urlExpression' => 'Yii::app()->createUrl('
                                       . '"/user/details/", '
                                       . 'array("id" => $data->user_id)'
                                   . ')',
                  'header' => 'Name',
                  'visible' => \Yii::app()->user->checkAccess('admin'),
              ),
              array('name' => 'user.email', 'header' => 'Email'),
              array('name' => 'user.status', 'header' => 'Status'),
              array(
                  'name' => 'user.role',
                  'header' => 'Role',
                  'value' => 'User::getRoleString($data->user->role)',
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
    ?>
  </div>
</div>


