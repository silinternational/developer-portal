<?php
/* @var $this SiteTextController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Site Texts' => array('/site-text/'),
    $form->model->name => array(
        '/site-text/details/',
        'id' => $form->model->site_text_id,
    ),
    'Edit Site Text'
);

$this->pageTitle = 'Edit "' . $form->model->name . '"';

// Show the form.
?>
<div class="pad-top">
    <?php echo $form; ?>
</div>
