<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('/api/'),
    $form->model->display_name => array(
        '/api/details/',
        'code' => $form->model->code,
    ),
    'Edit Documentation',
);

$this->pageTitle = 'Edit Documentation';
$this->pageSubtitle = $form->model->display_name;

// Show the form.
echo $form;
