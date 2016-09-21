<?php
/* @var $this \Sil\DevPortal\controllers\FaqController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'FAQs' => array('/faq/'),
    $form->model->question => array(
        '/faq/details/',
        'id' => $form->model->faq_id,
    ),
    'Edit FAQ'
);

$this->pageTitle = 'Edit FAQ';

// Show the form.
?>
<div class="pad-top">
    <?php echo $form; ?>
</div>
