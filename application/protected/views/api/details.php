<?php

use Sil\DevPortal\models\Api;
use Sil\DevPortal\models\User;

/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $actionLinks ActionLink[] */
/* @var $api Api */
/* @var $webUser WebUser */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('/api/'),
    $api->display_name,
);

$this->pageTitle = $api->display_name;
if ($webUser->hasActiveKeyToApi($api)) {
    $this->pageSubtitle = $api->getStyledPublicUrlHtml('text-dark');
    $this->pageSubtitleIsHtml = true;
}

// Get the attribute labels.
$attrLabels = $api->attributeLabels();

?>
<div class="row">
    <div class="span12">
        <div class="pull-right pad-horiz">
            <?php if ($webUser->isGuest): ?>
                <a href="<?= \CHtml::encode($this->createUrl('/auth/login/')); ?>"
                   class="btn btn-success">Login to continue</a>
            <?php else: ?>
                <?= LinksManager::showActionLinks($actionLinks); ?>
            <?php endif; ?>
        </div>
        <dl class="dl-horizontal">

            <dt>Description</dt>
            <dd><?php
                if ($api->brief_description) {
                    echo CHtml::encode($api->brief_description);
                } else {
                    echo '<i class="muted">(none)</i>';
                }
                ?>
            </dd>

            <?php
            if ( ! $webUser->isGuest) {
                $currentUser = $webUser->getUser();
                
                if ($currentUser->hasActiveKeyToApi($api)) {
                    ?>
                    <dt>Query rate limits</dt>
                    <dd><?php
                        echo sprintf(
                            '<span class="nowrap">%s %s</span> &nbsp;|&nbsp; '
                            . '<span class="nowrap">%s %s</span>',
                            (int) $api->queries_second,
                            'per second',
                            (int) $api->queries_day,
                            'per day'
                        );
                        ?>
                    </dd>
                    <?php
                }

                // If the user has permission to see them, show the key counts.
                if ($currentUser->canSeeKeysForApi($api)) {
                    ?>
                    <dt>Keys</dt>
                    <dd><?php
                        echo sprintf(
                            '<span class="nowrap">%s active</span> &nbsp;|&nbsp; '
                            . '<span class="nowrap">%s pending</span>',
                            $api->getActiveKeyCountBadgeHtml(
                                'Click for more information.',
                                $this->createUrl(
                                    '/api/active-keys/',
                                    array('code' => $api->code)
                                )
                            ),
                            $api->getPendingKeyCountBadgeHtml(
                                'Click for more information.',
                                $this->createUrl(
                                    '/api/pending-keys/',
                                    array('code' => $api->code)
                                )
                            )
                        );
                        ?>
                    </dd>
                    <?php
                }

                // If the user has permission to administer this Api, show more info.
                if ($currentUser->hasAdminPrivilegesForApi($api)) {

                    ?>
                    <dt><?php echo CHtml::encode($attrLabels['owner_id']); ?></dt>
                    <dd><?php
                        if ($api->owner instanceof User) {
                            echo CHtml::encode(sprintf(
                                '%s (%s)',
                                $api->owner->display_name,
                                $api->owner->email
                            ));
                        } else {
                            echo '<i class="muted">(none)</i>';
                        }
                    ?>&nbsp;</dd>

                    <dt><?php echo \CHtml::encode($attrLabels['visibility']); ?></dt>
                    <dd><?php
                        echo \CHtml::encode($api->getVisibilityDescription());
                    ?>&nbsp;</dd>

                    <dt><?php echo \CHtml::encode($attrLabels['require_signature']); ?></dt>
                    <dd><?= \CHtml::encode($api->getRequiresSignatureText()); ?></dd>

                    <?php if (count($api->apiVisibilityUsers) > 0): ?>
                        <dt>Invited Users</dt>
                        <dd>
                            <?php echo $api->getInvitedUsersCountBadgeHtml(
                                'Click for more information.',
                                $this->createUrl(
                                    '/api/invited-users/',
                                    array('code' => $api->code)
                                )
                            ); ?>
                        </dd>
                    <?php endif; ?>

                    <?php if (count($api->apiVisibilityDomains) > 0): ?>
                        <dt>Invited Domains</dt>
                        <dd>
                            <?php echo $api->getInvitedDomainsCountBadgeHtml(
                                'Click for more information.',
                                $this->createUrl(
                                    '/api/invited-domains/',
                                    array('code' => $api->code)
                                )
                            ); ?>
                        </dd>
                    <?php endif; ?>

                    <dt><?php echo CHtml::encode($attrLabels['approval_type']); ?></dt>
                    <dd><?php
                        echo CHtml::encode($api->getApprovalTypeDescription());
                    ?>&nbsp;</dd>

                    <dt>Internal API Endpoint</dt>
                    <dd><?php
                        echo CHtml::encode($api->getInternalApiEndpoint());
                    ?>&nbsp</dd>

                    <dt><?php echo CHtml::encode($attrLabels['endpoint_timeout']); ?></dt>
                    <dd><?php echo (int)$api->endpoint_timeout; ?> seconds</dd>

                    <dt><?php echo CHtml::encode($attrLabels['strict_ssl']); ?></dt>
                    <dd><?php echo ($api->strict_ssl ? 'True' : 'False'); ?></dd>

                    <dt><?php echo CHtml::encode($attrLabels['created']); ?></dt>
                    <dd><?php echo Utils::getFriendlyDate($api->created); ?>&nbsp;</dd>

                    <dt><?php echo CHtml::encode($attrLabels['updated']); ?></dt>
                    <dd><?php echo Utils::getFriendlyDate($api->updated); ?>&nbsp;</dd>
                    <?php
                }
                
                if ($currentUser->hasActiveKeyToApi($api)) {
                    if ($api->technical_support) {
                        ?>
                        <dt><?php echo CHtml::encode($attrLabels['technical_support']); ?></dt>
                        <dd><?php echo CHtml::encode($api->technical_support); ?>&nbsp;</dd>
                        <?php
                    }
                    if ($api->customer_support) {
                        ?>
                        <dt><?php echo CHtml::encode($attrLabels['customer_support']); ?></dt>
                        <dd><?php echo CHtml::encode($api->customer_support); ?>&nbsp;</dd>
                        <?php
                    }
                }
            }
            ?>
        </dl>
    </div>
