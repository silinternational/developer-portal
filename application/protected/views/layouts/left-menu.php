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
                  'label' => 'Pending Key Requests',
                  'url' => $this->createUrl('/key-request/'),
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
          ),
      ));
      
      //// If there are any news items, show them.
      //$news = \Yii::app()->user->user->getNews();
      //if (isset($news) && (count($news) > 0)) {
      //  ?><!-- <div> <h3>News</h3> --><?php
      //
      //    foreach ($news as $newsItem) {
      //
      //      // TODO: Show news items.
      //
      //    }
      //
      //    ?><!-- </div> --><?php
      //}
      
      ?>
    </div>
    <div class="span9">
      <?php
      if ($this->pageTitle) {
          echo sprintf(
              '<h2>%s%s</h2> ',
              CHtml::encode($this->pageTitle),
              $this->pageSubtitle ? sprintf(
                  ' <small>%s</small>',
                  CHtml::encode($this->pageSubtitle)
              ) : ''
          );
      }
      
      echo $content;
      ?>
    </div>
  </div>
</div>
<?php $this->endContent(); ?>
