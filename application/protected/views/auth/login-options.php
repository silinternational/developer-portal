<?php
/* @var $this \Sil\DevPortal\controllers\AuthController */
/* @var $loginOptions Sil\DevPortal\components\LoginOption[] */

// Set the page title.
$this->pageTitle = 'Login Options';

echo '<h2>' . \CHtml::encode($this->pageTitle) . '</h2> ';

foreach ($loginOptions as $loginOption) {
    echo $loginOption->getLinkHtml('btn');
}
