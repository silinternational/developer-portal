<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>

<?php $this->widget('bootstrap.widgets.TbAlert', array(
    'block' => true,
    'fade' => true,
    'closeText' => '&times;',
)); ?>
<div class="container-fluid">
  <?php if (\Yii::app()->user->hasFlashes()): ?>
    <div class="row-fluid">
      <div class="span12"><?php $this->widget('bootstrap.widgets.TbAlert', array(
            'block' => true,
            'fade' => true,
            'closeText' => '&times;',
        )); ?></div>
    </div>
  <?php endif; ?>
  <div class="row-fluid">
    <div class="span3">
      <?php
      
      $this->widget('bootstrap.widgets.TbMenu', array(
          'type' => 'tabs',
          'stacked' => true,
          'items' => array(
              array(
                  'label' => 'Browse APIs',
                  'url' => $this->createUrl('/api/'),
              ),
              array(
                  'label' => 'Active Keys',
                  'url' => $this->createUrl('/key/active/'),
                  'visible' => \Yii::app()->user->checkAccess('admin'),
              ),
              array(
                  'label' => 'Pending Keys',
                  'url' => $this->createUrl('/key/pending/'),
                  'visible' => \Yii::app()->user->checkAccess('admin'),
              ),
              array(
                  'label' => 'My Keys',
                  'url' => $this->createUrl('/key/mine/'),
              ),
              array(
                  'label' => 'Users',
                  'url' => $this->createUrl('/user/'),
                  'visible' => \Yii::app()->user->checkAccess('admin'),
              ),
              array(
                  'label' => 'API Playground',
                  'url' => $this->createUrl('/api/playground/'),
              ),
              array(
                  'linkOptions' => array(
                      'title' => 'Frequently Asked Questions',
                  ),
                  'label' => 'FAQs',
                  'url' => $this->createUrl('/faq/'),
              ),
              array(
                  'label' => 'Site Text',
                  'url' => $this->createUrl('/site-text/'),
                  'visible' => \Yii::app()->user->checkAccess('admin'),
              ),
              array(
                  'label' => 'Event Log',
                  'url' => $this->createUrl('/event/'),
                  'visible' => \Yii::app()->user->checkAccess('admin'),
              ),
          ),
      ));
      
      ?>
    </div>
    <div class="span9">
      <?= $content; ?>
    </div>
  </div>
</div>
<?php $this->endContent(); ?>
