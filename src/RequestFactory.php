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

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Factory for PSR-7 Requests
 */
final class RequestFactory implements RequestFactoryInterface
{
    /**
     * Creates a PSR-7 request instance.
     *
     * @param string                          $method
     * @param string                          $url
     * @param array                           $headers headers for the message
     * @param string|resource|StreamInterface $body    message body
     * @param string                          $version HTTP protocol version
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, string $url, array $headers = [], $body = null, string $version = '1.1')
    {
        return new Request($method, $url, $headers, $body, $version);
    }
}
