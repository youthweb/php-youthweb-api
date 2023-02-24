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

namespace Youthweb\Api;

use Art4\JsonApiClient\Accessable;
use Art4\JsonApiClient\Helper\Parser as JsonApiParser;
use DateInterval;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Throwable;
use Youthweb\Api\Authentication\Authenticator;
use Youthweb\Api\Authentication\NativeAuthenticator;
use Youthweb\Api\Cache\NullCacheItemPool;
use Youthweb\Api\Exception\ErrorResponseException;
use Youthweb\Api\Exception\UnauthorizedException;
use Youthweb\Api\Resource\ResourceInterface;
use Youthweb\OAuth2\Client\Provider\Youthweb as Oauth2Provider;

/**
 * Simple PHP Youthweb client
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
final class Client implements ClientInterface
{
    /**
     * @deprecated
     */
    public const CACHEKEY_ACCESS_TOKEN = 'access_token';

    /**
     * @var string
     */
    private $api_version = '0.18';

    /**
     * @var string
     */
    private $api_domain = 'https://api.youthweb.net';

    /**
     * @var string
     */
    private $auth_domain = 'https://youthweb.net';

    /**
     * @var array
     */
    private $scope = [];

    private HttpClientInterface $httpClient;

    private Authenticator $oauth2Provider;

    private CacheItemPoolInterface $cacheProvider;

    /**
     * @var string
     */
    private $cache_namespace = 'php_youthweb_api.';

    /**
     * @var array
     */
    private $resources = [];

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private UriFactoryInterface $uriFactory;

    /**
     * @var ResourceFactoryInterface
     */
    private $resource_factory;

    /**
     * @var string
     *
     * @since Youthweb-API 0.6
     */
    private $client_id;

    /**
     * @var string
     *
     * @since Youthweb-API 0.6
     */
    private $client_secret;

    /**
     * @var string
     *
     * @since Youthweb-API 0.6
     */
    private $redirect_url = '';

    /**
     * Constructs the Client.
     *
     * @param array $options       an array of options to set on the client.
     *                             Options include `api_version`, `api_domain`, `auth_domain`,
     *                             `cache_namespace`, `client_id`, `client_secret`, `redirect_url` and `scope`
     * @param array $collaborators An array of collaborators that may be used to
     *                             override this provider's default behavior. Collaborators include
     *                             http_client`, `oauth2_provider`, `cache_provider`, `request_factory`
     *                             and `resource_factory`.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $allowed_options = [
            'api_version',
            'api_domain',
            'auth_domain',
            'cache_namespace',
            'client_id',
            'client_secret',
            'redirect_url',
            'scope',
        ];

        foreach ($options as $option => $value) {
            if (in_array($option, $allowed_options)) {
                if ($option !== 'scope') {
                    $value = strval($value);
                }
                // scope must be an array
                elseif (! is_array($value)) {
                    $value = [strval($value)];
                }

                $this->{$option} = $value;
            }
        }

        if (empty($collaborators['http_client'])) {
            $collaborators['http_client'] = new GuzzleHttpClient(
                [
                    // Guzzle config
                ]
            );
        }

        $this->httpClient = $collaborators['http_client'];

        if (empty($collaborators['request_factory'])) {
            $collaborators['request_factory'] = new HttpFactory();
        }

        $this->requestFactory = $collaborators['request_factory'];
        $this->streamFactory = new HttpFactory();
        $this->uriFactory = new HttpFactory();

        if (empty($collaborators['oauth2_provider'])) {
            $collaborators['oauth2_provider'] = new NativeAuthenticator(new Oauth2Provider([
                'clientId'     => $this->client_id,
                'clientSecret' => $this->client_secret,
                'redirectUrl'  => $this->redirect_url,
                'apiVersion'   => $this->api_version,
                'apiDomain'    => $this->api_domain,
                'authDomain'   => $this->auth_domain,
            ]));
        }

        $this->oauth2Provider = $collaborators['oauth2_provider'];

        if (empty($collaborators['cache_provider'])) {
            $collaborators['cache_provider'] = new NullCacheItemPool();
        }

        $this->cacheProvider = $collaborators['cache_provider'];

        if (empty($collaborators['resource_factory'])) {
            $collaborators['resource_factory'] = new ResourceFactory();
        }

        $this->setResourceFactory($collaborators['resource_factory']);
    }

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException
     *
     * @return ResourceInterface
     */
    public function getResource(string $name)
    {
        if (! isset($this->resources[$name])) {
            $this->resources[$name] = $this->getResourceFactory()->createResource($name, $this);
        }

        return $this->resources[$name];
    }

    /**
     * Get a cache item
     *
     * @param string $key The item key
     *
     * @return CacheItemInterface the cache item
     */
    public function getCacheItem(string $key)
    {
        $key = $this->createCacheKey($key);

        return $this->cacheProvider->getItem($key);
    }

    /**
     * Save a cache item
     *
     * @param CacheItemInterface $item The item
     */
    public function saveCacheItem(CacheItemInterface $item): void
    {
        $this->cacheProvider->save($item);
    }

    /**
     * Delete a cache item
     *
     * @param CacheItemInterface $item The item
     */
    public function deleteCacheItem(CacheItemInterface $item): void
    {
        $this->cacheProvider->deleteItem($item->getKey());
    }

    /**
     * Check if we have a access token
     *
     * @return bool
     */
    public function isAuthorized()
    {
        // Check the access token
        try {
            $this->getAccessToken();

            return true;
        } catch (UnauthorizedException $e) {
            return false;
        }
    }

    /**
     * Force the Authorization with client (or user) credentials
     *
     * @param string $grant  the grant, e.g. `authorization_code`
     * @param array  $params for authorization code:
     *                       [
     *                       'code' => 'authorization_code_from_callback_url...',
     *                       'state' => 'state_from_callback_url_for_csrf_protection',
     *                       ]
     *
     * @throws InvalidArgumentException If a wrong state was set
     * @throws UnauthorizedException    contains the url to get an authorization code
     *
     * @return bool true, if a new access token was saved
     */
    public function authorize(string $grant, array $params = []): bool
    {
        if (! isset($params['code'])) {
            throw new InvalidArgumentException(__METHOD__ . '(): Argument #2 "$param" must have a "code" value.');
        }

        // Check state if present
        if (isset($params['state'])) {
            $item = $this->getCacheItem('state');

            if (! $item->isHit() or $item->get() !== $params['state']) {
                $this->deleteCacheItem($item);

                throw new InvalidArgumentException('Invalid state');
            }

            $this->deleteCacheItem($item);
        }

        // Try to get an access token (using the authorization code grant)
        $token = $this->oauth2Provider->getAccessToken($grant, [
            'code' => $params['code'],
        ]);

        $this->saveAccessToken($token);

        return true;
    }

    /**
     * Returns an authorization code url
     *
     * @param array $options
     *
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(array $options = [])
    {
        $default_options = [
            'scope' => $this->scope,
            'state' => $this->getState(),
        ];

        $options = array_merge($default_options, $options);

        return $this->oauth2Provider->getAuthorizationUrl($options);
    }

    /**
     * Returns the current value of the state parameter.
     *
     * This can be accessed by the redirect handler during authorization.
     *
     * @return string
     */
    public function getState()
    {
        $item = $this->getCacheItem('state');

        if (! $item->isHit()) {
            $state = $this->oauth2Provider->getState();

            $item->set($state);

            // Save state for 10 min
            $item->expiresAfter(new DateInterval('PT10M'));
            $this->saveCacheItem($item);
        }

        return $item->get();
    }

    /**
     * HTTP GETs a json $path and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @throws ClientExceptionInterface If anything went wrong on the http request
     * @throws ErrorResponseException If the server responses with a status code >= 400
     * @throws UnauthorizedException On 401 status code; contains the url to get an authorization code
     */
    public function get(string $path, array $data = []): Accessable
    {
        $data['headers']['Authorization'] = 'Bearer ' . $this->getAccessToken();

        $request = $this->createRequest('GET', $this->getApiUrl() . $path, $data);

        return $this->runRequest($request);
    }

    /**
     * HTTP GETs a json $path without Authorization and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @throws ClientExceptionInterface If anything went wrong on the http request
     * @throws ErrorResponseException If the server responses with a status code >= 400
     * @throws UnauthorizedException On 401 status code; contains the url to get an authorization code
     */
    public function getUnauthorized(string $path, array $data = []): Accessable
    {
        $request = $this->createRequest('GET', $this->getApiUrl() . $path, $data);

        return $this->runRequest($request);
    }

    /**
     * HTTP POSTs a json $path without Authorization and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @throws ClientExceptionInterface If anything went wrong on the http request
     * @throws ErrorResponseException If the server responses with a status code >= 400
     * @throws UnauthorizedException On 401 status code; contains the url to get an authorization code
     */
    public function postUnauthorized(string $path, array $data = []): Accessable
    {
        $request = $this->createRequest('POST', $this->getApiUrl() . $path, $data);

        return $this->runRequest($request);
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    private function getApiUrl()
    {
        return $this->api_domain;
    }

    /**
     * Build a cache key
     *
     * @param string $key The key
     *
     * @return string The cache key
     **/
    private function createCacheKey(string $key)
    {
        return $this->cache_namespace . strval($key);
    }

    /**
     * Save a access token in cache provider
     */
    private function saveAccessToken(AccessTokenInterface $token): void
    {
        $item = $this->getCacheItem('access_token');
        $item->set($token->getToken());
        $item->expiresAt(new DateTimeImmutable('@' . $token->getExpires()));

        $this->saveCacheItem($item);
    }

    /**
     * Get the Bearer Token
     *
     * @throws UnauthorizedException On 401 status code; contains the url to get an authorization code
     *
     * @return string The Bearer token, e.g. "jcx45..."
     */
    private function getAccessToken()
    {
        $item = $this->getCacheItem('access_token');

        if ($item->isHit()) {
            return $item->get();
        }

        $this->deleteCacheItem($item);

        throw UnauthorizedException::fromAuthorizationUrl('Unauthorized', $this->getAuthorizationUrl());
    }

    /**
     * @param RequestInterface $request The request to run
     *
     * @throws ClientExceptionInterface If anything went wrong on the http request
     * @throws ErrorResponseException If the server responses with a status code >= 400
     * @throws UnauthorizedException On 401 status code; contains the url to get an authorization code
     */
    private function runRequest(RequestInterface $request): Accessable
    {
        $response = $this->httpClient->sendRequest($request);

        $this->throwExceptionOnServerErrors($response);

        return $this->parseResponse($response);
    }

    /**
     * Creates a PSR-7 request instance.
     *
     * @param array  $options
     */
    private function createRequest(string $method, string $url, array $options): RequestInterface
    {
        $request = $this->requestFactory->createRequest(
            $method,
            $this->uriFactory->createUri($url),
        );

        // Should match default values for getRequest
        /** @var (null|array|string)[] */
        $defaultOptions = [
            'headers' => [],
            'body'    => null,
        ];

        $options = array_merge($defaultOptions, $options);

        $defaultHeaders = [
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->api_version,
        ];

        $headers = array_merge($defaultHeaders, $options['headers']);

        foreach ($headers as $name => $headerValue) {
            $request = $request->withAddedHeader($name, explode(',', $headerValue));
        }

        if ($options['body'] !== null) {
            $request = $request->withBody($this->streamFactory->createStream($options['body']));
        }

        return $request;
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws \Exception If anything goes wrong on the request
     */
    private function parseResponse(ResponseInterface $response): Accessable
    {
        $body = $response->getBody()->getContents();

        return JsonApiParser::parseResponseString($body);
    }

    /**
     * Set a resource_factory
     *
     * @param ResourceFactoryInterface $resource_factory the resource factory
     */
    private function setResourceFactory(ResourceFactoryInterface $resource_factory): void
    {
        $this->resource_factory = $resource_factory;
    }

    /**
     * Get the resource factory
     *
     * @return ResourceFactoryInterface the resource factory
     */
    private function getResourceFactory()
    {
        return $this->resource_factory;
    }

    /**
     * Handels potential server errors in a response
     *
     * @throws ErrorResponseException If the server responses with a status code >= 400
     * @throws UnauthorizedException On 401 status code; contains the url to get an authorization code
     **/
    private function throwExceptionOnServerErrors(ResponseInterface $response): void
    {
        if ($response->getStatusCode() < 400) {
            return;
        }

        $message = 'The server responses with an unknown error.';

        try {
            $document = $this->parseResponse($response);
        } catch (Throwable $th) {
            throw ErrorResponseException::fromResponse($response, $message);
        }

        // Get an error message from the json api body
        if ($document->has('errors.0')) {
            $error = $document->get('errors.0');

            if ($error->has('detail')) {
                $message = $error->get('detail');
            } elseif ($error->has('title')) {
                $message = $error->get('title');
            }
        }

        // Delete the access token if a 401 error occured
        if ($response->getStatusCode() === 401) {
            $item = $this->getCacheItem('access_token');
            $this->deleteCacheItem($item);

            throw UnauthorizedException::fromAuthorizationUrl($message, $this->getAuthorizationUrl());
        }

        throw ErrorResponseException::fromResponse($response, $message);
    }
}
