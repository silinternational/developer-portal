<?php
/* @var $this SiteController */
/* @var $popularApis \Api[] */

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
                    <?php
                    foreach ($popularApis as $api) {
                        echo sprintf(
                            '<dt><a href="%s">%s&nbsp;</a></dt> <dd>%s&nbsp;</dd> ',
                            \CHtml::encode($this->createUrl(
                                'api/details',
                                array('code' => $api->code)
                            )),
                            \CHtml::encode($api->display_name),
                            \CHtml::encode($api->brief_description)
                        );
                    }
                    ?>
                </dl>
                <a href="#" class="pull-right space-before-icon">Browse APIs<i class="icon-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>
