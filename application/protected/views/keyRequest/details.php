<?php
/* @var $this KeyRequestController */
/* @var $actionLinks ActionLink[] */
/* @var $key Key */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Keys' => array('/key/'),
    'Key Details',
);

$this->pageTitle = 'Key Details';

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
                            'id' => $key->user_id,
                        )),
                        CHtml::encode($key->user->display_name)
                    );
                } else {
                    echo CHtml::encode($key->user->display_name);
                }
                
                ?>
            </dd>
            
            <dt>Api</dt>
            <dd>
                <a href="<?php echo $this->createUrl('/api/details/',
                                   array('code' => $key->api->code)
                               ); ?>" 
                   target="_blank"><?php
                    echo CHtml::encode($key->api->display_name .
                                       ' (' . $key->api->code . ')'); ?>
                </a>
            </dd>
            
            <dt>Purpose</dt>
            <dd><?php echo CHtml::encode($key->purpose); ?>&nbsp;</dd>
            
            <dt>Domain</dt>
            <dd><?php echo CHtml::encode($key->domain); ?>&nbsp;</dd>
            
            <dt>Status</dt>
            <dd><?php echo $key->getStyledStatusHtml(); ?>&nbsp;</dd>
            
            <dt>Created</dt>
            <dd><?php echo Utils::getFriendlyDate($key->created); ?>&nbsp;</dd>

            <dt>Updated</dt>
            <dd><?php echo Utils::getFriendlyDate($key->updated); ?>&nbsp;</dd>

            <?php
            
            // If the key has been processed...
            if ($key->processedBy) {
                
                // Show who processed it.
                ?>
                <dt>Processed by</dt>
                <dd>
                    <?php
                    echo CHtml::encode($key->processedBy->display_name);
                    ?>
                </dd>
                <?php
            }
            
            ?>
        </dl>
    </div>
    <div class="span4">
        <?php

        // If the key is still pending
        //    AND
        // if the user has permission to grant/deny this request...
        $user = \Yii::app()->user->user;
        if (($key->status == \Key::STATUS_PENDING) &&
            $user->hasAdminPrivilegesForApi($key->api)) {

            // Provide a way for this admin user to grant/deny the request.
            ?>
            <h3>Actions</h3>
            <p>What do you want to do with this key? </p>
            <?php $form=$this->beginWidget('CActiveForm'); ?>
                <dl>
                    <dd>
                        <?php 
                        echo CHtml::submitButton('Grant a Key', 
                                array('name' => \Key::STATUS_APPROVED,
                                      'class' => 'btn btn-primary'));
                        ?>
                    </dd>
                    <dd>
                        <?php
                        echo CHtml::submitButton('Deny the Request',
                                array('name' => \Key::STATUS_DENIED,
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
