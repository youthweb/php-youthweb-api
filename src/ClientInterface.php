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

use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Youthweb\Api\Exception\UnauthorizedException;
use Youthweb\Api\Resource\ResourceInterface;

/**
 * Interface for client
 */
interface ClientInterface
{
    /**
     * Get a cache item
     *
     * @param string $key The item key
     *
     * @return CacheItemInterface the cache item
     */
    public function getCacheItem(string $key);

    /**
     * Save a cache item
     *
     * @param CacheItemInterface $item The item
     */
    public function saveCacheItem(CacheItemInterface $item);

    /**
     * Delete a cache item
     *
     * @param CacheItemInterface $item The item
     */
    public function deleteCacheItem(CacheItemInterface $item);

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException
     *
     * @return ResourceInterface
     */
    public function getResource(string $name);

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
     * @throws UnauthorizedException       contains the url to get an authorization code
     *
     * @return bool true, if a new access token was saved
     */
    public function authorize(string $grant, array $params = []);

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
     * @return \Art4\JsonApiClient\Accessable
     */
    public function get(string $path, array $data = []);

    /**
     * HTTP GETs a json $path without Authorization and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function getUnauthorized(string $path, array $data = []);

    /**
     * HTTP POSTs a json $path without Authorization and decodes it to an object
     *
     * @param string $path
     * @param array  $data
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function postUnauthorized(string $path, array $data = []);
}
