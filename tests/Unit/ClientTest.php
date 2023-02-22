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
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Youthweb\Api\Authentication\Authenticator;
use Youthweb\Api\Client;
use Youthweb\Api\Resource\UsersInterface;
use Youthweb\Api\ResourceFactoryInterface;

class ClientTest extends TestCase
{
    /**
     * Create a client with mocks of all collaborators
     */
    private function createClient(array $options = [], array $collaborators = [])
    {
        $default_options = [];

        $options = array_merge($default_options, $options);

        $default_collaborators = [
            'http_client' => $this->createMock(HttpClientInterface::class),
            'oauth2_provider' => $this->createMock(Authenticator::class),
            'cache_provider' => $this->createMock(CacheItemPoolInterface::class),
            'request_factory' => $this->createMock(RequestFactoryInterface::class),
            'resource_factory' => $this->createMock(ResourceFactoryInterface::class),
        ];

        $collaborators = array_merge($default_collaborators, $collaborators);

        return new Client($options, $collaborators);
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
                ['php_youthweb_api.state', $cache_item_state],
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
                ['php_youthweb_api.state', $cache_item_state],
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
    public function testGetResource(): void
    {
        $resource = $this->createMock(UsersInterface::class);

        $resource_factory = $this->createMock(ResourceFactoryInterface::class);
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
    public function testGetUnknownResourceThrowsException(): void
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
    public function testAuthorizeWithoutCodeThrowsException(): void
    {
        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
                'scope'         => 'user:email',
            ],
            []
        );

        $this->expectException('Youthweb\Api\Exception\UnauthorizedException');
        $this->expectExceptionMessage('');

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

        $cache_item_state = $this->createMock(CacheItemInterface::class);
        $cache_item_state->expects($this->any())
            ->method('isHit')
            ->willReturn(false);

        $cache_item_state->method('getKey')
            ->willReturn('');

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->any())
            ->method('isHit')
            ->willReturn(false);

        $cache_item_access->expects($this->once())
            ->method('set')
            ->willReturn('access_token');

        $oauth2Provider = $this->createMock(Authenticator::class);
        $oauth2Provider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'auth_code'])
            ->willReturn($access_token);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.access_token', $cache_item_access],
                ['php_youthweb_api.state', $cache_item_state],
            ]));

        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'http_client' => $httpClient,
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
            ->method('set')
            ->willReturn('access_token');

        $oauth2Provider = $this->createMock(Authenticator::class);
        $oauth2Provider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'auth_code'])
            ->willReturn($access_token);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.access_token', $cache_item_access],
                ['php_youthweb_api.state', $cache_item_state],
            ]));

        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'http_client' => $httpClient,
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
    public function testAuthorizeWithAuthCodeAndWrongStateThrowsException(): void
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
                ['php_youthweb_api.state', $cache_item_state],
            ]));

        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'http_client' => $httpClient,
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
                ['php_youthweb_api.access_token', $cache_item_state],
            ]));

        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'http_client' => $httpClient,
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
            ->willReturn('');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.access_token', $cache_item_state],
            ]));

        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = $this->createClient(
            [
                'client_id'     => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url'  => 'https://example.org/callback',
            ],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
            ]
        );

        $this->assertFalse($client->isAuthorized());
    }

    /**
     * @test
     */
    public function testAuthorizedGetRequestReturnsObject(): void
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
                ['php_youthweb_api.access_token', $cache_item_access],
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
    public function testGetUnauthorizedReturnsObject(): void
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
    public function testPostUnauthorizedReturnsObject(): void
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
    public function testGetRequestWithoutCredentialsThrowsException(): void
    {
        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $cache_item_access->method('getKey')
            ->willReturn('');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(1))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.access_token', $cache_item_access],
            ]));

        $httpClient = $this->createMock(HttpClientInterface::class);

        $resource_factory = $this->createMock(ResourceFactoryInterface::class);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'cache_provider' => $cache_provider,
                'resource_factory' => $resource_factory,
            ]
        );

        $this->expectException('Youthweb\Api\Exception\UnauthorizedException');
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testHandleClientExceptionWithResponseException(): void
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
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.access_token', $cache_item_access],
            ]));

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":[{"status":"401","title":"Unauthorized"}]}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(401);

        $exception = new \GuzzleHttp\Exception\ClientException('The server responses with an unknown error.', $request, $response);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->will($this->throwException($exception));

        $resource_factory = $this->createMock(ResourceFactoryInterface::class);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'resource_factory' => $resource_factory,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException('Exception');
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testHandleClientExceptionWithDetailResponseException(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn('{"errors":[{"status":"401","title":"Unauthorized","detail":"Detailed error message"}]}');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(401);

        $cache_item_access = $this->createMock(CacheItemInterface::class);
        $cache_item_access->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cache_item_access->method('getKey')
            ->willReturn('');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
        $cache_provider->expects($this->exactly(2))
            ->method('getItem')
            ->will($this->returnValueMap([
                ['php_youthweb_api.access_token', $cache_item_access],
            ]));

        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $exception = new \GuzzleHttp\Exception\ClientException('error message', $request, $response);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->will($this->throwException($exception));

        $resource_factory = $this->createMock(ResourceFactoryInterface::class);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'resource_factory' => $resource_factory,
                'cache_provider' => $cache_provider,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException('Exception');
        $this->expectExceptionMessage('Detailed error message');
        $this->expectExceptionCode(401);

        $client->get('foobar');
    }

    /**
     * @test
     */
    public function testHandleClientExceptionWithException(): void
    {
        $exception = new Exception('error message', 0);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->will($this->throwException($exception));

        $request = $this->createMock(RequestInterface::class);
        $request->method('withAddedHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $client = $this->createClient(
            [],
            [
                'http_client' => $httpClient,
                'request_factory' => $requestFactory,
            ]
        );

        $this->expectException('Exception');
        $this->expectExceptionMessage('The server responses with an unknown error.');
        $this->expectExceptionCode(0);

        $client->getUnauthorized('foobar');
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
    public function testSaveCacheItem(): void
    {
        $cache_item = $this->createMock(CacheItemInterface::class);

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
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
    public function testDeleteCacheItem(): void
    {
        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->expects($this->exactly(1))
            ->method('getKey')
            ->willReturn('php_youthweb_api.test_item');

        $cache_provider = $this->createMock(CacheItemPoolInterface::class);
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
