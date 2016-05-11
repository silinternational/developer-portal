<?php
/* @var $this ApiController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
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
