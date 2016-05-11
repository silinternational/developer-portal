<?php

class ActionLinkTest extends CTestCase
{
    public function testGetAsHtml_hasGivenUrlString()
    {
        // Arrange:
        $url = '/api/active-keys/fake-api/';
        
        // Act:
        $actionLink = new ActionLink($url);
        $linkAsHtml = $actionLink->getAsHtml();
        
        // Assert:
        $this->assertContains(
            $url,
            $linkAsHtml,
            'Failed to include the given URL string in the generated HTML.'
        );
    }
    
    public function testGetAsHtml_hasGivenText()
    {
        // Arrange:
        $url = '/api/active-keys/fake-api/';
        $text = 'Fake link text';
        
        // Act:
        $actionLink = new ActionLink($url, $text);
        $linkAsHtml = $actionLink->getAsHtml();
        
        // Assert:
        $this->assertContains(
            $text,
            $linkAsHtml,
            'Failed to include the given link text in the generated HTML.'
        );
    }
    
    public function testGetAsHtml_hasSpecifiedIcon()
    {
        // Arrange:
        $url = '/api/active-keys/fake-api/';
        $icon = 'fake-icon';
        
        // Act:
        $actionLink = new ActionLink($url, null, $icon);
        $linkAsHtml = $actionLink->getAsHtml();
        
        // Assert:
        $this->assertContains(
            $icon,
            $linkAsHtml,
            'Failed to include the specified icon in the generated HTML.'
        );
    }
    
    public function testGetAsHtml_hasGivenTextAndSpecifiedIcon()
    {
        // Arrange:
        $url = '/api/active-keys/fake-api/';
        $text = 'Fake link text';
        $icon = 'fake-icon';
        
        // Act:
        $actionLink = new ActionLink($url, $text, $icon);
        $linkAsHtml = $actionLink->getAsHtml();
        
        // Assert:
        $this->assertContains(
            $text,
            $linkAsHtml,
            'Failed to include the given link text in the generated HTML (when '
            . 'both link text and an icon are specified).'
        );
        $this->assertContains(
            $icon,
            $linkAsHtml,
            'Failed to include the specified icon in the generated HTML (when '
            . 'both link text and an icon are specified).'
        );
    }
}
