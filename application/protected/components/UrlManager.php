<?php

class UrlManager extends CUrlManager
{
    /**
     * Whether to show the "index.php" when creating a URL.
     * @var boolean $showScriptName 
     */
    public $showScriptName = true;
    public $appendParams = false;
    public $useStrictParsing = false;
    public $urlSuffix = '/';
 
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        $route = preg_replace_callback('/(?<![A-Z])[A-Z]/', function($matches) {
            return '-' . lcfirst($matches[0]);
        }, $route);
        return parent::createUrl($route, $params, $ampersand);
    }
 
    public function parseUrl($request)
    {
        $route = parent::parseUrl($request);
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $route))));
    }
}
