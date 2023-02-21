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
use Youthweb\Api\Exception\UnauthorizedException;

/**
 * Interface for authenticator
 */
interface AuthenticatorInterface
{
    /**
     * Constructs the Authenticator
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
     * get the authorization url
     *
     * @param array $options
     *
     * @return string
     */
    public function getAuthorizationUrl(array $options = []);

    /**
     * get a random state
     *
     * @return string
     */
    public function getState();

    /**
     * Get an access token
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
     */
    public function getAccessToken(string $grant, array $params = []);
}
