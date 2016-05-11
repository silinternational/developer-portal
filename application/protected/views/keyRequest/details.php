<?php
/* @var $this KeyRequestController */
/* @var $actionLinks ActionLink[] */
/* @var $keyRequest KeyRequest */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Keys' => array('/key/'),
    'Key Request Details',
);

$this->pageTitle = 'Key Request Details';

?>
<div class="row">
    <div class="span7">

        <dl class="dl-horizontal">
            <dt>User</dt>
            <dd><?php
                if (\Yii::app()->user->checkAccess('admin')) {
                    echo sprintf(
                        '<a href="%s">%s</a>',
                        $this->createUrl('/user/details/', array(
                            'id' => $keyRequest->user_id,
                        )),
                        CHtml::encode($keyRequest->user->display_name)
                    );
                } else {
                    echo CHtml::encode($keyRequest->user->display_name);
                }
                
                ?>
            </dd>
            
            <dt>Api</dt>
            <dd>
                <a href="<?php echo $this->createUrl('/api/details/',
                                   array('code' => $keyRequest->api->code)
                               ); ?>" 
                   target="_blank"><?php
                    echo CHtml::encode($keyRequest->api->display_name .
                                       ' (' . $keyRequest->api->code . ')'); ?>
                </a>
            </dd>
            
            <dt>Purpose</dt>
            <dd><?php echo CHtml::encode($keyRequest->purpose); ?>&nbsp;</dd>
            
            <dt>Domain</dt>
            <dd><?php echo CHtml::encode($keyRequest->domain); ?>&nbsp;</dd>
            
            <dt>Status</dt>
            <dd><?php echo $keyRequest->getStyledStatusHtml(); ?>&nbsp;</dd>
            
            <dt>Created</dt>
            <dd><?php echo Utils::getFriendlyDate($keyRequest->created); ?>&nbsp;</dd>

            <dt>Updated</dt>
            <dd><?php echo Utils::getFriendlyDate($keyRequest->updated); ?>&nbsp;</dd>

            <?php
            
            // If the key request has been processed...
            if ($keyRequest->processedBy) {
                
                // Show who processed it.
                ?>
                <dt>Processed by</dt>
                <dd>
                    <?php
                    echo CHtml::encode($keyRequest->processedBy->display_name);
                    ?>
                </dd>
                <?php
            }
            
            ?>
        </dl>
    </div>
    <div class="span4">
        <?php

        // If the request is still pending
        //    AND
        // if the user has permission to grant/deny this request...
        $user = \Yii::app()->user->user;
        if (($keyRequest->status == KeyRequest::STATUS_PENDING) &&
            $user->hasAdminPrivilegesForApi($keyRequest->api)) {

            // Provide a way for this admin user to grant/deny the request.
            ?>
            <h3>Actions</h3>
            <p>What do you want to do with this key request? </p>
            <?php $form=$this->beginWidget('CActiveForm'); ?>
                <dl>
                    <dd>
                        <?php 
                        echo CHtml::submitButton('Grant a Key', 
                                array('name' => KeyRequest::STATUS_APPROVED,
                                      'class' => 'btn btn-primary'));
                        ?>
                    </dd>
                    <dd>
                        <?php
                        echo CHtml::submitButton('Deny the Request',
                                array('name' => KeyRequest::STATUS_DENIED,
                                      'class' => 'btn btn-danger'));
                        ?>
                    </dd>
                </dl>
            <?php
            $this->endWidget();
        } else {
            
            // Otherwise, show any (normal) action links.
            echo LinksManager::generateActionsDropdownHtml($actionLinks);
        }

        ?>
    </div>
</div>
