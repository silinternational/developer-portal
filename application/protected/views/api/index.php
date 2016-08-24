<?php
/* @var $this ApiController */
/* @var $apiList CDataProvider */
/* @var $webUser WebUser */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    $webUser->getHomeLinkText() => $webUser->getHomeUrl(),
    'APIs',
);

// Set the page title.
$this->pageTitle = "Browse APIs";

?>
<div class="row">
  <div class="span12">
      <?php
      
        // Set up the table of APIs.
        $this->widget('bootstrap.widgets.TbGridView', array(
            'type' => 'striped hover',
            'dataProvider' => $apiList,
            'template' => '{items}{pager}',
            'columns' => array(
                array(
                    'name' => 'display_name',
                    'header' => 'Name',
                    'value' => '$data["display_name"]',
                    'visible' => ( ! $webUser->isAdmin()),
                ),
                array(
                    'name' => 'display_name',
                    'header' => 'Name / URL',
                    'type' => 'raw',
                    'value' => '"<b>" . CHtml::encode($data["display_name"]) . "</b><br />" . ' .
                               '"<span style=\"color: #999\">" . $data->getStyledPublicUrlHtml("text-dark") . "</span>"',
                    'visible' => $webUser->isAdmin(),
                ),
                array(
                    'name' => 'brief_description',
                    'header' => 'Description',
                ),
                array(
                    'name' => 'owner_id',
                    'header' => 'Owner',
                    'value' => '($data->owner ? $data->owner->display_name : "")',
                    'visible' => $webUser->isAdmin(),
                ),
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => ''
                    . '\Yii::app()->user->user->canSeeKeysForApi($data) ? sprintf('
                        . '"<span class=\"badge%s\" title=\"%s\">%s</span>",'
                        . '($data->approvedKeyCount ? " badge-info" : "" ),'
                        . '"Click for more information",'
                        . '$data->approvedKeyCount'
                    . ') : ""',
                    'urlExpression' => ''
                    . '\Yii::app()->user->user->canSeeKeysForApi($data) ? \Yii::app()->createUrl('
                        . '"api/active-keys", '
                        . 'array("code" => $data->code)'
                    . ') : ""',
                    'header' => 'Active Keys',
                    'headerHtmlOptions' => array(
                      'style' => 'text-align: center',
                    ),
                    'htmlOptions' => array(
                      'style' => 'text-align: center',
                    ),
                    'visible' => $webUser->hasOwnerPrivileges(),
                ),
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => ''
                    . '\Yii::app()->user->user->canSeeKeysForApi($data) ? sprintf('
                        . '"<span class=\"badge%s\" title=\"%s\">%s</span>",'
                        . '($data->pendingKeyCount ? " badge-important" : "" ),'
                        . '"Click for more information",'
                        . '$data->pendingKeyCount'
                    . ') : ""',
                    'urlExpression' => ''
                    . '\Yii::app()->user->user->canSeeKeysForApi($data) ? \Yii::app()->createUrl('
                        . '"api/pending-keys", '
                        . 'array("code" => $data->code)'
                    . ') : ""',
                    'header' => 'Pending Keys',
                    'headerHtmlOptions' => array(
                      'style' => 'text-align: center',
                    ),
                    'htmlOptions' => array(
                      'style' => 'text-align: center',
                    ),
                    'visible' => $webUser->hasOwnerPrivileges(),
                ),
                array(
                    'class' => 'ActionLinksColumn',
                    'htmlOptions' => array('style' => 'text-align: right'),
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
        
        if ($webUser->hasOwnerPrivileges()) {
            ?>
            <a href="<?php echo $this->createUrl('/api/add/'); ?>" 
               class="btn space-after-icon" >
                <i class="icon-plus"></i>Publish a new API
            </a>
            <?php
        }
        ?>
    </div>
</div>
