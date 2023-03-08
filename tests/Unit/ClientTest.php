<?php

declare(strict_types=1);
/*
 * PHP Youthweb API is an object-oriented wrapper for PHP of the Youthweb API.
 * Copyright (C) 2015-2019  Youthweb e.V.  https://youthweb.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Youthweb\Api\Tests\Unit;

use Art4\JsonApiClient\Accessable;
use Exception;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Youthweb\Api\Authentication\Authenticator;
use Youthweb\Api\Client;
use Youthweb\Api\Configuration;
use Youthweb\Api\Exception\ErrorResponseException;
use Youthweb\Api\Exception\UnauthorizedException;
use Youthweb\Api\Resource\UsersInterface;
use Youthweb\Api\ResourceFactoryInterface;

class ClientTest extends TestCase
{
    /**
     * Create a client with mocks of all collaborators
     */
    private function createClient(array $options = [], array $collaborators = []): Client
    {
        $options = array_merge(
            [
                'client_id'     => '',
                'client_secret' => '',
                'redirect_url'  => '',
                'scope'         => [],
            ],
            $options,
        );

        $collaborators = array_merge(
            [
                'http_client' => $this->createMock(HttpClientInterface::class),
                'oauth2_provider' => $this->createMock(Authenticator::class),
                'cache_provider' => $this->createMock(CacheItemPoolInterface::class),
                'request_factory' => $this->createMock(RequestFactoryInterface::class),
                'resource_factory' => $this->createMock(ResourceFactoryInterface::class),
            ],
            $collaborators,
        );

        $config = Configuration::create(
            $options['client_id'],
            $options['client_secret'],
            $options['redirect_url'],
            $options['scope'],
            'test-user',
        );
        $config->setHttpClient($collaborators['http_client']);
        $config->setAuthenticator($collaborators['oauth2_provider']);
        $config->setCacheItemPool($collaborators['cache_provider']);
        $config->setRequestFactory($collaborators['request_factory']);
        $config->setResourceFactory($collaborators['resource_factory']);

        return Client::fromConfig($config);
    }

    /**
     * @test
     */
    public function testGetAuthorizationUrlReturnsUrl(): void
    {
        $url = 'https://example.org';

        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->exactly(1))
            ->method('isHit')
            ->willReturn(false);

        $cache_item_state->expects($this->exactly(1))
            ->method('get')
            ->willReturn('random_string');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $oauth2Provider = $this->createMock(Authenticator::class);
        $oauth2Provider->expects($this->once())
            ->method('getState')
            ->willReturn('random_string');

        $oauth2Provider->expects($this->once())
            ->method('getAuthorizationUrl')
            ->willReturn($url);

        $client = $this->createClient([], [
            'oauth2_provider' => $oauth2Provider,
            'cache_provider' => $cache_provider,
        ]);

        $this->assertSame($url, $client->getAuthorizationUrl());
    }

    /**
     * @test
     */
    public function testGetStateReturnsState(): void
    {
        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->exactly(1))
            ->method('isHit')
            ->willReturn(false);

        $state = 'random_string';

        $cache_item_state->expects($this->exactly(1))
            ->method('get')
            ->willReturn($state);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $oauth2Provider = $this->createMock(Authenticator::class);
        $oauth2Provider->expects($this->once())
            ->method('getState')
            ->willReturn($state);

        $client = $this->createClient([], [
            'oauth2_provider' => $oauth2Provider,
            'cache_provider' => $cache_provider,
        ]);

        $this->assertSame($state, $client->getState());
    }

    /**
     * @test
     */
    public function testGetResourceReturnsResource(): void
    {
        $resource = $this->createMock(UsersInterface::class);

        $resource_factory = $this->createMock(ResourceFactoryInterface::class);
        $resource_factory->expects($this->once())
            ->method('createResource')
            ->with('users')
            ->willReturn($resource);

        $client = $this->createClient(
            [],
            [
                'resource_factory' => $resource_factory,
            ]
        );

        $this->assertSame($resource, $client->getResource('users'));

        // test that the client caches the resources
        $this->assertSame($resource, $client->getResource('users'));
    }

    /**
     * @test
     */
    public function testGetUnknownResourceThrowsInvalidArgumentException(): void
    {
        $resource_factory = $this->createMock(ResourceFactoryInterface::class);
        $resource_factory->expects($this->once())
            ->method('createResource')
            ->with('foobar')
            ->will($this->throwException(new InvalidArgumentException('The resource "foobar" does not exists.')));

        $client = $this->createClient(
            [],
            [
                'resource_factory' => $resource_factory,
            ]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The resource "foobar" does not exists.');

        $client->getResource('foobar');
    }

    /**
     * @test
     */
    public function testAuthorizeWithoutCodeThrowsInvalidArgumentException(): void
    {
        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
                'scope'         => ['user:email'],
            ],
            []
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument #2 "$param" must have a "code" value.');

        $client->authorize('authorization_code');
    }

    /**
     * @test
     */
    public function testAuthorizeWithAuthCodeSavesToken(): void
    {
        $access_token = $this->createMock(AccessTokenInterface::class);
        $access_token->expects($this->once())
            ->method('getToken')
            ->willReturn('access_token');
        $access_token->expects($this->once())
            ->method('getExpires')
            ->willReturn(1234567890);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->never())
            ->method('isHit')
            ->willReturn(false);

        $cache_item_access->expects($this->once())
            ->method('set');

        $oauth2Provider = $this->createMock(Authenticator::class);
        $oauth2Provider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'auth_code'])
            ->willReturn($access_token);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'cache_provider' => $cache_provider,
                'oauth2_provider' => $oauth2Provider,
            ]
        );

        $client->authorize('authorization_code', ['code' => 'auth_code']);
    }

    /**
     * @test
     */
    public function testAuthorizeWithAuthCodeAndStateSavesToken(): void
    {
        $access_token = $this->createMock(AccessTokenInterface::class);
        $access_token->expects($this->once())
            ->method('getToken')
            ->willReturn('access_token');
        $access_token->expects($this->once())
            ->method('getExpires')
            ->willReturn(1234567890);

        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->any())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_state->expects($this->once())
            ->method('get')
            ->willReturn('random_string');

        $cache_item_state->method('getKey')
            ->willReturn('');

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->any())
            ->method('isHit')
            ->willReturn(false);

        $cache_item_access->expects($this->once())
            ->method('set');

        $oauth2Provider = $this->createMock(Authenticator::class);
        $oauth2Provider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'auth_code'])
            ->willReturn($access_token);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'cache_provider' => $cache_provider,
                'oauth2_provider' => $oauth2Provider,
            ]
        );

        $client->authorize('authorization_code', [
            'code' => 'auth_code',
            'state' => 'random_string',
        ]);
    }

    /**
     * @test
     */
    public function testAuthorizeWithAuthCodeAndWrongStateThrowsInvalidArgumentException(): void
    {
        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->any())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_state->method('getKey')
            ->willReturn('');

        $cache_item_state->expects($this->once())
            ->method('get')
            ->willReturn('random_string');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'cache_provider' => $cache_provider,
            ]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid state');

        $client->authorize('authorization_code', [
            'code' => 'auth_code',
            'state' => 'wrong_state',
        ]);
    }

    /**
     * @test
     */
    public function testIsAuthorizeReturnsTrue(): void
    {
        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->any())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_state->expects($this->once())
            ->method('get')
            ->willReturn('random_string');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_state],
            ]));

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'cache_provider' => $cache_provider,
            ]
        );

        $this->assertTrue($client->isAuthorized());
    }

    /**
     * @test
     */
    public function testIsAuthorizeReturnsFalse(): void
    {
        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->any())
            ->method('isHit')
            ->willReturn(false);
        $cache_item_state->method('getKey')
            ->willReturn('php_youthweb_api.test-user.state');

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->any())
            ->method('isHit')
            ->willReturn(false);
        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'cache_provider' => $cache_provider,
            ]
        );

        $this->assertFalse($client->isAuthorized());
    }

    /**
     * @test
     */
    public function testAuthorizedGetRequestReturnsAccessable(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->expects($this->once())
            ->method('get')
            ->willReturn('access_token');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->once())
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"meta":{"this":"that"}}');

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->assertInstanceOf(Accessable::class, $client->get('foobar'));
    }

    /**
     * @test
     */
    public function testGetUnauthorizedReturnsAccessable(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"meta":{"this":"that"}}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'request_factory' => $requestFactory,
            ]
        );

        $this->assertInstanceOf(Accessable::class, $client->getUnauthorized('foobar'));
    }

    /**
     * @test
     */
    public function testPostUnauthorizedReturnsAccessable(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"meta":{"this":"that"}}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'request_factory' => $requestFactory,
            ]
        );

        $this->assertInstanceOf(Accessable::class, $client->postUnauthorized('foobar', ['body' => '{}']));
    }

    /**
     * @test
     */
    public function testGetRequestWithoutCredentialsThrowsUnauthorizedException(): void
    {
        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cache_item_state->method('getKey')
            ->willReturn('php_youthweb_api.test-user.state');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $client = $this->createClient(
            [],
            [
                'cache_provider' => $cache_provider,
            ]
        );

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testGetRequestWithClientExceptionThrowsClientException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->method('getKey')
            ->willReturn('');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        // We cannot create a mock for ClientExceptionInterface (or \Throwable)
        // @link https://github.com/sebastianbergmann/phpunit/issues/4458
        $exception = new class () extends Exception implements ClientExceptionInterface {};

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->will($this->throwException($exception));

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectExceptionObject($exception);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testServerErrorThrowsErrorResponseException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":[{"title":"Server error","detail":"Some more details about the server error."}]}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $response->expects($this->exactly(3))
            ->method('getStatusCode')
            ->willReturn(500);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('Some more details about the server error.');
        $this->expectExceptionCode(500);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testServerErrorWithoutDetailThrowsErrorResponseException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":[{"title":"Server error"}]}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $response->expects($this->exactly(3))
            ->method('getStatusCode')
            ->willReturn(500);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('Server error');
        $this->expectExceptionCode(500);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testServerErrorWithUnknownErrorThrowsErrorResponseException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":[]}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(500);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('The server responses with an unknown error.');
        $this->expectExceptionCode(500);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testServerErrorWithoutJsonApiThrowsErrorResponseException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('Server Error!');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(500);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('The server responses with an unknown error.');
        $this->expectExceptionCode(500);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testUnauthorizedServerErrorThrowsUnauthorizedException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cache_item_access->method('getKey')
            ->willReturn('php_youthweb_api.test-user.access_token');

        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cache_item_state->method('getKey')
            ->willReturn('php_youthweb_api.test-user.state');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(3))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.access_token', $cache_item_access],
                ['php_youthweb_api.test-user.state', $cache_item_state],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":[{"title":"Unauthorized"}]}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(401);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testGetCacheItemReturnsCacheItem(): void
    {
        $cache_item = $this->createMock(CacheItemInterface::class);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.test-user.test_item', $cache_item],
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
    public function testSaveCacheItem(): void
    {
        $cache_item = $this->createMock(CacheItemInterface::class);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('save')
            ->with($cache_item);

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
    public function testDeleteCacheItem(): void
    {
        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->expects($this->exactly(1))
            ->method('getKey')
            ->willReturn('php_youthweb_api.test-user.test_item');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('deleteItem')
            ->with('php_youthweb_api.test-user.test_item');

        $client = $this->createClient(
            [],
            [
                'cache_provider' => $cache_provider,
            ]
        );

        $client->deleteCacheItem($cache_item);
    }
}
