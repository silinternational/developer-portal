<?php
/* @var $this KeyController */
/* @var $key Key */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Keys' => array('/key/'),
    'Reset Key',
);

$this->pageTitle = 'Reset Key';

?>
<div class="row">
    <div class="span12">
        <dl class="dl-horizontal">
            <dt>API</dt>
            <dd>
                <?php echo sprintf(
                    '<a href="%s">%s</a>',
                    $this->createUrl('/api/details/', array(
                        'code' => $key->api->code,
                    )),
                    CHtml::encode(
                        $key->api->display_name . ' (' . $key->api->code . ')'
                    )
                ); ?>
            </dd>

            <dt>User</dt>
            <dd>
                <?php
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

            <dt>Value</dt>
            <dd><?php echo CHtml::encode($key->value); ?>&nbsp;</dd>

            <dt>Secret</dt>
            <dd>
                <?php
                if ($key->isOwnedBy(\Yii::app()->user->user)) {
                    ?>
                    <input type="password" 
                           readonly="readonly"
                           onblur="this.type = 'password';"
                           onfocus="this.type = 'text';"
                           title="Click to view shared secret"
                           value="<?php echo CHtml::encode($key->secret); ?>" />
                    <?php
                } else {
                    echo '<span class="muted">(only visible to the key\'s owner)</span>';
                }
                ?>
            </dd>
            
            <?php
            if ($key->keyRequest !== null) {
                ?>
                <dt>Purpose</dt>
                <dd>
                    <?php
                    echo CHtml::encode($key->keyRequest->purpose);
                    ?>&nbsp;
                </dd>

                <dt>Domain</dt>
                <dd>
                    <?php
                    echo CHtml::encode($key->keyRequest->domain);
                    ?>&nbsp;
                </dd>
                <?php
            }
            ?>
        </dl>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <?php $form = $this->beginWidget('CActiveForm'); ?>

        <p>Do you really want to reset this key's value and secret? </p>
    </div>
</div>
<div class="row">
    <div class="span10 offset2">
        <ul class="inline">
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'link',
                    'icon' => 'ban-circle',
                    'label' => 'Cancel',
                    'url' => $this->createUrl(
                        '/key/details/',
                        array('id' => (int)$key->key_id)
                    ),
                ));

                ?>
            </li>
            <li>
                <?php

                $this->widget('bootstrap.widgets.TbButton', array(
                    'buttonType' => 'submit',
                    'icon' => 'refresh white',
                    'label' => 'Reset',
                    'type' => 'primary'
                ));

                ?>
            </li>
        </ul>

        <?php $this->endWidget(); ?>
    </div>
</div>
