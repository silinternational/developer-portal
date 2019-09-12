<?php

use Sil\DevPortal\tests\TestCase;

class ControllerTest extends TestCase
{
    public function testGeneratePageTitleHtml_hasTitle()
    {
        // Arrange:
        $controller = new Controller('fake');
        $pageTitle = 'The Page Title';
        $controller->pageTitle = $pageTitle;
        
        // Act:
        $result = $controller->generatePageTitleHtml();
        
        // Assert:
        $this->assertStringContainsString(
            $pageTitle,
            $result,
            'Failed to include the page title in the resulting HTML.'
        );
    }
    
    public function testGeneratePageTitleHtml_noTitle()
    {
        // Arrange:
        $controller = new Controller('fake');
        $controller->pageTitle = '';
        
        // Act:
        $result = $controller->generatePageTitleHtml();
        
        // Assert:
        $this->assertSame(
            '',
            $result,
            'Failed to return an empty string for the HTML when there is no '
            . 'page title.'
        );
    }
    
    public function testGeneratePageTitleHtml_hasSubtitle()
    {
        // Arrange:
        $controller = new Controller('fake');
        $pageTitle = 'The Page Title';
        $pageSubtitle = 'The Page "Subtitle"';
        $encodedPageSubtitle = 'The Page &quot;Subtitle&quot;';
        $controller->pageTitle = $pageTitle;
        $controller->pageSubtitle = $pageSubtitle;
        
        // Act:
        $result = $controller->generatePageTitleHtml();
        
        // Assert:
        $this->assertStringContainsString(
            $encodedPageSubtitle,
            $result,
            'Failed to include the (HTML encoded) page subtitle in the '
            . 'resulting HTML.'
        );
    }
    
    public function testGeneratePageTitleHtml_noSubtitle()
    {
        // Arrange:
        $controller = new Controller('fake');
        $controller->pageSubtitle = null;
        
        // Act:
        $result = $controller->generatePageTitleHtml();
        
        // Assert:
        $this->assertStringNotContainsString(
            '<small>',
            $result,
            'Incorrectly included the HTML tag for holding the page subtitle'
            . 'when no subtitle was set.'
        );
    }
    
    public function testGeneratePageTitleHtml_hasHtmlSubtitle()
    {
        // Arrange:
        $controller = new Controller('fake');
        $pageTitle = 'The Page Title';
        $pageSubtitle = 'The Page "Subtitle"';
        $controller->pageTitle = $pageTitle;
        $controller->pageSubtitle = $pageSubtitle;
        $controller->pageSubtitleIsHtml = true;
        
        // Act:
        $result = $controller->generatePageTitleHtml();
        
        // Assert:
        $this->assertStringContainsString(
            $pageSubtitle,
            $result,
            'Failed to include the page subtitle HTML (unencoded) in the '
            . 'resulting HTML.'
        );
    }
}
