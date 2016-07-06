<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/styles.css?2016" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/prism.css" />
    <meta charset="utf-8">
    <title><?php echo CHtml::encode($this->pageTitle . ' - ' . \Yii::app()->name); ?></title>
    <?php
    // Load the Yii-Bootstrap CSS/JS.
    Yii::app()->bootstrap->init();
    ?>
</head>

<body>
<?php 

// Set up the menu.
$this->widget('bootstrap.widgets.TbNavbar', array(
    'type' => 'inverse',
    'brand' => CHtml::encode(Yii::app()->name),
    'brandUrl' => $this->createAbsoluteUrl('/'),
    'collapse' => false, // requires bootstrap-responsive.css
    'items' => array(
        array(
            'class' => 'bootstrap.widgets.TbMenu',
            'items' => array(
                array(
                    'label' => 'Dashboard',
                    'url' => array('dashboard/'),
                    'active' => ($this->id == 'dashboard'),
                    'visible' => ( ! Yii::app()->user->isGuest),
                ),
                array(
                    'label' => 'APIs',
                    'url' => array('api/'),
                    'active' => (($this->id == 'api') && ($this->route != 'api/playground')),
                    'visible' => ( ! Yii::app()->user->isGuest),
                ),
                array(
                    'label' => 'Keys',
                    'url' => array('key/'),
                    'active' => (($this->id == 'key') || ($this->id == 'keyRequest')),
                    'visible' => ( ! (Yii::app()->user->isGuest || Yii::app()->user->checkAccess('admin'))),
                ),
                array(
                    'label' => 'Keys',
                    'items' => array(
                        array(
                            'label' => 'Active Keys',
                            'url' => array('/key/all/'),
                            'active' => ($this->route == 'key/all'),
                        ),
                        array(
                            'label' => 'Pending Keys',
                            'url' => array('/key-request/'),
                            'active' => ($this->route == 'keyRequest/index'),
                        ),
                        array(
                            'label' => 'My Keys',
                            'url' => array('/key/mine/'),
                            'active' => ($this->route == 'key/mine'),
                        ),
                    ),
                    'visible' => Yii::app()->user->role == 'admin',
                    'active' => (($this->id == 'key') || ($this->id == 'keyRequest')),
                ),
                array(
                    'label' => 'Users',
                    'url' => array('/user/'),
                    'active' => ($this->id == 'user'),
                    'visible' => Yii::app()->user->checkAccess('admin'),
                ),
                array(
                    'label' => 'Playground',
                    'url' => array('/api/playground/'),
                    'active' => ($this->route == 'api/playground'),
                    'visible' => ( ! Yii::app()->user->isGuest),
                ),
                array(
                    'label' => 'FAQs',
                    'url' => array('/faq/'),
                    'active' => ($this->id == 'faq'),
                    'visible' => ( ! Yii::app()->user->isGuest),
                ),
            ),
        ),
        (
            Yii::app()->user->isGuest ?
            '<a class="btn btn-primary pull-right" href="' . $this->createUrl('/auth/login') . '">Login</a>' : 
            array(
                'class' => 'bootstrap.widgets.TbMenu',
                'htmlOptions' => array('class' => 'pull-right'),
                'items' => array(
                    array('label' => CHtml::encode(Yii::app()->user->name),
                        'visible' => !Yii::app()->user->isGuest,
                        'items' => array(
                            array('label' => 'Logout', 'url' => $this->createUrl('/auth/logout')),
                        ),
                    ),
                ),
            )
        ),
    ),
));

?>
<div class="container">
    <?php

    // Show the breadcrumbs (if any).
    if (isset($this->breadcrumbs)) {
        $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
            'homeLink' => false,
            'links' => $this->breadcrumbs,
        ));
    }

    // If there are any flash messages (alerts), show them.
    $this->widget('bootstrap.widgets.TbAlert', array());

    // Show the view content.
    echo $content;

    ?>
    <div class="clear"></div>
    <hr>
    <div id="footer">
        &copy; <?php echo date('Y'); ?> by SIL International Inc. | All Rights Reserved.<br/>
        Delivered by GTIS, USA Studio<br />
        <?php
        if ( ! Yii::app()->user->isGuest) {
            $versionInfo = Utils::getApplicationVersion();
            echo sprintf(
                '%s version <em>%s</em> built on <em>%s</em><br />',
                CHtml::encode(Yii::app()->name),
                CHtml::encode($versionInfo['version']),
                CHtml::encode($versionInfo['build'])
            );
        }
        ?>
        <a href="<?php echo Yii::app()->createUrl('/site/privacy-policy'); ?>">Privacy Policy</a>
        <?php if (( ! \Yii::app()->user->isGuest) && isset(\Yii::app()->params['adminEmail'])): ?>
        | <a href="mailto:<?php echo CHtml::encode(Yii::app()->params['adminEmail']); ?>">Contact Us</a>
        <?php endif; ?>
    </div><!-- footer -->
</div><!-- page -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl.'/js/prism.js'; ?>"></script>
<?php
$this->renderPartial('//partials/google-analytics', array(
    'user' => Yii::app()->user
));

if ( ! Yii::app()->user->isGuest) {
    ?><script type="text/javascript" src="https://www.jira.insitehome.org/s/en_USly0wxi-1988229788/6100/10/1.4.0-m3/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=04a5241e"></script>
    <?php
}
?>
</body>
</html>
