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

namespace Youthweb\Api\Authentication;

use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Youthweb\Api\Exception\UnauthorizedException;

/**
 * Interface for authenticator
 */
interface Authenticator
{
    /**
     * get the authorization url
     *
     * @param array $options
     */
    public function getAuthorizationUrl(array $options = []): string;

    /**
     * get a random state
     *
     * @return string Could be empty
     */
    public function getState(): string;

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
     */
    public function getAccessToken(string $grant, array $params = []): AccessTokenInterface;
}
