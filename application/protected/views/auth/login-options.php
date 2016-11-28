<?php
/* @var $this \Sil\DevPortal\controllers\AuthController */
/* @var $loginOptions Sil\DevPortal\components\LoginOption[] */

// Set the page title.
$this->pageTitle = 'Login Options';
?>
<div class="text-center">
    <div style="display: inline-block; margin: auto;">
        <h2><?= \CHtml::encode($this->pageTitle); ?></h2>
        <?php foreach ($loginOptions as $loginOption): ?>
            <div style="display: inline-block; margin: 4px;">
                <?= $loginOption->getLinkHtml('btn btn-success login-logo-button'); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
