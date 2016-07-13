<?php
/* @var $this Controller */
/* @var $key \Key */
/* @var $currentUser \User */

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
            ?>&nbsp;
        </dd>
        
        <?php if ($key->status === \Key::STATUS_APPROVED): ?>
            <dt>Value</dt>
            <dd><?= \CHtml::encode($key->value); ?>&nbsp;</dd>
            
            <dt>Secret</dt>
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
            <dt>Accepted terms on</dt>
            <dd><?= Utils::getFriendlyDate($key->accepted_terms_on); ?></dd>
        <?php endif; ?>
    </dl>
    <?php
}
