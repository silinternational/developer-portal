<?php
/* @var $this SiteController */

$this->pageTitle = 'Welcome';
?>
<div class="home-page">
    <div class="hero-unit">
        <div class="row">
            <div class="span7">
                <h2>
                    <span class="text-sm">Welcome to the </span><br />
                    <span class="sil-blue site-name"><?= \CHtml::encode(Yii::app()->name); ?></span>
                </h2>
            </div>
            
            <div class="span3">
                <div id="get-started">
                    <h2>Get Started</h2>
                    Login using ___<br />
                    Login using ___
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="span4">
            <h2>API Owners</h2>
            <p>
                Publish your APIs and manage access to who can use them.
            </p>
        </div>

        <div class="span4">
            <h2>API Consumers</h2>
            <p>
                Discover new APIs, read integration documentation, and request access
                to APIs.
            </p>
        </div>

        <div class="span4">
            <h2>Getting Started</h2>
            <p>
                To begin, click the Login button above.
            </p>
        </div>
    </div>
</div>
