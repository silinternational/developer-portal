<?php
/* @var $this \Sil\DevPortal\controllers\SiteController */
/* @var $loginOptions Sil\DevPortal\components\LoginOption[] */
/* @var $logoUrls string[] */
/* @var $homeLowerLeftHtml string|null */
/* @var $homeLowerRightHtml string|null */
/* @var $popularApis \Sil\DevPortal\models\Api[]|null */

$this->breadcrumbs = array();
$this->pageTitle = 'Welcome';
?>
<div class="home-page">
    <div class="hero-unit">
        <div class="row-fluid">
            <div class="span8">
                <h2>
                    <span class="text-sm">Welcome to the </span><br />
                    <span class="sil-blue site-name"><?= \CHtml::encode(Yii::app()->name); ?></span>
                </h2>
            </div>
            
            <div class="span4">
                <div id="get-started">
                    <h2>Get Started</h2>
                    <div style="display: inline-block;">
                        <?php foreach ($loginOptions as $loginOption): ?>
                            <div style="margin: 4px;"><?= $loginOption->getLinkHtml('btn btn-success login-logo-button'); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="pad-horiz-extra">
        <div class="row-fluid">
            <div class="span8">
                <?= $homeLowerLeftHtml; ?>
            </div>

            <div class="span4">
                <?php if ($popularApis === null): ?>
                    <?php $homeLowerRightHtml; ?>
                <?php else: ?>
                    <?php
                    $this->renderPartial('//partials/popular-apis', array(
                        'popularApis' => $popularApis,
                    ));
                    ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ( ! empty($logoUrls)): ?>
            <div id="logo-strip">
                <div>
                    <?php foreach ($logoUrls as $logoUrl): ?>
                        <img src="<?= \CHtml::encode($logoUrl); ?>" />
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
