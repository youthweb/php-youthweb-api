<?php
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
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Youthweb\Api\Exception\UnauthorizedException;

/**
 * Simple PHP Youthweb client
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
final class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $api_version = '0.14';

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
     * @var League\OAuth2\Client\Provider\AbstractProvider
     */
    private $oauth2_client;

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
            $collaborators['oauth2_provider'] = new YouthwebAuthenticator([
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_url'  => $this->redirect_url,
                'api_domain'    => $this->api_domain,
                'auth_domain'   => $this->auth_domain,
            ]);
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
     * @throws \InvalidArgumentException
     *
     * @return Resource\AbstractResource
     */
    public function getResource($name)
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
     * @return Psr\Cache\CacheItemInterface the cache item
     */
    public function getCacheItem($key)
    {
        $key = $this->createCacheKey($key);

        return $this->getCacheProviderInternally()->getItem($key);
    }

    /**
     * Save a cache item
     *
     * @param Psr\Cache\CacheItemInterface $item The item
     */
    public function saveCacheItem(CacheItemInterface $item)
    {
        $this->getCacheProviderInternally()->saveDeferred($item);

        $this->getCacheProviderInternally()->commit();
    }

    /**
     * Delete a cache item
     *
     * @param Psr\Cache\CacheItemInterface $item The item
     */
    public function deleteCacheItem(CacheItemInterface $item)
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
        $access_token_item = $this->getCacheItem('access_token');

        if ($access_token_item->isHit()) {
            return true;
        }

        $this->deleteCacheItem($access_token_item);

        return false;
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
    public function authorize($grant, array $params = [])
    {
        if (! isset($params['code'])) {
            throw new UnauthorizedException;
        }

        $state_item = $this->getCacheItem('state');

        // Check state if present
        if (isset($params['state'])) {
            if (! $state_item->isHit() or $state_item->get() !== $params['state']) {
                $this->deleteCacheItem($state_item);

                throw new \InvalidArgumentException('Invalid state');
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
     * @return array
     */
    public function get($path, array $data = [])
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
     * @return array
     */
    public function getUnauthorized($path, array $data = [])
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
     * @return array
     */
    public function postUnauthorized($path, array $data = [])
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
    private function setHttpClientInternally(HttpClientInterface $client)
    {
        $this->http_client = $client;
    }

    /**
     * Set a cache provider
     *
     * @param Psr\Cache\CacheItemPoolInterface $cache_provider the cache provider
     */
    private function setCacheProviderInternally(CacheItemPoolInterface $cache_provider)
    {
        $this->cache_provider = $cache_provider;
    }

    /**
     * Get the cache provider
     *
     * @return Psr\Cache\CacheItemPoolInterface the cache provider
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
    private function createCacheKey($key)
    {
        return $this->cache_namespace . strval($key);
    }

    /**
     * Save a access token in cache provider
     *
     * @param AccessToken $token The access token
     */
    private function saveAccessToken(AccessToken $token)
    {
        $access_token_item = $this->getCacheItem('access_token');
        $access_token_item->set($token->getToken());
        $access_token_item->expiresAt(new DateTime('@' . $token->getExpires()));
        $this->saveCacheItem($access_token_item);
    }

    /**
     * Set a oauth2 provider
     *
     * @param AuthenticatorInterface $oauth2_provider the oauth2 provider
     */
    private function setOauth2Provider(AuthenticatorInterface $oauth2_provider)
    {
        $this->oauth2_provider = $oauth2_provider;
    }

    /**
     * Get the oauth2 provider
     *
     * @return AuthenticatorInterface the oauth2 provider
     */
    private function getOauth2Provider()
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
        if (! $this->isAuthorized()) {
            throw new UnauthorizedException('Unauthorized', 401);
        }

        $access_token_item = $this->getCacheItem('access_token');

        if ($access_token_item->isHit()) {
            return $access_token_item->get();
        }
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
    private function createRequest($method, $url, array $options)
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
    private function setRequestFactory(RequestFactoryInterface $request_factory)
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
    private function setResourceFactory(ResourceFactoryInterface $resource_factory)
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
     * @param \Exception $e The exception
     *
     * @return \Exception An exception for re-throwing
     **/
    private function handleClientException(\Exception $e)
    {
        $message = null;
        $response = null;

        // Try to get the response
        if ($e instanceof ClientException or is_callable([$e, 'getResponse'])) {
            $response = $e->getResponse();
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
        if (strval($e->getCode()) === '401') {
            $access_token_item = $this->getCacheItem('access_token');
            $this->deleteCacheItem($access_token_item);
        }

        return new \Exception($message, $e->getCode(), $e);
    }
}
