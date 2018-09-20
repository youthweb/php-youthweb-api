<?php
/*
 * PHP Youthweb API is an object-oriented wrapper for PHP of the Youthweb API.
 * Copyright (C) 2015-2018  Youthweb e.V.  https://youthweb.net
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

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Http client interface
 */
interface HttpClientInterface
{
    /**
     * Perform an HTTP request and return response
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request obtions to apply to the given request and to the transfer
     *
     * @throws Exception if request failed (network problem, timeout, etc.)
     *
     * @return ResponseInterface
     */
    public function send(RequestInterface $request, array $options = []);
}
