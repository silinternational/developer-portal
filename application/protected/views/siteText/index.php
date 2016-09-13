<?php
/* @var $this SiteTextController */
/* @var $siteTextDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Site Texts',
);

$this->pageTitle = 'Site Text';

?>
<div class="row pad-top">
    <div class="span12">
        <?php
        
        // Show the list of Site Texts (each as a link).
        foreach ($siteTexts as $siteText) {
            echo sprintf(
                '<p><a href="%s">%s</a></p>',
                $this->createUrl('/site-text/details/', array('id' => $siteText->site_text_id)),
                CHtml::encode($siteText->name)
            );
        }
        
        if (empty($siteTexts)) {
            echo '<i class="muted">None</i>';
        }
 
        ?>
    </div>
</div>
<?php

// If the user is an admin, allow adding Site Texts.
if (\Yii::app()->user->checkAccess('admin')) {
    ?>
    <div class="row pad-top">
        <div class="span12">
            <a href="<?php echo $this->createUrl('/site-text/add/'); ?>" 
               class="btn space-after-icon" >
                <i class="icon-plus"></i>Add Site Text
            </a>
        </div>
    </div>
    <?php
}
