<?php
/* @var $this ApiController */
/* @var $actionLinks ActionLink[] */
/* @var $api Api */
/* @var $currentUser User */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $api->display_name,
);

$this->pageTitle = $api->display_name;
$this->pageSubtitle = $api->getStyledPublicUrlHtml('text-dark');
$this->pageSubtitleIsHtml = true;

// Get the attribute labels.
$attrLabels = $api->attributeLabels();

?>
<div class="row">
    <div class="offset1 span11">
        <?php
        
        // If there are any action links, show them.
        echo LinksManager::generateActionsDropdownHtml($actionLinks);
        
        ?>
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

            // If the Api has an support text, show it.
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

            ?>
        </dl>
    </div>
</div>

<b>Documentation</b>
<div>
    <?php if ($currentUser->hasAdminPrivilegesForApi($api)): ?>
        <a href="<?php echo $this->createUrl('/api/docs-edit/', array('code' => $api->code)); ?>" 
           class="nowrap space-after-icon pull-right btn btn-xs" style="margin: 5px;">
            <i class="icon-pencil"></i>Edit documentation
        </a>
    <?php endif; ?>

    <div class="well">
        <?php

        $this->beginWidget('CMarkdown', array('purifyOutput' => false));
        echo $api->documentation;
        $this->endWidget();

        ?>
    </div>
</div>

<b>Terms</b>
<div>
    <div class="well">
        <?php
        if ($api->hasTerms()) {
            $this->beginWidget('CMarkdown', array('purifyOutput' => false));
            echo $api->terms;
            $this->endWidget();
        } else {
            echo '<i class="muted">none</i>';
        }
        ?>
    </div>
</div>
