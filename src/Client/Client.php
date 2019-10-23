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
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Youthweb\Api\MinimalClientInterface;
use Youthweb\Api\Resource\ResourceInterface;

/**
 * Interface for client
 */
interface Client extends MinimalClientInterface
{
    /**
     * Get the PSR-16 simple cache.
     */
    public function getSimpleCache(): CacheInterface;

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return \Youthweb\Api\Resource
     */
    public function getResource(/*string */$name)/*: ResourceInterface*/;

    /**
     * Check if we have a access token.
     *
     * @return bool
     */
    public function isAuthorized()/*: bool*/;

    /**
     * Authorize the client credentials.
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
    public function authorize(/*string */$grant, array $params = [])/*: bool*/;

    /**
     * Returns an authorization code url.
     *
     * @param array $options
     *
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(array $options = [])/*: string*/;

    /**
     * Returns the current value of the state parameter.
     *
     * This can be accessed by the redirect handler during authorization.
     *
     * @return string
     */
    public function getState()/*: string*/;

    /**
     * Create a new authorized request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param string $uri The URI associated with the request.
     */
    public function createRequest(string $method, string $uri): RequestInterface;

    /**
     * Create a new unauthorized request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param string $uri The URI associated with the request.
     */
    public function createUnauthorizedRequest(string $method, string $uri): RequestInterface;

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
    public function handleRequest(RequestInterface $request): Accessable;
}
