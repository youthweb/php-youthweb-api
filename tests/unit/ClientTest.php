<?php

namespace Youthweb\Api\Tests;

use Youthweb\Api\Client;
use InvalidArgumentException;

class ClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testSetOptionsThroughConstructor()
	{
		$url = 'http://test.local';

		$client = new Client([
			'api_domain' => $url,
		]);

		$this->assertSame($url, $client->getUrl());
	}

	/**
	 * @test
	 */
	public function testSetUrlReturnsClient()
	{
		$client = new Client();

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setUrl('http://test.local'));
	}

	/**
	 * @test
	 */
	public function testGetUrlReturnsValueFromSetUrl()
	{
		$client = (new Client())->setUrl('http://test.local');

		$this->assertSame('http://test.local', $client->getUrl());
	}

	/**
	 * @test
	 */
	public function testSetUserCredentialsReturnsClient()
	{
		$client = new Client();

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setUserCredentials('Username', 'User-Token'));
	}

	/**
	 * @test
	 */
	public function testGetUserCredentialReturnsValueFromSetUserCredentials()
	{
		$client = (new Client())->setUserCredentials('Username', 'User-Token');

		$this->assertSame('Username', $client->getUserCredential('username'));
		$this->assertSame('User-Token', $client->getUserCredential('token_secret'));
	}

	/**
	 * @test
	 */
	public function testGetUserCredentialWithWrongKeyThrowsException()
	{
		$client = (new Client())->setUserCredentials('Username', 'User-Token');

		$this->setExpectedException(
			'UnexpectedValueException',
			'"foobar" is not a valid key for user credentials.'
		);

		$foo = $client->getUserCredential('foobar');
	}

	/**
	 * @test
	 */
	public function testSetHttpClientReturnsClient()
	{
		$client = new Client();

		$stub = $this->createMock('Youthweb\Api\HttpClientInterface');

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setHttpClient($stub));
	}

	/**
	 * @test
	 */
	public function testGetCacheProviderReturnsPsrCacheItemPool()
	{
		$client = new Client();

		$this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $client->getCacheProvider());
	}

	/**
	 * @test
	 */
	public function testSetCacheProviderReturnsClient()
	{
		$client = new Client();

		$stub = $this->createMock('Psr\Cache\CacheItemPoolInterface');

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setCacheProvider($stub));
	}

	/**
	 * @test
	 */
	public function testBuildCacheKeyReturnsString()
	{
		$client = new Client();

		$this->assertSame('php_youthweb_api.foobar', $client->buildCacheKey('foobar'));
	}

	/**
	 * @test
	 * @dataProvider getResoursesClassesProvider
	 */
	public function testGetApiInstance($resource_name, $class_name)
	{
		$resource = $this->createMock($class_name);

		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');

		$resource_factory->expects($this->once())
			->method('createResource')
			->with($resource_name)
			->willReturn($resource);

		$client = new Client(
			[],
			['resource_factory' => $resource_factory]
		);

		$this->assertSame($resource, $client->getResource($resource_name));
	}

	/**
	 * Resources DataProvider
	 */
	public function getResoursesClassesProvider()
	{
		return array(
			array('auth', 'Youthweb\Api\Resource\AuthInterface'),
			array('stats', 'Youthweb\Api\Resource\StatsInterface'),
			array('users', 'Youthweb\Api\Resource\UsersInterface'),
		);
	}

	/**
	 * @test
	 */
	public function testGetUnknownResourceThrowsException()
	{
		$client = new Client();

		$this->setExpectedException(
			'InvalidArgumentException',
			'The resource "foobar" does not exists.'
		);

		$client->getResource('foobar');
	}

	/**
	 * @test
	 */
	public function testAuthorizeWithoutAccessTokenThrowException()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$oauth2_provider->expects($this->once())
			->method('getAuthorizationUrl')
			->willReturn('https://example.org/url_for_auth_code');

		$cache_item->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item],
				['php_youthweb_api.refresh_token', $cache_item],
			]));

		$client = new Client(
			[
				'client_id'     => 'client_id',
				'client_secret' => 'client_secret',
				'redirect_url'  => 'https://example.org/callback',
			],
			[
				'http_client' => $http_client,
				'cache_provider' => $cache_provider,
				'oauth2_provider' => $oauth2_provider,
			]
		);

		$this->setExpectedException(
			'Youthweb\Api\Exception\UnauthorizedException',
			'We need an authorization code. Call this url to get one.'
		);

		$client->authorize();
	}

	/**
	 * @test
	 */
	public function testAuthorizeWithAccessReturnsNothing()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_item->expects($this->any())
			->method('isHit')
			->willReturn(true);

		$cache_provider->expects($this->exactly(1))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item],
			]));

		$client = new Client(
			[
				'client_id'     => 'client_id',
				'client_secret' => 'client_secret',
				'redirect_url'  => 'https://example.org/callback',
			],
			[
				'http_client' => $http_client,
				'cache_provider' => $cache_provider,
			]
		);

		$this->assertNull($client->authorize());
	}

	/**
	 * @test
	 */
	public function testAuthorizeWithAuthCodeSavesToken()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');
		$cache_item_refresh = $this->createMock('Psr\Cache\CacheItemInterface');
		$access_token = $this->createMock('League\OAuth2\Client\Token\AccessToken');

		$access_token->expects($this->once())
			->method('getToken')
			->willReturn('access_token');
		$access_token->expects($this->once())
			->method('getRefreshToken')
			->willReturn('refresh_token');
		$access_token->expects($this->once())
			->method('getExpires')
			->willReturn(1234567890);

		$cache_item_access->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_access->expects($this->once())
			->method('set')
			->willReturn('access_token');

		$cache_item_refresh->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_refresh->expects($this->once())
			->method('set')
			->willReturn('refresh_token');

		$oauth2_provider->expects($this->once())
			->method('getAccessToken')
			->with('authorization_code', ['code' => 'auth_code'])
			->willReturn($access_token);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item_access],
				['php_youthweb_api.refresh_token', $cache_item_refresh],
			]));

		$client = new Client(
			[
				'client_id'     => 'client_id',
				'client_secret' => 'client_secret',
				'redirect_url'  => 'https://example.org/callback',
			],
			[
				'http_client' => $http_client,
				'cache_provider' => $cache_provider,
				'oauth2_provider' => $oauth2_provider,
			]
		);

		$client->authorize(['code' => 'auth_code']);
	}

	/**
	 * @test
	 */
	public function testGetUnauthorizedReturnsObject()
	{
		$body = $this->createMock('Psr\Http\Message\StreamInterface');

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"meta":{"this":"that"}}');

		$response = $this->createMock('GuzzleHttp\Psr7\Response');

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->willReturn($response);

		$client = new Client(
			[],
			[
				'http_client' => $http_client,
			]
		);

		$this->assertInstanceOf('\Art4\JsonApiClient\Document', $client->getUnauthorized('foobar'));
	}

	/**
	 * @test
	 */
	public function testParseResponseReturnsObject()
	{
		$body = $this->createMock('Psr\Http\Message\StreamInterface');

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"meta":{"this":"that"}}');

		$response = $this->createMock('GuzzleHttp\Psr7\Response');

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->willReturn($response);

		$auth_resource = $this->createMock('Youthweb\Api\Resource\AuthInterface');

		$auth_resource->expects($this->once())
			->method('getBearerToken')
			->willReturn('Bearer JWT');

		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');

		$resource_factory->expects($this->once())
			->method('createResource')
			->with('auth')
			->willReturn($auth_resource);

		$client = new Client(
			[],
			[
				'resource_factory' => $resource_factory,
				'http_client' => $http_client,
			]
		);

		$this->assertInstanceOf('\Art4\JsonApiClient\Document', $client->get('foobar'));
	}

	/**
	 * @test
	 */
	public function testGetRequestWithoutCredentialsThrowsException()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$client = new Client(
			[],
			['http_client' => $http_client]
		);

		$this->setExpectedException(
			'Youthweb\Api\Exception\MissingCredentialsException',
			''
		);

		$client->get('foobar');
	}

	/**
	 * @test
	 */
	public function testHandleClientExceptionWithResponseException()
	{
		$body = $this->createMock('Psr\Http\Message\StreamInterface');

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"errors":[{"status":"401","title":"Unauthorized"}]}');

		$response = $this->createMock('Psr\Http\Message\ResponseInterface');

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$exception = $this->createMock('GuzzleHttp\Exception\ClientException');

		$exception->expects($this->once())
			->method('getResponse')
			->willReturn($response);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->will($this->throwException($exception));

		$client = new Client(
			[],
			['http_client' => $http_client]
		);

		$client->setUserCredentials('username', 'secret');

		$this->setExpectedException(
			'Exception',
			'Unauthorized'
		);

		$client->get('foobar');
	}

	/**
	 * @test
	 */
	public function testHandleClientExceptionWithDetailResponseException()
	{
		$body = $this->createMock('Psr\Http\Message\StreamInterface');

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"errors":[{"status":"401","title":"Unauthorized","detail":"Detailed error message"}]}');

		$response = $this->createMock('Psr\Http\Message\ResponseInterface');

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$exception = $this->createMock('GuzzleHttp\Exception\ClientException');

		$exception->expects($this->once())
			->method('getResponse')
			->willReturn($response);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->will($this->throwException($exception));

		$client = new Client(
			[],
			['http_client' => $http_client]
		);

		$client->setUserCredentials('username', 'secret');

		$this->setExpectedException(
			'Exception',
			'Detailed error message'
		);

		$client->get('foobar');
	}

	/**
	 * @test
	 */
	public function testHandleClientExceptionWithException()
	{
		$exception = $this->createMock('\Exception');

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->will($this->throwException($exception));

		$client = new Client(
			[],
			['http_client' => $http_client]
		);

		$this->setExpectedException(
			'Exception',
			'The server responses with an unknown error.'
		);

		$client->getUnauthorized('foobar');
	}
}