</div>

<?php if ( ! empty($api->how_to_get)): ?>
    <b>How to Get</b>
    <div>
        <div class="well">
            <?php
            $this->beginWidget('CMarkdown', array('purifyOutput' => true));
            echo $api->how_to_get;
            $this->endWidget();
            ?>
        </div>
    </div>
<?php endif; ?>

<b>Documentation</b>
<div>
    <?php if ( ! empty($api->embedded_docs_url)): ?>
        <iframe src="<?= \CHtml::encode($api->embedded_docs_url); ?>"
                class="embedded-docs"></iframe>
    <?php else: ?>
        <?php if ($webUser->hasAdminPrivilegesForApi($api)): ?>
            <a href="<?php echo $this->createUrl('/api/docs-edit/', array('code' => $api->code)); ?>" 
               class="nowrap space-after-icon pull-right btn btn-xs" style="margin: 5px;">
                <i class="icon-pencil"></i>Edit documentation
            </a>
        <?php endif; ?>

        <div class="well">
            <?php

            $this->beginWidget('CMarkdown', array('purifyOutput' => true));
            echo $api->documentation;
            $this->endWidget();

            ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($api->hasTerms()): ?>
    <b>Terms</b>
    <div>
        <div class="well">
            <?php
            $this->beginWidget('CMarkdown', array('purifyOutput' => true));
            echo $api->terms;
            $this->endWidget();
            ?>
        </div>
    </div>
<?php endif; ?>
