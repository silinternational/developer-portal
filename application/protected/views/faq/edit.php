<?php
/* @var $this FaqController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
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
