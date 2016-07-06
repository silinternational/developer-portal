<?php
/* @var $this KeyController */
/* @var $actionLinks ActionLink[] */
/* @var $key Key */
/* @var $currentUser User */

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
                if ($currentUser->isAdmin()) {
                    echo sprintf(
                        '<a href="%s">%s</a>',
                        $this->createUrl('/user/details/', array(
                            'id' => $key->user_id,
                        )),
                        \CHtml::encode($key->user->display_name)
                    );
                } else {
                    echo \CHtml::encode($key->user->display_name);
                }
                
                ?>
            </dd>
            
            <dt>API</dt>
            <dd><?= sprintf(
                '<a href="%s">%s</a> (<span class="fixed">%s</span>)&nbsp;',
                $this->createUrl('/api/details/', array(
                    'code' => $key->api->code,
                )),
                \CHtml::encode($key->api->display_name),
                \CHtml::encode($key->api->code)
            ); ?></dd>
            
            <dt>Purpose</dt>
            <dd><?php echo \CHtml::encode($key->purpose); ?>&nbsp;</dd>
            
            <dt>Domain</dt>
            <dd><?php echo \CHtml::encode($key->domain); ?>&nbsp;</dd>
            
            <dt>Status</dt>
            <dd><?php echo $key->getStyledStatusHtml(); ?>&nbsp;</dd>
            
            <dt>Requested</dt>
            <dd><?php echo Utils::getFriendlyDate($key->requested_on); ?>&nbsp;</dd>

            <?php if ($key->processed_on || $key->processed_by): ?>
                <dt>Processed</dt>
                <dd>
                    <?php if ($key->processed_on): ?>
                        <div><?= Utils::getFriendlyDate($key->processed_on); ?></div>
                    <?php endif; ?>
                    <?php if ($key->processedBy): ?>
                        <div><i>by <?= \CHtml::encode($key->processedBy->display_name); ?></i></div>
                    <?php endif; ?>
                </dd>
            <?php endif; ?>

            <?php if ($key->accepted_terms_on !== null): ?>
                <dt>Processed on</dt>
                <dd><?= Utils::getFriendlyDate($key->accepted_terms_on); ?></dd>
            <?php endif; ?>
        </dl>
    </div>
    <div class="span4">
        <?php
        
        // If the key is still pending
        //    AND
        // if the user has permission to grant/deny this request...
        if (($key->status == \Key::STATUS_PENDING) &&
            $currentUser->hasAdminPrivilegesForApi($key->api)) {
            
            // Provide a way for this admin user to grant/deny the request.
            ?>
            <h3>Actions</h3>
            <p>What do you want to do with this key? </p>
            <?php $form = $this->beginWidget('CActiveForm'); ?>
                <dl>
                    <dd>
                        <?= \CHtml::submitButton('Grant a Key', array(
                            'name' => \Key::STATUS_APPROVED,
                            'class' => 'btn btn-primary',
                        )); ?>
                    </dd>
                    <dd>
                        <?= \CHtml::submitButton('Deny the Request', array(
                            'name' => \Key::STATUS_DENIED,
                            'class' => 'btn btn-danger',
                        )); ?>
                    </dd>
                </dl>
                <?php //echo \CHtml::errorSummary($key); ?>
            <?php
            $this->endWidget();
            
        } else {
            
            // Otherwise, show any (normal) action links.
            echo LinksManager::generateActionsDropdownHtml(
                LinksManager::getPendingKeyDetailsActionLinksForUser($key, $currentUser)
            );
        }

        ?>
    </div>
</div>
