<?php
/* @var $this \Sil\DevPortal\controllers\FaqController */
/* @var $faqDataProvider CDataProvider */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'FAQs',
);

$this->pageTitle = 'Frequently Asked Questions (FAQs)';

?>
<div class="row pad-top">
    <div class="span12">
        <?php
        
        // Show the list of FAQs (each as a link).
        foreach ($faqs as $faq) {
            echo sprintf(
                '<p><a href="%s">%s</a></p>',
                $this->createUrl('/faq/details/', array('id' => $faq->faq_id)),
                CHtml::encode($faq->question)
            );
        }
 
        ?>
    </div>
</div>
<?php

// If the user is an admin, allow adding FAQs.
if (\Yii::app()->user->checkAccess('admin')) {
    ?>
    <div class="row pad-top">
        <div class="span12">
            <a href="<?php echo $this->createUrl('/faq/add/'); ?>" 
               class="btn space-after-icon" >
                <i class="icon-plus"></i>Add FAQ
            </a>
        </div>
    </div>
    <?php
}
