<?php
/* @var $this \Sil\DevPortal\controllers\AdminController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'FAQs' => array('/faq/'),
    'Add FAQ'
);

$this->pageTitle = 'Add FAQ';

// Show the form.
echo $form->render();
