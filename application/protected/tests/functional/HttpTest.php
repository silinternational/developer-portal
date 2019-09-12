<?php

use Sil\DevPortal\tests\DbTestCase;

/**
 * Test accesing the pages of the website as an anonymous (?) user.
 */
class HttpTest extends DbTestCase
{
    public $fixtures = array(
        'apis' => '\Sil\DevPortal\models\Api',
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

    public function testWelcome()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/');
        $client = new EHttpClient($uri);

        $response = $client->request();
        $this->assertTrue(
            $response->isSuccessful(),
            'Failed call to ' . $uri . '. Code: ' . $response->getStatus()
        );
    }

    public function testApiActiveKeysRedirect()
    {
        Yii::import('ext.httpclient.*');
        $api = $this->apis('apiWithTwoPendingKeys');
        $uri = Yii::app()->createAbsoluteUrl(
            '/api/active-keys/', array('code' => $api->code)
        );
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiAddRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/add/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiPendingKeysRedirect()
    {
        Yii::import('ext.httpclient.*');
        $api = $this->apis('apiWithTwoPendingKeys');
        $uri = Yii::app()->createAbsoluteUrl(
            '/api/pending-keys/', array('code' => $api->code)
        );
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testBadUrl()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/not/rea/');
        $client = $this->get_client($uri);

        $expected = 404;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');
    }

    public function testKeyResetRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/key/reset/', array('id' => 1));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testKeyDeleteRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/key/delete/', array('id' => 1));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiRequestKeyRedirect()
    {
        Yii::import('ext.httpclient.*');
        $api = $this->apis('api1');
        $uri = Yii::app()->createAbsoluteUrl('/api/request-key/', array(
            'code' => $api->code,
        ));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiDetailsRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/details/', array(
            'code' => 'my-api',
        ));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiUsageRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/usage/my-code');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testDashboardRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/dashboard/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApisRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiEditRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/edit/', array('id' => 1));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiDeleteRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/delete/', array('id' => 1));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testApiPlayground()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/api/playground/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testKeyRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/key/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testKeyDetailsRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/key/details/', array('id' => 1));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testFaqAddRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/faq/add/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testFaqDetailsRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/faq/details/', array(
            'id' => 1,
        ));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testFaqEditRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/faq/edit/', array(
            'id' => 1,
        ));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testFaqRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/faq/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testUserRedirect()
    {
        Yii::import('ext.httpclient.*');
        $uri = Yii::app()->createAbsoluteUrl('/user/');
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testUserDetailsRedirect()
    {
        Yii::import('ext.httpclient.*');
        $user = $this->users('user1');
        $uri = Yii::app()->createAbsoluteUrl('/user/details/', array(
            'id' => $user->user_id,
        ));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }

    public function testUserEditRedirect()
    {
        Yii::import('ext.httpclient.*');
        $user = $this->users('user1');
        $uri = Yii::app()->createAbsoluteUrl('/user/edit/', array(
            'id' => $user->user_id,
        ));
        $client = $this->get_client($uri);

        $expected = 302;
        $response = $client->request();
        $code = $response->getStatus();
        $this->assertEquals($expected, $code, '>>> Got wrong response.');

        $expected = Yii::app()->createAbsoluteUrl('/auth/login/');
        $redirect = $response->getHeader('Location');
        $this->assertEquals($expected, $redirect, '>>> Got wrong redirect.');
    }
}
