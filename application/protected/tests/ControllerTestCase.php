<?php
namespace Sil\DevPortal\tests;

use CHttpRequest;
use Yii;

class ControllerTestCase extends TestCase
{
    /**
     * The name of the controller class (e.g. - "SiteController") expected to
     * handle the URLs in these tests.
     * @var string
     */
    protected $expectedController;
    
    /**
     * Determines what controller and action a given URL will be routed to.
     * @param string $pathToCheck The portion of the URL to check that comes
     *     after the domain name (e.g. "/desired/path" for checking
     *     "http://www.example.com/desired/path").
     * @param boolean $shouldPass Whether the given URL is expected to pass
     *     (meaning it's a valid route that maps to a valid controller) or not
     *     (and thus should fail). Defaults to true (meaning it expects a valid
     *     route).
     * @return (array|null) The controller instance and the action ID. Null if
     *     the controller class does not exist or the route is invalid. See 
     *     http://www.yiiframework.com/doc/api/1.1/CWebApplication#createController-detail
     */
    protected function getControllerAndActionForUrl($pathToCheck,
                                                    $shouldPass = true)
    {
        // Fake the expected $_SERVER variables.
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        $_SERVER['SCRIPT_NAME'] =  '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $pathToCheck;

        // Get the Controller (object) and action (string) to which that URL was
        // routed.
        $route = Yii::app()->getUrlManager()->parseUrl(new CHttpRequest());
        $routedUrl = Yii::app()->createController($route);
        
        // If this test is intended to pass...
        if ($shouldPass) {
            
            // Make sure the controller class exists and the route is valid (as
            // indicated by a non-null return value from CWebApplication's
            // createController function).
            $this->assertNotNull($routedUrl,
                    'Controller class does not exist or route is invalid ' .
                    'for "' . $pathToCheck . '"');

            // Make the returned array contains two entries (presumably the
            // controller instance and the action string).
            $this->assertEquals(2, count($routedUrl),
                    'Unexpected data returned when getting route for "' .
                    $pathToCheck . '"');
        }
        // Otherwise (i.e. - its asking for a non-existent controller or
        // otherwise invalid path)...
        else {
            
            // Make sure it did in fact fail (as indicated by a null return
            // value from CWebApplication's createController function).
            $this->assertNull($routedUrl,
                    'Invalid URL ("' . $pathToCheck . '") did not fail ' .
                    'routing');
        }
        
        // Return the route data.
        return $routedUrl;
    }
    
    /**
     * Assert that the routing of the given REQUEST_URI path (e.g. - "/page")
     * FAILS as expected.
     * @param string $requestUriToCheck The portion of the URL to check that
     *     comes after the domain name (e.g. "/desired/path" for checking
     *     "http://www.example.com/desired/path").
     */
    protected function checkInvalidUrlRoute($requestUriToCheck)
    {
        // Make sure we are NOT able to get the Controller (object) and action
        // (string) for the desired URL.
        $this->getControllerAndActionForUrl($requestUriToCheck, false);
    }
    
    /**
     * Assert that the given REQUEST_URI path (e.g. - "/page") was routed to the
     * expected Controller and action.
     * @param string $requestUriToCheck The portion of the URL to check that
     *     comes after the domain name (e.g. "/desired/path" for checking
     *     "http://www.example.com/desired/path").
     * @param string $expectedAction The name of the action (e.g. "page")
     *     expected to handle the given URL.
     */
    protected function checkUrlRoute($requestUriToCheck, $expectedAction)
    {
        // Get the Controller (object) and action (string) for the desired URL.
        $routeData = $this->getControllerAndActionForUrl($requestUriToCheck);
        
        // If the returned route data is null, an assertion should have already
        // failed, so skip the rest of this test.
        if (is_null($routeData)) return;
        
        // Extract the Controller instance and action string.
        list($controller, $action) = $routeData;

        // Make sure it's what we expected.
        $this->assertEquals($this->expectedController, get_class($controller),
                'URL "' . $requestUriToCheck . '" not routed to correct ' .
                'controller');
        $this->assertEquals($expectedAction, $action,
                'URL "' . $requestUriToCheck . '" not routed to correct ' .
                'action');
    }
}
