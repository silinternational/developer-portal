<?php
/* @var $this UserController */
/* @var $form CForm */

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Dashboard' => array('/dashboard/'),
    'Users' => array('/user/'),
    $form->model->display_name => array(
        '/user/details/',
        'id' => $form->model->user_id
    ),
    'Edit User'
);

$this->pageTitle = 'Edit User';
$this->pageSubtitle = $form->model->display_name;

?>
<div class="row">
    <div class="span12">
        <?php

        // Show the form.
        echo $form;
        
        ?>
    </div>
</div>
