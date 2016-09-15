<?php
/* @var $this \Sil\DevPortal\controllers\ApiController */
/* @var $contactEmail string */

// Set the page title.
$this->pageTitle = 'Publish a new API';

// Set up the breadcrumbs.
$this->breadcrumbs = array(
    'Home' => array('/dashboard/'),
    'APIs' => array('/api/'),
    $this->pageTitle
);

?>
<p>
    If you would like to add your own API to our API Developer Portal, 
    please contact us at <?php echo sprintf(
        '<a href="mailto:%s?subject=%s">%s</a>',
        $contactEmail,
        'Adding an API to the API Developer Portal',
        $contactEmail
    ); ?>.
</p>
