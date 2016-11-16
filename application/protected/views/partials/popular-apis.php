<?php
/* @var $this Controller */
/* @var $popularApis \Sil\DevPortal\models\Api[] */
?>
<h2>Popular&nbsp;APIs</h2>
<dl>
    <?php
    foreach ($popularApis as $api) {
        echo sprintf(
            '<dt><a href="%s">%s&nbsp;</a></dt> <dd>%s&nbsp;</dd> ',
            \CHtml::encode($this->createUrl(
                'api/details',
                array('code' => $api->code)
            )),
            \CHtml::encode($api->display_name),
            \CHtml::encode($api->brief_description)
        );
    }
    ?>
</dl>
<a href="<?= $this->createUrl('api/'); ?>"
   class="pull-right space-before-icon">Browse APIs<i class="icon-arrow-right"></i></a>