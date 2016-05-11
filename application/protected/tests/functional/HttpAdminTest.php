<?php

/**
 * Test accesing the pages of the website as a UnitTestUser.
 */
class HttpAdminTest extends CDbTestCase
{
    public $fixtures = array(
        'apis' => 'Api',
        'faqs' => 'Faq',
        'users' => 'User',
        'keys' => 'Key',
        'keyRequests' => 'KeyRequest',
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
        $client->setHeaders('x-http-environment-override', 'testing');

        // Make the request and check the HTTP status code.
        $response = $client->request();
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
        // Prepare to make the URL request.
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl($requestRoute, $requestParams);
        $client = $this->get_client($uri);
        $client->setHeaders('x-http-environment-override', 'testing');

        // Make the request and check the HTTP status code.
        $response = $client->request();
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
    
    public function testPortalRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/',
            array(),
            '/dashboard/'
        );
    }

    public function testKeyAll()
    {
        $this->assertContentPiecesFoundOnPage(
            array('All Keys'),
            '/key/all/'
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

    public function testKeyDetails()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Key Details'),
            '/key/details/',
            array('id' => 1)
        );
    }

    public function testPortalKeyDetailsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/key-details/',
            array('id' => 1),
            '/key/details/',
            array('id' => 1)
        );
    }

    public function testPortalKeysRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/keys/',
            array(),
            '/key/mine/'
        );
    }

    public function testPortalKeyResetRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/reset-key/',
            array('id' => 1),
            '/key/reset/',
            array('id' => 1)
        );
    }

    public function testPortalKeyDeleteRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/delete-key/',
            array('id' => 1),
            '/key/delete/',
            array('id' => 1)
        );
    }

    public function testPortalRequestKeyRedirect()
    {
        $api = $this->apis('api1');
        $this->assertPageReturnsRedirect(
            '/portal/request-key/',
            array('id' => $api->api_id),
            '/api/request-key/',
            array('code' => $api->code)
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
        $api = $this->apis('apiWithTwoPendingKeyRequests');
        $this->assertContentPiecesFoundOnPage(
            array('Active Keys'),
            '/api/active-keys/',
            array('code' => $api->code)
        );
    }

    public function testApiPendingKeys()
    {
        $api = $this->apis('apiWithTwoPendingKeyRequests');
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

    public function testPortalApisRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/apis/',
            array(),
            '/api/'
        );
    }

    public function testPortalApiDetailsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/api-details/',
            array('code' => 'auto'),
            '/api/details/',
            array('code' => 'auto')
        );
    }

    public function testPortalApiDocsRedirect()
    {
        $api = $this->apis('api4');
        $this->assertPageReturnsRedirect(
            '/portal/api-docs/',
            array('code' => $api->code),
            '/api/details/',
            array('code' => $api->code)
        );
    }

    public function testPortalApiUsageRedirect()
    {
        $api = $this->apis('api4');
        $this->assertPageReturnsRedirect(
            '/portal/api-usage/',
            array('code' => $api->code),
            '/api/usage/',
            array('code' => $api->code)
        );
    }

    public function testAdminRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/',
            array(),
            '/dashboard/'
        );
    }

    public function testAdminApisRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/apis/',
            array(),
            '/api/'
        );
    }

    public function testAdminApiDetailsRedirect()
    {
        $api = $this->apis('api4');
        $this->assertPageReturnsRedirect(
            '/admin/api-details/',
            array('code' => $api->code),
            '/api/details/',
            array('code' => $api->code)
        );
    }

    public function testAdminApiDocsEditRedirect()
    {
        $api = $this->apis('api4');
        $this->assertPageReturnsRedirect(
            '/admin/api-docs-edit/',
            array('id' => $api->api_id),
            '/api/docs-edit/',
            array('code' => $api->code)
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

    public function testAdminApiEditRedirect()
    {
        $api = $this->apis('api4');
        $this->assertPageReturnsRedirect(
            '/admin/api-edit/',
            array('id' => $api->api_id),
            '/api/edit/',
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

    public function testAdminApiAddRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/api-add/',
            array(),
            '/api/add/'
        );
    }

    public function testApiAdd()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Publish a new API'),
            '/api/add/'
        );
    }

    public function testAdminFaqAddRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/faq-add/',
            array(),
            '/faq/add/'
        );
    }

    public function testAdminFaqsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/faqs/',
            array(),
            '/faq/'
        );
    }

    public function testPortalFaqsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/faqs/',
            array(),
            '/faq/'
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
            array('Dashboard'),
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

    public function testAdminFaqDetailsRedirect()
    {
        $faq = $this->faqs('faq1');
        $this->assertPageReturnsRedirect(
            '/admin/faq-details/',
            array('id' => $faq->faq_id),
            '/faq/details/',
            array('id' => $faq->faq_id)
        );
    }

    public function testAdminFaqEditRedirect()
    {
        $faq = $this->faqs('faq1');
        $this->assertPageReturnsRedirect(
            '/admin/faq-edit/',
            array('id' => $faq->faq_id),
            '/faq/edit/',
            array('id' => $faq->faq_id)
        );
    }

    public function testPortalFaqDetailsRedirect()
    {
        $faq = $this->faqs('faq1');
        $this->assertPageReturnsRedirect(
            '/portal/faq-details/',
            array('id' => $faq->faq_id),
            '/faq/details/',
            array('id' => $faq->faq_id)
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

    public function testApiAddContactUs()
    {
        $this->assertContentPiecesFoundOnPage(
            array('please contact us'),
            '/api/add-contact-us/'
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

    public function testAdminApiDeleteRedirect()
    {
        $api = $this->apis('api4');
        $this->assertPageReturnsRedirect(
            '/admin/api-delete/',
            array('id' => $api->api_id),
            '/api/delete/',
            array('code' => $api->code)
        );
    }

    public function testAdminKeysRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/keys/',
            array(),
            '/key/all/'
        );
    }

    public function testAdminKeyDetailsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/key-details',
            array('id' => 1),
            '/key/details/',
            array('id' => 1)
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

    public function testAdminKeyResetRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/reset-key',
            array('id' => 1),
            '/key/reset/',
            array('id' => 1)
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

    public function testAdminKeyRevokeRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/revoke-key',
            array('id' => 1),
            '/key/delete/',
            array('id' => 1)
        );
    }

    public function testKeyRequest()
    {
        $this->assertContentPiecesFoundOnPage(
            array('Pending Key Requests'),
            '/key-request/'
        );
    }

    public function testAdminKeyRequestsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/key-requests/',
            array(),
            '/key-request/'
        );
    }

    public function testPortalKeyRequestsRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/portal/key-requests/',
            array(),
            '/key-request/'
        );
    }

    public function testKeyRequestDelete_approvedRedirect()
    {
        $keyRequest = $this->keyRequests('approvedKeyRequestUser4');
        $this->assertPageReturnsRedirect(
            '/key-request/delete/',
            array('id' => $keyRequest->key_request_id),
            '/key-request/details/',
            array('id' => $keyRequest->key_request_id),
            302
        );
    }

    public function testKeyRequestDelete_pending()
    {
        $keyRequest = $this->keyRequests('pendingKeyRequestUser6');
        $this->assertContentPiecesFoundOnPage(
            array('Delete Key Request'),
            '/key-request/delete/',
            array('id' => $keyRequest->key_request_id)
        );
    }

    public function testKeyRequestDetails()
    {
        $keyRequest = $this->keyRequests('keyRequest1');
        $this->assertContentPiecesFoundOnPage(
            array('Key Request Details'),
            '/key-request/details/',
            array('id' => $keyRequest->key_request_id)
        );
    }

    public function testAdminKeyRequestDetailsRedirect()
    {
        $keyRequest = $this->keyRequests('keyRequest1');
        $this->assertPageReturnsRedirect(
            '/admin/key-request-details/',
            array('id' => $keyRequest->key_request_id),
            '/key-request/details/',
            array('id' => $keyRequest->key_request_id)
        );
    }

    public function testAdminUsersRedirect()
    {
        $this->assertPageReturnsRedirect(
            '/admin/users/',
            array(),
            '/user/'
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

    public function testAdminUserDetailsRedirect()
    {
        $user = $this->users('user1');
        $this->assertPageReturnsRedirect(
            '/admin/user-details/',
            array('id' => $user->user_id),
            '/user/details/',
            array('id' => $user->user_id)
        );
    }

    public function testAdminUserEditRedirect()
    {
        $user = $this->users('user1');
        $this->assertPageReturnsRedirect(
            '/admin/user-edit/',
            array('id' => $user->user_id),
            '/user/edit/',
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
