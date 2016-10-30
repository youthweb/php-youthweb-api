<?php

namespace Youthweb\Api\Tests\Unit;

use Youthweb\Api\Client;
use InvalidArgumentException;

class ClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Create a client with mocks of all collaborators
	 */
	private function createClient(array $options = [], array $collaborators = [])
	{
		$default_options = [];

		$options = array_merge($default_options, $options);

		$default_collaborators = [
			'http_client' => $this->createMock('Youthweb\Api\HttpClientInterface'),
			'oauth2_provider' => $this->createMock('League\OAuth2\Client\Provider\AbstractProvider'),
			'cache_provider' => $this->createMock('Psr\Cache\CacheItemPoolInterface'),
			'request_factory' => $this->createMock('Youthweb\Api\RequestFactoryInterface'),
			'resource_factory' => $this->createMock('Youthweb\Api\ResourceFactoryInterface'),
		];

		$collaborators = array_merge($default_collaborators, $collaborators);

		return new Client($options, $collaborators);
	}

	/**
	 * @test
	 */
	public function testSetOptionsThroughConstructor()
	{
		$url = 'http://test.local';

		$client = $this->createClient([
			'api_domain' => $url,
		]);

		$this->assertSame($url, $client->getUrl());
	}

	/**
	 * @test
	 */
	public function testSetUrlReturnsClient()
	{
		$client = $this->createClient();

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setUrl('http://test.local'));
	}

	/**
	 * @test
	 */
	public function testGetUrlReturnsValueFromSetUrl()
	{
		$client = $this->createClient();
		$client->setUrl('http://test.local');

		$this->assertSame('http://test.local', $client->getUrl());
	}

	/**
	 * @test
	 */
	public function testSetUserCredentialsReturnsClient()
	{
		$client = $this->createClient();

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setUserCredentials('Username', 'User-Token'));
	}

	/**
	 * @test
	 */
	public function testGetUserCredentialReturnsValueFromSetUserCredentials()
	{
		$client = $this->createClient();
		$client->setUserCredentials('Username', 'User-Token');

		$this->assertSame('Username', $client->getUserCredential('username'));
		$this->assertSame('User-Token', $client->getUserCredential('token_secret'));
	}

	/**
	 * @test
	 */
	public function testGetUserCredentialWithWrongKeyThrowsException()
	{
		$client = $this->createClient();
		$client->setUserCredentials('Username', 'User-Token');

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
		$client = $this->createClient();

		$stub = $this->createMock('Youthweb\Api\HttpClientInterface');

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setHttpClient($stub));
	}

	/**
	 * @test
	 */
	public function testGetCacheProviderReturnsPsrCacheItemPool()
	{
		$client = $this->createClient();

		$this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $client->getCacheProvider());
	}

	/**
	 * @test
	 */
	public function testSetCacheProviderReturnsClient()
	{
		$client = $this->createClient();

		$stub = $this->createMock('Psr\Cache\CacheItemPoolInterface');

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setCacheProvider($stub));
	}

	/**
	 * @test
	 */
	public function testBuildCacheKeyReturnsString()
	{
		$client = $this->createClient();

		$this->assertSame('php_youthweb_api.foobar', $client->buildCacheKey('foobar'));
	}

	/**
	 * @test
	 */
	public function testGetResource()
	{
		$resource = $this->createMock('Youthweb\Api\Resource\UsersInterface');

		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');

		$resource_factory->expects($this->once())
			->method('createResource')
			->with('users')
			->willReturn($resource);

		$client = $this->createClient(
			[],
			['resource_factory' => $resource_factory]
		);

		$this->assertSame($resource, $client->getResource('users'));

		// test that the client caches the resources
		$this->assertSame($resource, $client->getResource('users'));
	}

	/**
	 * @test
	 */
	public function testGetUnknownResourceThrowsException()
	{
		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');

		$resource_factory->expects($this->once())
			->method('createResource')
			->with('foobar')
			->will($this->throwException(new \InvalidArgumentException('The resource "foobar" does not exists.')));

		$client = $this->createClient(
			[],
			[
				'resource_factory' => $resource_factory,
			]
		);

		$this->setExpectedException(
			'InvalidArgumentException',
			'The resource "foobar" does not exists.'
		);

		$client->getResource('foobar');
	}

	/**
	 * @test
	 */
	public function testAuthorizeWithoutAccessTokenThrowsException()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$oauth2_provider->expects($this->once())
			->method('getAuthorizationUrl')
			->with(['scope' => ['user:email']])
			->willReturn('https://example.org/url_for_auth_code');

		$cache_item->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_provider->expects($this->exactly(1))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.state', $cache_item],
			]));

		$client = $this->createClient(
			[
				'client_id'     => 'client_id',
				'client_secret' => 'client_secret',
				'redirect_url'  => 'https://example.org/callback',
				'scope'         => 'user:email',
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
	public function testAuthorizeWithAccessThrowsException()
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
				['php_youthweb_api.state', $cache_item],
				['php_youthweb_api.access_token', $cache_item],
			]));

		$client = $this->createClient(
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

		$this->setExpectedException(
			'Youthweb\Api\Exception\UnauthorizedException',
			'We need an authorization code. Call this url to get one.'
		);

		$this->assertNull($client->authorize());
	}

	public function testAuthorizeWithRefeshTokenThrowsException()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item_state = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_provider->expects($this->exactly(1))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.state', $cache_item_state],
			]));

		$client = $this->createClient(
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
		$cache_item_state = $this->createMock('Psr\Cache\CacheItemInterface');
		$access_token = $this->createMock('League\OAuth2\Client\Token\AccessToken');

		$access_token->expects($this->once())
			->method('getToken')
			->willReturn('access_token');
		$access_token->expects($this->once())
			->method('getExpires')
			->willReturn(1234567890);

		$cache_item_state->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_access->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_access->expects($this->once())
			->method('set')
			->willReturn('access_token');

		$oauth2_provider->expects($this->once())
			->method('getAccessToken')
			->with('authorization_code', ['code' => 'auth_code'])
			->willReturn($access_token);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item_access],
				['php_youthweb_api.state', $cache_item_state],
			]));

		$client = $this->createClient(
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
	public function testAuthorizeWithAuthCodeAndStateSavesToken()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');
		$cache_item_state = $this->createMock('Psr\Cache\CacheItemInterface');
		$access_token = $this->createMock('League\OAuth2\Client\Token\AccessToken');

		$access_token->expects($this->once())
			->method('getToken')
			->willReturn('access_token');
		$access_token->expects($this->once())
			->method('getExpires')
			->willReturn(1234567890);

		$cache_item_state->expects($this->any())
			->method('isHit')
			->willReturn(true);

		$cache_item_state->expects($this->once())
			->method('get')
			->willReturn('random_string');

		$cache_item_access->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_access->expects($this->once())
			->method('set')
			->willReturn('access_token');

		$oauth2_provider->expects($this->once())
			->method('getAccessToken')
			->with('authorization_code', ['code' => 'auth_code'])
			->willReturn($access_token);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item_access],
				['php_youthweb_api.state', $cache_item_state],
			]));

		$client = $this->createClient(
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

		$client->authorize(['code' => 'auth_code', 'state' => 'random_string']);
	}

	/**
	 * @test
	 */
	public function testAuthorizeWithAuthCodeAndWrongStateThrowsException()
	{
		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item_state = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_item_state->expects($this->any())
			->method('isHit')
			->willReturn(true);

		$cache_item_state->expects($this->once())
			->method('get')
			->willReturn('random_string');

		$cache_provider->expects($this->exactly(1))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.state', $cache_item_state],
			]));

		$client = $this->createClient(
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
			'InvalidArgumentException',
			'Invalid state'
		);

		$client->authorize(['code' => 'auth_code', 'state' => 'wrong_state']);
	}

	/**
	 * @test
	 */
	public function testAuthorizedGetRequestReturnsObject()
	{
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$oauth2_provider = $this->createMock('League\OAuth2\Client\Provider\AbstractProvider');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');
		$body = $this->createMock('Psr\Http\Message\StreamInterface');
		$request_factory = $this->createMock('Youthweb\Api\RequestFactoryInterface');

		$cache_item_access->expects($this->once())
			->method('isHit')
			->willReturn(true);

		$cache_item_access->expects($this->once())
			->method('get')
			->willReturn('access_token');

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item_access],
			]));

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"meta":{"this":"that"}}');

		$request = $this->createMock('Psr\Http\Message\RequestInterface');

		$request_factory->expects($this->once())
			->method('createRequest')
			->willReturn($request);

		$response = $this->createMock('Psr\Http\Message\ResponseInterface');

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->with($request)
			->willReturn($response);

		$client = $this->createClient(
			[
				'client_id'     => 'client_id',
				'client_secret' => 'client_secret',
				'redirect_url'  => 'https://example.org/callback',
			],
			[
				'http_client' => $http_client,
				'cache_provider' => $cache_provider,
				'oauth2_provider' => $oauth2_provider,
				'request_factory' => $request_factory,
			]
		);

		$this->assertInstanceOf('\Art4\JsonApiClient\Document', $client->get('foobar'));
	}

	/**
	 * @test
	 */
	public function testGetUnauthorizedReturnsObject()
	{
		$request_factory = $this->createMock('Youthweb\Api\RequestFactoryInterface');

		$request = $this->createMock('Psr\Http\Message\RequestInterface');

		$request_factory->expects($this->once())
			->method('createRequest')
			->willReturn($request);

		$body = $this->createMock('Psr\Http\Message\StreamInterface');

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"meta":{"this":"that"}}');

		$response = $this->createMock('Psr\Http\Message\ResponseInterface');

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->with($request)
			->willReturn($response);

		$client = $this->createClient(
			[],
			[
				'http_client' => $http_client,
				'request_factory' => $request_factory,
			]
		);

		$this->assertInstanceOf('\Art4\JsonApiClient\Document', $client->getUnauthorized('foobar'));
	}

	/**
	 * @test
	 */
	public function testGetWithUserTokenReturnsObject()
	{
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item_state = $this->createMock('Psr\Cache\CacheItemInterface');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_item_state->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_access->expects($this->once())
			->method('isHit')
			->willReturn(false);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.state', $cache_item_state],
				['php_youthweb_api.access_token', $cache_item_access],
			]));

		$request_factory = $this->createMock('Youthweb\Api\RequestFactoryInterface');

		$request = $this->createMock('Psr\Http\Message\RequestInterface');

		$request_factory->expects($this->once())
			->method('createRequest')
			->willReturn($request);

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
			->with($request)
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

		$client = $this->createClient(
			[],
			[
				'resource_factory' => $resource_factory,
				'http_client' => $http_client,
				'cache_provider' => $cache_provider,
				'request_factory' => $request_factory,
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
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');
		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');
		$auth_resource = $this->createMock('Youthweb\Api\Resource\AuthInterface');

		$auth_resource->expects($this->once())
			->method('getBearerToken')
			->will($this->throwException(new \Youthweb\Api\Exception\MissingCredentialsException));

		$resource_factory->expects($this->once())
			->method('createResource')
			->with('auth')
			->willReturn($auth_resource);

		$cache_item_access->expects($this->once())
			->method('isHit')
			->willReturn(false);

		$cache_provider->expects($this->exactly(1))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item_access],
			]));

		$client = $this->createClient(
			[],
			[
				'http_client' => $http_client,
				'cache_provider' => $cache_provider,
				'resource_factory' => $resource_factory,
			]
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
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item_state = $this->createMock('Psr\Cache\CacheItemInterface');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');
		$body = $this->createMock('Psr\Http\Message\StreamInterface');
		$request_factory = $this->createMock('Youthweb\Api\RequestFactoryInterface');
		$request = $this->createMock('Psr\Http\Message\RequestInterface');

		$request_factory->expects($this->once())
			->method('createRequest')
			->willReturn($request);

		$cache_item_state->expects($this->any())
			->method('isHit')
			->willReturn(false);

		$cache_item_access->expects($this->once())
			->method('isHit')
			->willReturn(false);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.state', $cache_item_state],
				['php_youthweb_api.access_token', $cache_item_access],
			]));

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

		$auth_resource = $this->createMock('Youthweb\Api\Resource\AuthInterface');

		$auth_resource->expects($this->once())
			->method('getBearerToken')
			->willReturn('Bearer JWT');

		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');

		$resource_factory->expects($this->once())
			->method('createResource')
			->with('auth')
			->willReturn($auth_resource);

		$client = $this->createClient(
			[],
			[
				'http_client' => $http_client,
				'resource_factory' => $resource_factory,
				'cache_provider' => $cache_provider,
				'request_factory' => $request_factory,
			]
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

		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item_access = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_item_access->expects($this->once())
			->method('isHit')
			->willReturn(false);

		$cache_provider->expects($this->exactly(2))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.access_token', $cache_item_access],
			]));

		$request_factory = $this->createMock('Youthweb\Api\RequestFactoryInterface');
		$request = $this->createMock('Psr\Http\Message\RequestInterface');

		$request_factory->expects($this->once())
			->method('createRequest')
			->willReturn($request);

		$http_client = $this->createMock('Youthweb\Api\HttpClientInterface');

		$http_client->expects($this->once())
			->method('send')
			->will($this->throwException($exception));

		$auth_resource = $this->createMock('Youthweb\Api\Resource\AuthInterface');

		$auth_resource->expects($this->once())
			->method('getBearerToken')
			->willReturn('Bearer JWT');

		$resource_factory = $this->createMock('Youthweb\Api\ResourceFactoryInterface');

		$resource_factory->expects($this->once())
			->method('createResource')
			->with('auth')
			->willReturn($auth_resource);

		$client = $this->createClient(
			[],
			[
				'http_client' => $http_client,
				'resource_factory' => $resource_factory,
				'cache_provider' => $cache_provider,
				'request_factory' => $request_factory,
			]
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

		$request_factory = $this->createMock('Youthweb\Api\RequestFactoryInterface');
		$request = $this->createMock('Psr\Http\Message\RequestInterface');

		$request_factory->expects($this->once())
			->method('createRequest')
			->willReturn($request);

		$client = $this->createClient(
			[],
			[
				'http_client' => $http_client,
				'request_factory' => $request_factory,
			]
		);

		$this->setExpectedException(
			'Exception',
			'The server responses with an unknown error.'
		);

		$client->getUnauthorized('foobar');
	}

	/**
	 * @test
	 */
	public function testGetCacheItemReturnsCacheItem()
	{
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_provider->expects($this->exactly(1))
			->method('getItem')
			->will($this->returnValueMap([
				['php_youthweb_api.test_item', $cache_item],
			]));

		$client = $this->createClient(
			[],
			[
				'cache_provider' => $cache_provider,
			]
		);

		$this->assertSame($cache_item, $client->getCacheItem('test_item'));
	}

	/**
	 * @test
	 */
	public function testSaveCacheItem()
	{
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_provider->expects($this->exactly(1))
			->method('saveDeferred')
			->with($cache_item);

		$cache_provider->expects($this->exactly(2))
			->method('commit');

		$client = $this->createClient(
			[],
			[
				'cache_provider' => $cache_provider,
			]
		);

		$client->saveCacheItem($cache_item);
	}

	/**
	 * @test
	 */
	public function testDeleteCacheItem()
	{
		$cache_provider = $this->createMock('Psr\Cache\CacheItemPoolInterface');
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_item->expects($this->exactly(1))
			->method('getKey')
			->willReturn('php_youthweb_api.test_item');

		$cache_provider->expects($this->exactly(1))
			->method('deleteItem')
			->with('php_youthweb_api.test_item');

		$client = $this->createClient(
			[],
			[
				'cache_provider' => $cache_provider,
			]
		);

		$client->deleteCacheItem($cache_item);
	}
}
