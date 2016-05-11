<?php
/* @var $this ApiController */
/* @var $apiList CDataProvider */
/* @var $user User */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
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
            'hideHeader' => ( ! $user->hasOwnerPrivileges()),
            'columns' => array(
                array(
                    'name' => 'display_name',
                    'header' => 'Name',
                    'type' => 'raw',
                    'value' => '"<b>" . CHtml::encode($data["display_name"]) . "</b>"',
                    'visible' => ($user->role !== User::ROLE_ADMIN),
                ),
                array(
                    'name' => 'display_name',
                    'header' => 'Name / URL',
                    'type' => 'raw',
                    'value' => '"<b>" . CHtml::encode($data["display_name"]) . "</b><br />" . ' .
                               '"<span style=\"color: #999\">" . $data->getStyledPublicUrlHtml("text-dark") . "</span>"',
                    'visible' => ($user->role === User::ROLE_ADMIN),
                ),
                array(
                    'name' => 'brief_description',
                    'header' => 'Description',
                ),
                array(
                    'name' => 'owner_id',
                    'header' => 'Owner',
                    'value' => '($data->owner ? $data->owner->display_name : "")',
                    'visible' => ($user->role === User::ROLE_ADMIN),
                ),
                array(
                    'class' => 'CLinkColumn',
                    'labelExpression' => ''
                    . '\Yii::app()->user->user->canSeeKeysForApi($data) ? sprintf('
                        . '"<span class=\"badge%s\" title=\"%s\">%s</span>",'
                        . '($data->keyCount ? " badge-info" : "" ),'
                        . '"Click for more information",'
                        . '$data->keyCount'
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
                    'visible' => $user->hasOwnerPrivileges(),
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
                    'visible' => $user->hasOwnerPrivileges(),
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

        // Determine which page to link to based on whether the user is allowed to
        // add APIs.
        if ($user->hasOwnerPrivileges()) {
            $addApiLinkRoute = '/api/add/';
        } else {
            $addApiLinkRoute = '/api/add-contact-us/';
        }

        // Show the link for adding an API.
        ?>
        <a href="<?php echo $this->createUrl($addApiLinkRoute); ?>" 
           class="btn space-after-icon" >
            <i class="icon-plus"></i>Publish a new API
        </a>
    </div>
</div>
