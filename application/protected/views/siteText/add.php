<?php
/* @var $this AdminController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'Site Texts' => array('/site-text/'),
    'Add Site Text'
);

$this->pageTitle = 'Add Site Text';

// Show the form.
echo $form->render();
