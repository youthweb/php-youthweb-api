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

namespace Youthweb\Api\Client;

use Art4\JsonApiClient\Accessable;
use Art4\JsonApiClient\Helper\Parser as JsonApiParser;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Youthweb\Api\AuthenticatorInterface;
use Youthweb\Api\Exception\UnauthorizedException;
use Youthweb\Api\Resource\ResourceInterface;
use Youthweb\Api\YouthwebAuthenticator;

/**
 * Generic client
 */
final class GenericClient implements Client
{
    /**
     * Constructs the Client from providers.
     *
     * @param \Youthweb\Api\AuthenticatorInterface $oauth2Provider
     */
    public static function createFromProviders(
        AuthenticatorInterface $oauth2client,
        ClientInterface $httpClient,
        CacheInterface $cacheClient,
        RequestFactoryInterface $requestFactory
    ) {
        return new self(
            $oauth2client,
            $httpClient,
            $cacheClient,
            $requestFactory
        );
    }

    private const CACHEKEY_ACCESS_TOKEN = 'access_token';

    /**
     * @var string
     */
    private $apiVersion = '0.15';

    /**
     * @var string
     */
    private $apiDomain = 'https://api.youthweb.net';

    /**
    * @var \Youthweb\Api\AuthenticatorInterface
    */
    private $oauth2client;

    /**
     * @var \Psr\Http\Client\ClientInterface
     */
    private $httpClient;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cacheClient;

    /**
    * @var \Psr\Http\Message\RequestFactoryInterface
    */
    private $requestFactory;

    /**
     * @var string
     */
    private $cacheNamespace = 'php_youthweb_api.';

    /**
     * @var array
     */
    private $resources = [];

    /**
     * Constructs the Client.
     *
     * @param \Youthweb\Api\AuthenticatorInterface $oauth2Provider
     */
    private function __construct(
        AuthenticatorInterface $oauth2client,
        ClientInterface $httpClient,
        CacheInterface $cacheClient,
        RequestFactoryInterface $requestFactory
    ) {
        $this->oauth2client = $oauth2client;
        $this->httpClient = $httpClient;
        $this->cacheClient = $cacheClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Get the PSR-16 simple cache.
     */
    public function getSimpleCache(): CacheInterface
    {
        return $this->cacheClient;
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Resource\AbstractResource
     */
    public function getResource(/*string */$name)/*: ResourceInterface*/
    {
        if (! isset($this->resources[$name])) {
            $this->resources[$name] = $this->createResource($name);
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
        $key = $this->cacheNamespace . strval($key);

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
    public function isAuthorized()/*: bool*/
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
    public function authorize(/*string */$grant, array $params = [])/*: bool*/
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
        $token = $this->oauth2Provider->getAccessToken($grant, [
            'code' => $params['code'],
        ]);

        $access_token_item = $this->getCacheItem(self::CACHEKEY_ACCESS_TOKEN);
        $access_token_item->set($token->getToken());
        $access_token_item->expiresAt(new DateTime('@' . $token->getExpires()));
        $this->saveCacheItem($access_token_item);

        return true;
    }

    /**
     * Returns an authorization code url
     *
     * @param array $options
     *
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(array $options = [])/*: string*/
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
    public function getState()/*: string*/
    {
        $state_item = $this->getCacheItem('state');

        if (! $state_item->isHit()) {
            $state = $this->oauth2Provider->getState();

            $state_item->set($state);

            // Save state for 10 min
            $state_item->expiresAfter(new DateInterval('PT10M'));
            $this->saveCacheItem($state_item);
        }

        return $state_item->get();
    }

    /**
     * Create a new authorized request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param string $uri The URI associated with the request.
     */
    public function createRequest(string $method, string $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    /**
     * Create a new unauthorized request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param string $uri The URI associated with the request.
     */
    public function createUnauthorizedRequest(string $method, string $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    /**
     * Sends a PSR-7 request and returns an Accessable.
     *
     * @param RequestInterface $request
     *
     * @return \Art4\JsonApiClient\Accessable
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     * @throws \Exception If anything goes wrong on the request
     */
    public function handleRequest(RequestInterface $request): Accessable
    {
        return $this->runRequest($request);
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

        $request = $this->createRequestInternally('GET', $this->apiDomain . $path, $data);

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
        $request = $this->createRequestInternally('GET', $this->apiDomain . $path, $data);

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
        $request = $this->createRequestInternally('POST', $this->apiDomain . $path, $data);

        return $this->runRequest($request);
    }

    /**
     * Creates a API resource
     *
     * @param string $name
     *
     * @return RequestInterface
     */
    private function createResource($name)
    {
        $classes = [
            'posts' => 'Youthweb\\Api\\Resource\\Posts',
            'stats' => 'Youthweb\\Api\\Resource\\Stats',
            'users' => 'Youthweb\\Api\\Resource\\Users',
        ];

        if (! isset($classes[$name])) {
            throw new \InvalidArgumentException('The resource "' . $name . '" does not exists.');
        }

        $resource = $classes[$name];

        return new $resource($this);
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
            $response = $this->httpClient->send($request);
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
    private function createRequestInternally($method, $url, array $options)
    {
        $options = $this->parseOptions($options);

        $default_headers = [
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->apiVersion,
        ];

        $headers = array_merge($default_headers, $options['headers']);

        return $this->requestFactory->createRequest($method, $url, $headers, $options['body'], $options['version']);
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
            $access_token_item = $this->getCacheItem(self::CACHEKEY_ACCESS_TOKEN);
            $this->deleteCacheItem($access_token_item);
        }

        return new \Exception($message, $e->getCode(), $e);
    }
}
