<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>
<div id="content">
    <?php
    
    // Show the page title/subtitle (if any).
    echo $this->generatePageTitleHtml();
    
    // Show the page content.
	echo $content;
    
    ?>
</div><!-- content -->
<?php $this->endContent(); ?>