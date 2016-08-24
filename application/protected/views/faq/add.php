<?php
/* @var $this AdminController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'FAQs' => array('/faq/'),
    'Add FAQ'
);

$this->pageTitle = 'Add FAQ';

// Show the form.
echo $form->render();
