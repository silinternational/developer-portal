<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $form->model->display_name => array(
        '/api/details/',
        'code' => $form->model->code,
    ),
    'Edit API',
);

$this->pageTitle = 'Edit API';
$this->pageSubtitle = $form->model->getStyledPublicUrlHtml('text-dark');
$this->pageSubtitleIsHtml = true;

// Show the form.
echo $form;
