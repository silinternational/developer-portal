<?php
/* @var $this SiteController */

$this->pageTitle = 'Welcome';
?>

<div class="hero-unit">
    <h1>Welcome to the <br /><span class="sil-blue"><?php echo CHtml::encode(Yii::app()->name); ?></span></h1>
    <p></p>
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
