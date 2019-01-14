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

use Cache\Adapter\Void\VoidCachePool;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for client
 */
interface ClientInterface
{
    /**
     * Constructs the Client.
     *
     * @param array $options       an array of options to set on the client.
     *                             Options include `api_version`, `api_domain`, `auth_domain`,
     *                             `cache_namespace`, `client_id`, `client_secret` and `redirect_url`
     * @param array $collaborators An array of collaborators that may be used to
     *                             override this provider's default behavior. Collaborators include
     *                             http_client`, `oauth2_provider`, `cache_provider`, `request_factory`
     *                             and `resource_factory`.
     */
    public function __construct(array $options = [], array $collaborators = []);

    /**
     * Get a cache item
     *
     * @param string $key The item key
     *
     * @return Psr\Cache\CacheItemInterface the cache item
     */
    public function getCacheItem($key);

    /**
     * Save a cache item
     *
     * @param Psr\Cache\CacheItemInterface $item The item
     */
    public function saveCacheItem(CacheItemInterface $item);

    /**
     * Delete a cache item
     *
     * @param Psr\Cache\CacheItemInterface $item The item
     */
    public function deleteCacheItem(CacheItemInterface $item);

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Resource\AbstractResource
     */
    public function getResource($name);

    /**
     * Check if we have a access token
     *
     * @return bool
     */
    public function isAuthorized();

    /**
     * Authorize the client credentials
     *
     * @param string $grant  the grant, e.g. `authorization_code`
     * @param array  $params for authorization code:
     *                       [
     *                       'code' => 'authorization_code_from_callback_url...',
     *                       'state' => 'state_from_callback_url_for_csrf_protection',
     *                       ]
     *
     * @throws InvalidArgumentException    If a wrong state was set
     * @throws MissingCredentialsException If no user or client credentials are set
     * @throws UnauthorizedException       contains the url to get an authorization code
     *
     * @return bool true, if a new access token was saved
     */
    public function authorize($grant, array $params = []);

    /**
     * Returns an authorization code url
     *
     * @param array $options
     *
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(array $options = []);

    /**
     * Returns the current value of the state parameter.
     *
     * This can be accessed by the redirect handler during authorization.
     *
     * @return string
     */
    public function getState();

    /**
     * HTTP GETs a json $path and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @return array
     */
    public function get($path, array $data = []);

    /**
     * HTTP GETs a json $path without Authorization and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @return array
     */
    public function getUnauthorized($path, array $data = []);

    /**
     * HTTP POSTs a json $path without Authorization and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @return array
     */
    public function postUnauthorized($path, array $data = []);
}
