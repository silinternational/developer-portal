<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $form YbHorizForm */

// Set the page title.
$this->pageTitle = 'Publish a new API';

// Set up the breadcrumbs.
$this->breadcrumbs += array(
    'APIs' => array('/api/'),
    $this->pageTitle
);

?>
<div class="pad-top">
    <?php
    
    // Show the form.
    echo $form;
    
    ?>
</div>
