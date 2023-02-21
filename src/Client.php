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

use Art4\JsonApiClient\Helper\Parser as JsonApiParser;
use Cache\Adapter\Void\VoidCachePool;
use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Youthweb\Api\Authentication\Authenticator;
use Youthweb\Api\Authentication\NativeAuthenticator;
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

    /**
     * @var HttpClientInterface
     */
    private $http_client;

    /**
     * @var Authenticator
     */
    private $oauth2_provider;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache_provider;

    /**
     * @var string
     */
    private $cache_namespace = 'php_youthweb_api.';

    /**
     * @var array
     */
    private $resources = [];

    /**
     * @var RequestFactoryInterface
     */
    private $request_factory;

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
            $collaborators['http_client'] = new HttpClient(
                [
                    // Guzzle config
                ]
            );
        }

        $this->setHttpClientInternally($collaborators['http_client']);

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

        $this->setOauth2Provider($collaborators['oauth2_provider']);

        if (empty($collaborators['cache_provider'])) {
            $collaborators['cache_provider'] = new VoidCachePool();
        }

        $this->setCacheProviderInternally($collaborators['cache_provider']);

        if (empty($collaborators['request_factory'])) {
            $collaborators['request_factory'] = new RequestFactory();
        }

        $this->setRequestFactory($collaborators['request_factory']);

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
     * @return \Psr\Cache\CacheItemInterface the cache item
     */
    public function getCacheItem(string $key)
    {
        $key = $this->createCacheKey($key);

        return $this->getCacheProviderInternally()->getItem($key);
    }

    /**
     * Save a cache item
     *
     * @param \Psr\Cache\CacheItemInterface $item The item
     */
    public function saveCacheItem(CacheItemInterface $item): void
    {
        $this->getCacheProviderInternally()->saveDeferred($item);

        $this->getCacheProviderInternally()->commit();
    }

    /**
     * Delete a cache item
     *
     * @param \Psr\Cache\CacheItemInterface $item The item
     */
    public function deleteCacheItem(CacheItemInterface $item): void
    {
        $this->getCacheProviderInternally()->deleteItem($item->getKey());
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
    public function authorize(string $grant, array $params = [])
    {
        if (! isset($params['code'])) {
            throw new UnauthorizedException();
        }

        $state_item = $this->getCacheItem('state');

        // Check state if present
        if (isset($params['state'])) {
            if (! $state_item->isHit() or $state_item->get() !== $params['state']) {
                $this->deleteCacheItem($state_item);

                throw new InvalidArgumentException('Invalid state');
            }
        }

        $this->deleteCacheItem($state_item);

        // Try to get an access token (using the authorization code grant)
        $token = $this->getOauth2Provider()->getAccessToken($grant, [
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

        return $this->getOauth2Provider()->getAuthorizationUrl($options);
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
        $state_item = $this->getCacheItem('state');

        if (! $state_item->isHit()) {
            $state = $this->getOauth2Provider()->getState();

            $state_item->set($state);

            // Save state for 10 min
            $state_item->expiresAfter(new DateInterval('PT10M'));
            $this->saveCacheItem($state_item);
        }

        return $state_item->get();
    }

    /**
     * HTTP GETs a json $path and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @throws UnauthorizedException contains the url to get an authorization code
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function get(string $path, array $data = [])
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
     * @return \Art4\JsonApiClient\Accessable
     */
    public function getUnauthorized(string $path, array $data = [])
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
     * @return \Art4\JsonApiClient\Accessable
     */
    public function postUnauthorized(string $path, array $data = [])
    {
        $request = $this->createRequest('POST', $this->getApiUrl() . $path, $data);

        return $this->runRequest($request);
    }

    /**
     * destructor
     **/
    public function __destruct()
    {
        // Save deferred items
        $this->getCacheProviderInternally()->commit();
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
     * Set a http client
     *
     * @param HttpClientInterface $client the http client
     */
    private function setHttpClientInternally(HttpClientInterface $client): void
    {
        $this->http_client = $client;
    }

    /**
     * Set a cache provider
     *
     * @param CacheItemPoolInterface $cache_provider the cache provider
     */
    private function setCacheProviderInternally(CacheItemPoolInterface $cache_provider): void
    {
        $this->cache_provider = $cache_provider;
    }

    /**
     * Get the cache provider
     *
     * @return CacheItemPoolInterface the cache provider
     */
    private function getCacheProviderInternally()
    {
        return $this->cache_provider;
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
        $access_token_item = $this->getCacheItem(self::CACHEKEY_ACCESS_TOKEN);
        $access_token_item->set($token->getToken());
        $access_token_item->expiresAt(new DateTime('@' . $token->getExpires()));
        $this->saveCacheItem($access_token_item);
    }

    /**
     * Set a oauth2 provider
     *
     * @param Authenticator $oauth2_provider the oauth2 provider
     */
    private function setOauth2Provider(Authenticator $oauth2_provider): void
    {
        $this->oauth2_provider = $oauth2_provider;
    }

    /**
     * Get the oauth2 provider
     *
     * @return Authenticator the oauth2 provider
     */
    private function getOauth2Provider(): Authenticator
    {
        return $this->oauth2_provider;
    }

    /**
     * Get the Bearer Token
     *
     * @throws UnauthorizedException contains the url to get an authorization code
     *
     * @return string The Bearer token, e.g. "jcx45..."
     */
    private function getAccessToken()
    {
        $access_token_item = $this->getCacheItem(self::CACHEKEY_ACCESS_TOKEN);

        if ($access_token_item->isHit()) {
            return $access_token_item->get();
        }

        $this->deleteCacheItem($access_token_item);

        throw new UnauthorizedException('Unauthorized', 401);
    }

    /**
     * @param RequestInterface $request The request to run
     *
     * @throws \Exception If anything goes wrong on the request
     *
     * @return mixed
     */
    private function runRequest(RequestInterface $request)
    {
        try {
            $response = $this->getHttpClient()->send($request);
        } catch (\Exception $e) {
            throw $this->handleClientException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * Creates a PSR-7 request instance.
     *
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @return RequestInterface
     */
    private function createRequest(string $method, string $url, array $options)
    {
        $options = $this->parseOptions($options);

        $default_headers = [
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->api_version,
        ];

        $headers = array_merge($default_headers, $options['headers']);

        return $this->getRequestFactory()->createRequest($method, $url, $headers, $options['body'], $options['version']);
    }

    /**
     * Parses simplified options.
     *
     * @param array $options simplified options
     *
     * @return array extended options for use with getRequest
     */
    private function parseOptions(array $options)
    {
        // Should match default values for getRequest
        $defaults = [
            'headers' => [],
            'body'    => null,
            'version' => '1.1',
        ];

        return array_merge($defaults, $options);
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws \Exception If anything goes wrong on the request
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    private function parseResponse(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();

        return JsonApiParser::parseResponseString($body);
    }

    /**
     * Returns the http client
     *
     * @return HttpClientInterface The Http client
     */
    private function getHttpClient()
    {
        return $this->http_client;
    }

    /**
     * Set a request_factory
     *
     * @param RequestFactoryInterface $request_factory the request factory
     */
    private function setRequestFactory(RequestFactoryInterface $request_factory): void
    {
        $this->request_factory = $request_factory;
    }

    /**
     * Get the request factory
     *
     * @return RequestFactoryInterface the request factory
     */
    private function getRequestFactory()
    {
        return $this->request_factory;
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
     * Handels a Exception from the Client
     *
     * @param Throwable $th The exception
     *
     * @return Throwable An exception for re-throwing
     **/
    private function handleClientException(Throwable $th)
    {
        $message = null;
        $response = null;

        // Try to get the response
        if ($th instanceof ClientException or is_callable([$th, 'getResponse'])) {
            $response = $th->getResponse();
        }

        if (is_object($response) and $response instanceof ResponseInterface) {
            $document = $this->parseResponse($response);

            // Get an error message from the json api body
            if ($document->has('errors.0')) {
                $error = $document->get('errors.0');

                if ($error->has('detail')) {
                    $message = $error->get('detail');
                } elseif ($error->has('title')) {
                    $message = $error->get('title');
                }
            }
        }

        if (is_null($message)) {
            $message = 'The server responses with an unknown error.';
        }

        // Delete the access token if a 401 error occured
        if (strval($th->getCode()) === '401') {
            $access_token_item = $this->getCacheItem(self::CACHEKEY_ACCESS_TOKEN);
            $this->deleteCacheItem($access_token_item);
        }

        return new Exception($message, $th->getCode(), $th);
    }
}
