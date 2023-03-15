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

use Exception;
use GuzzleHttp\ClientInterface as GuzzleHttpClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Adapter for PSR-18 clients to implement Guzzle ClientInterface
 *
 * @internal
 */
final class Psr18GuzzleAdapter implements GuzzleHttpClientInterface
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        throw new Exception(__METHOD__ . '() is not implemented.', 1);
    }

    public function request(string $method, $uri, array $options = []): ResponseInterface
    {
        throw new Exception(__METHOD__ . '() is not implemented.', 1);
    }

    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        throw new Exception(__METHOD__ . '() is not implemented.', 1);
    }

    public function getConfig(?string $option = null): void
    {
        throw new Exception(__METHOD__ . '() is not implemented.', 1);
    }
}
