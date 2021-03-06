<?php
/* @var $this \Sil\DevPortal\controllers\FaqController */
/* @var $faq Sil\DevPortal\models\Faq */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'FAQs' => array('/faq/'),
    $faq->question,
);

$this->pageTitle = $faq->question;

// If the user is an admin, show an edit link.
if (\Yii::app()->user->checkAccess('admin')) {
    ?>
    <a href="<?php echo $this->createUrl('/faq/edit/', array('id' => $faq->faq_id)); ?>" 
       class="nowrap space-after-icon pull-right">
        <i class="icon-pencil"></i>Edit FAQ
    </a>
    <?php
}

?>
<h3><?php echo CHtml::encode($faq->question); ?></h3>
<div class="well">
    <?php
    
    $this->beginWidget('CMarkdown', array('purifyOutput' => true));
    echo $faq->answer;
    $this->endWidget();
    
    ?>
</div>
