<?php
/* @var $this KeyController */
/* @var $key Key */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Keys' => array('/key/'),
    'Details',
);

$this->pageTitle = 'Key Details';

?>
<div class="row">
  <div class="span8">
    <dl class="dl-horizontal">

      <dt>API</dt>
      <dd>
          <?php
          echo sprintf(
              '<a href="%s">%s</a>&nbsp;',
              $this->createUrl(
                  '/api/details/',
                  array('code' => $key->api->code)
              ),
              CHtml::encode($key->api->display_name)
          );
          ?>
      </dd>
      
      <dt>User</dt>
      <dd><?php echo CHtml::encode($key->user->display_name . 
                                   ' (' . $key->user->email . ')'); ?>&nbsp;</dd>
  
      <dt>Value</dt>
      <dd><input type="text" 
                 readonly="readonly"
                 onclick="$(this).select();"
                 value="<?php echo CHtml::encode($key->value); ?>" /></dd>

      <dt>Secret</dt>
      <dd><?php
          if (\Yii::app()->user->user->user_id == $key->user_id) {
              ?>
              <input type="password" 
                     readonly="readonly"
                     onblur="this.type = 'password';"
                     onfocus="this.type = 'text';"
                     onclick="$(this).select();"
                     title="Click to view shared secret"
                     value="<?php echo CHtml::encode($key->secret); ?>" />
              <?php
          } else {
              echo '<span class="muted">(only visible to the key\'s owner)</span>';
          }
          ?>
      </dd>

      <dt>Purpose</dt>
      <dd><?php echo $key->keyRequest->purpose; ?>&nbsp;</dd>

      <dt>Domain</dt>
      <dd><?php echo $key->keyRequest->domain; ?>&nbsp;</dd>

      <dt>Query rate limits</dt>
      <dd><?php
          echo sprintf(
              '<span class="nowrap">%s %s</span> &nbsp;|&nbsp; '
              . '<span class="nowrap">%s %s</span>',
              (int) $key->queries_second,
              'per second',
              (int) $key->queries_day,
              'per day'
          );
          ?>
      </dd>

      <dt>Created</dt>
      <dd><?php echo Utils::getFriendlyDate($key->created); ?>&nbsp;</dd>

      <dt>Updated</dt>
      <dd><?php echo Utils::getFriendlyDate($key->updated); ?>&nbsp;</dd>
    </dl>
  </div>
  <div class="span4">
    <h3>Actions</h3>
    <dl>
      <?php if (\Yii::app()->user->user->canResetKey($key)): ?>
      <dd>
        <a href="<?php echo $this->createUrl('/key/reset/',
                                             array('id' => $key->key_id)); ?>" 
           class="nowrap space-after-icon">
            <i class="icon-refresh"></i>Reset Key
        </a>
      </dd>
      <?php endif; ?>
      <?php if (\Yii::app()->user->user->canRevokeKey($key)): ?>
      <dd>
        <a href="<?php echo $this->createUrl('/key/delete/',
                                             array('id' => $key->key_id)); ?>" 
           class="nowrap space-after-icon">
            <i class="icon-remove"></i><?php
                echo ($key->isOwnedBy(\Yii::app()->user->user) ? 'Delete' : 'Revoke');
            ?> Key
        </a>
      </dd>
      <?php endif; ?>
    </dl>
  </div>
</div>
