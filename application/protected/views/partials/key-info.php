<?php

use Sil\DevPortal\models\Key;

/* @var $this Controller */
/* @var $key Key */
/* @var $currentUser \Sil\DevPortal\models\User */

if ($currentUser->canSeeKey($key)) {
    ?>
    <dl class="dl-horizontal">
        <dt>API</dt>
        <dd>
            <?php
            if ($key->api->isVisibleToUser($currentUser)) {
                echo sprintf(
                    '<a href="%s">%s</a>',
                    $this->createUrl('/api/details/', array(
                        'code' => $key->api->code,
                    )),
                    \CHtml::encode($key->api->display_name)
                );
            } else {
                \CHtml::encode($key->api->display_name);
            }
            ?>&nbsp;
        </dd>
        
        <dt>User</dt>
        <dd>
            <?php
            $userDisplayHtml = \CHtml::encode(sprintf(
                '%s (%s)',
                $key->user->display_name,
                $key->user->email
            ));
            if ($currentUser->isAdmin()) {
                echo sprintf(
                    '<a href="%s">%s</a>',
                    $this->createUrl('/user/details/', array(
                        'id' => $key->user_id,
                    )),
                    $userDisplayHtml
                );
            } else {
                echo $userDisplayHtml;
            }
            ?>&nbsp;
        </dd>
        
        <?php if ($key->value !== null): ?>
            <dt>Value</dt>
            <dd><?php
                if ($key->isOwnedBy($currentUser)) {
                    echo \CHtml::encode($key->value);
                } else {
                    echo sprintf(
                        '<span title="Full value only shown to key owner.">%s</span>',
                        \CHtml::encode(substr($key->value, 0, 12)) . '...'
                    );
                }
                ?></dd>
        <?php endif; ?>
        
        <?php if ($key->isApproved()): ?>
            <dt>Secret</dt>
            <?php if ($key->secret !== null): ?>
                <dd>
                    <?php
                    if ($key->isOwnedBy($currentUser)) {
                        ?>
                        <input type="password" 
                               readonly="readonly"
                               onblur="this.type = 'password';"
                               onfocus="this.type = 'text';"
                               title="Click to view shared secret"
                               value="<?= \CHtml::encode($key->secret); ?>" />
                        <?php
                    } else {
                        echo '<span class="muted">(only visible to the key\'s owner)</span>';
                    }
                    ?>
                </dd>
            <?php elseif ( ! $key->requiresSignature()): ?>
                <dd><i class="muted">not applicable - no signature necessary</i></dd>
            <?php endif;?>
        <?php endif;?>
        
        <dt>Query rate limits</dt>
        <dd>
            <?= (int)$key->queries_second ?> / second<br />
            <?= number_format((int)$key->queries_day) ?> / day
        </dd>
        
        <dt>Purpose</dt>
        <dd><?= \CHtml::encode($key->purpose); ?>&nbsp;</dd>
        
        <dt>Domain</dt>
        <dd><?= \CHtml::encode($key->domain); ?>&nbsp;</dd>
        
        <dt>Status</dt>
        <dd><?= $key->getStyledStatusHtml(); ?>&nbsp;</dd>
        
        <dt>Requested</dt>
        <dd><?= Utils::getFriendlyDate($key->requested_on); ?>&nbsp;</dd>
        
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
            <dt>Accepted terms</dt>
            <dd><?= Utils::getFriendlyDate($key->accepted_terms_on); ?></dd>
        <?php endif; ?>
    </dl>
    <?php
}
