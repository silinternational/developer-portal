<?php

/**
 * Test accessing the pages of the website as a UnitTestUser.
 */
class HttpAdminTest extends CDbTestCase
{
    public $fixtures = array(
        'apis' => '\Sil\DevPortal\models\Api',
        'faqs' => '\Sil\DevPortal\models\Faq',
        'users' => '\Sil\DevPortal\models\User',
        'keys' => '\Sil\DevPortal\models\Key',
    );

    private function get_client($uri, $redirects = 1, $timeout = 10)
    {
        return new EHttpClient($uri, array(
            'maxredirects' => $redirects,
            'timeout' => $timeout,
        ));
    }

    /**
     * Assert that the given content is found on the page at the specified
     * route (and route params, if any).
     * 
     * @param array $contentPieces A list of the pieces of content to look for
     *     in the response body.
     * @param string $route The controller/action route.
     * @param array $params (Optional:) The route params. Defaults to none.
     * @param number $expectedStatusCode (Optional:) The expected HTTP status
     *     code. Defaults to 200.
     */
    protected function assertContentPiecesFoundOnPage(
        $contentPieces,
        $route,
        $params = array(),
        $expectedStatusCode = 200
    ) {
        // Prepare to make the URL request.
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl($route, $params);
        $client = $this->get_client($uri);
        //$client->setHeaders('x-http-environment-override', 'testing');

        // Make the request and check the HTTP status code.
        $response = $client->request();
        if ($this->isLoginRedirect($response)) {
            $this->login();
            $response = $client->request();
        }
        die(var_dump($response));
        $actualStatusCode = $response->getStatus();
        $this->assertEquals(
            $expectedStatusCode,
            $actualStatusCode,
            '>>> Got wrong HTTP status code response.'
        );

        // Check for the specified content.
        $responseBody = $response->getBody();
        foreach ($contentPieces as $contentPiece) {
            $this->assertContains(
                $contentPiece,
                $responseBody,
                '>>> Page did NOT contain the specified piece of content.'
            );
        }
    }

    /**
     * Assert that the specified route (with optional params) returns a redirect
     * (of the specified type) to the given URL.
     * 
     * @param string $requestRoute The requested controller/action route.
     * @param array $requestParams The requested route's params. For none, use
     *     an empty array.
     * @param string $expectedRedirectRoute The controller/action route to use
     *     for assembling the expected redirect URL.
     * @param array $expectedRedirectParams (Optional:) The route params to use
     *     for assembling the expected redirect URL. Defaults to none.
     * @param number $expectedStatusCode (Optional:) The expected HTTP status
     *     code. Defaults to 301 (a permanent redirect).
     */
    protected function assertPageReturnsRedirect(
        $requestRoute,
        $requestParams,
        $expectedRedirectRoute,
        $expectedRedirectParams = array(),
        $expectedStatusCode = 301
    ) {
        // Make the (authenticated) request and check the HTTP status code.
        $response = $this->makeAuthenticatedRequest(
            $requestRoute,
            $requestParams
        );
        $actualStatusCode = $response->getStatus();
        $this->assertEquals(
            $expectedStatusCode,
            $actualStatusCode,
            '>>> Got wrong HTTP status code response.'
        );

        // Check for the expected redirect URL.
        $expectedRedirectUrl = Yii::app()->createAbsoluteUrl(
            $expectedRedirectRoute,
            $expectedRedirectParams
        );
        $actualRedirectUrl = $response->getHeader('Location');
        $this->assertEquals(
            $expectedRedirectUrl,
            $actualRedirectUrl,
            '>>> Received the wrong redirect URL.'
        );
    }
    
    protected function makeAuthenticatedRequest($requestRoute, $requestParams)
    {
        // Prepare to make the URL request.
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl($requestRoute, $requestParams);
        $client = $this->get_client($uri);
        //$client->setHeaders('x-http-environment-override', 'testing');

        // Make the request and check the HTTP status code.
        $response = $client->request();
        if ($this->isLoginRedirect($response)) {
            $this->login($client);
            $response = $client->request();
        }
        return $response;
    }
    
    /**
     * Determine whether the given response was a login redirect.
     * 
     * @param EHttpResponse $response
     * @throws EHttpClientException
     */
    protected function isLoginRedirect($response)
    {
        $loginUrl = \Yii::app()->createAbsoluteUrl('auth/login');
        if ($response->getHeader('Location') === $loginUrl) {
            return true;
        }
        return false;
    }
    
    /**
     * Log this client into the website.
     * 
     * @param EHttpClient $client
     */
    protected function login($client)
    {
        
    }
    
    public function testKeyActive()
    {
        $this->assertContentPiecesFoundOnPage(
            array('All Keys'),
            '/key/active/'
        );
    }

    public function testKeyMine()
    {
        $this->assertContentPiecesFoundOnPage(
            array('My Keys'),
            '/key/mine/'
        );
    }

