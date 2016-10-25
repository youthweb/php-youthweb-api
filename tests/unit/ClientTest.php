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
