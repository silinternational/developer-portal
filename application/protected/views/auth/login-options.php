<?php
/* @var $this AuthController */
/* @var $loginOptions array */

// Set the page title.
$this->pageTitle = 'Login Options';

echo '<h2>' . \CHtml::encode($this->pageTitle) . '</h2> ';

foreach ($loginOptions as $displayName => $loginUrl) {
    echo sprintf(
        '<a href="%s" class="btn">Login with %s</a> ',
        $loginUrl,
        \CHtml::encode($displayName)
    );
}