    public function testKeyDelete()
    {
        $key = $this->keys('approvedKey');
        $this->assertContentPiecesFoundOnPage(
            array('Revoke Key'),
            '/key/delete/',
            array('id' => $key->key_id)
        );
    }

    public function testApi()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Browse APIs'),
            '/api/'
        );
    }

    public function testApiActiveKeys()
    {
        $api = $this->apis('apiWithTwoPendingKeys');
        $this->assertContentPiecesFoundOnPage(
            array('Active Keys'),
            '/api/active-keys/',
            array('code' => $api->code)
        );
    }

    public function testApiPendingKeys()
    {
        $api = $this->apis('apiWithTwoPendingKeys');
        $this->assertContentPiecesFoundOnPage(
            array('Pending Keys'),
            '/api/pending-keys/',
            array('code' => $api->code)
        );
    }

    public function testApiPlayground()
    {
        $this->assertContentPiecesFoundOnPage(
            array('API Playground'),
            '/api/playground/'
        );
    }

    public function testApiRequestKey()
    {
        $api = $this->apis('api1');
        $this->assertContentPiecesFoundOnPage(
            array('Request Key', CHtml::encode($api->display_name)),
            '/api/request-key/',
            array('id' => $api->code)
        );
    }

    public function testApiUsage()
    {
        $this->assertContentPiecesFoundOnPage(
            array('API Usage'),
            '/api/usage/',
            array('code' => 'auto')
        );
    }

    public function testApiDocsEdit()
    {
        $api = $this->apis('api4');
        $this->assertContentPiecesFoundOnPage(
            array('Documentation', $api->display_name),
            '/api/docs-edit/',
            array('code' => $api->code)
        );
    }

    public function testApiDetails()
    {
        $api = $this->apis('api4');
        $this->assertContentPiecesFoundOnPage(
            array($api->display_name, 'Query rate limits'),
            '/api/details/',
            array('code' => $api->code)
        );
    }

    public function testApiEdit()
    {
        $api = $this->apis('api4');
        $this->assertContentPiecesFoundOnPage(
            array('Edit API'),
            '/api/edit/',
            array('code' => $api->code)
        );
    }

    public function testApiAdd()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Publish a new API'),
            '/api/add/'
        );
    }

    public function testFaqAdd()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Add FAQ'),
            '/faq/add/'
        );
    }

    public function testDashboard()
    {
        $this->assertContentPiecesFoundOnPage(
            array('My Usage'),
            '/dashboard/'
        );
    }

    public function testFaq()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Frequently Asked Questions'),
            '/faq/'
        );
    }

    public function testFaqDetails()
    {
        $faq = $this->faqs('faq1');
        $this->assertContentPiecesFoundOnPage(
            array(CHtml::encode($faq->answer)),
            '/faq/details/',
            array('id' => $faq->faq_id)
        );
    }

    public function testFaqEdit()
    {
        $faq = $this->faqs('faq1');
        $this->assertContentPiecesFoundOnPage(
            array('Edit FAQ'),
            '/faq/edit/',
            array('id' => $faq->faq_id)
        );
    }

    public function testApiDelete()
    {
        $api = $this->apis('api4');
        $this->assertContentPiecesFoundOnPage(
            array('Delete API'),
            '/api/delete/', array('code' => $api->code)
        );
    }

    public function testKeyReset()
    {
        $key = $this->keys('key1');
        $this->assertContentPiecesFoundOnPage(
            array('Reset Key'),
            '/key/reset/',
            array('id' => $key->key_id)
        );
    }

    public function testKeyRevoke()
    {
        $key = $this->keys('key1');
        $this->assertContentPiecesFoundOnPage(
            array('Revoke Key'),
            '/key/delete/',
            array('id' => $key->key_id)
        );
    }

    public function testKey()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Pending Keys'),
            '/key/'
        );
    }

    public function testKeyDelete_pending()
    {
        $key = $this->keys('pendingKeyUser6');
        $this->assertContentPiecesFoundOnPage(
            array('Delete Key'),
            '/key/delete/',
            array('id' => $key->key_id)
        );
    }

    public function testKeyDetails()
    {
        $key = $this->keys('key1');
        $this->assertContentPiecesFoundOnPage(
            array('Key Details'),
            '/key/details/',
            array('id' => $key->key_id)
        );
    }

    public function testUser()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Users'),
            '/user/'
        );
    }

    public function testUserDetails()
    {
        $user = $this->users('user1');
        $this->assertContentPiecesFoundOnPage(
            array('User Details'),
            '/user/details/',
            array('id' => $user->user_id)
        );
    }

    public function testUserEdit()
    {
        $user = $this->users('user1');
        $this->assertContentPiecesFoundOnPage(
            array('Edit User'),
            '/user/edit/',
            array('id' => $user->user_id)
        );
    }
}
