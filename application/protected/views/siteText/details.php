<?php
/* @var $this SiteTextController */
/* @var $siteText SiteText */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Site Texts' => array('/site-text/'),
    $siteText->name,
);

$this->pageTitle = $siteText->name;

// If the user is an admin, show an edit link.
if (\Yii::app()->user->checkAccess('admin')) {
    ?>
    <a href="<?php echo $this->createUrl('/site-text/edit/', array('id' => $siteText->site_text_id)); ?>" 
       class="nowrap space-after-icon pull-right">
        <i class="icon-pencil"></i>Edit Site Text
    </a>
    <?php
}

?>
<h3><?php echo CHtml::encode($siteText->name); ?></h3>
<div class="well">
    <?php
    
    $this->beginWidget('CMarkdown', array('purifyOutput' => false));
    echo $siteText->markdown_content;
    $this->endWidget();
    
    ?>
</div>
