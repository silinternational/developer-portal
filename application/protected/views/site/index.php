<?php
/* @var $this SiteController */

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
                    Login using ___<br />
                    Login using ___
                </div>
            </div>
        </div>
    </div>
    
    <div class="pad-horiz-extra">
        <div class="row-fluid">
            <div class="span8">
                <h2>Intro</h2>
                <p>
                    Lorem ipsum...
                </p>
            </div>

            <div class="span4">
                <h2>Popular APIs</h2>
                <dl>
                    <dt><a href="#">One</a></dt>
                    <dd>A sample API</dd>
                    
                    <dt><a href="#">Two</a></dt>
                    <dd>Another sample API</dd>
                    
                    <dt><a href="#">Three</a></dt>
                    <dd>A third sample API</dd>
                </dl>
                <a href="#" class="pull-right space-after-icon"><i class="icon-arrow-right"></i>Browse APIs</a>
            </div>
        </div>
    </div>
</div>
