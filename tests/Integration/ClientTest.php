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

namespace Youthweb\Api\Tests\Integration;

use LogicException;
use PHPUnit\Framework\TestCase;
use Youthweb\Api\Client;
use Youthweb\Api\ClientInterface;
use Youthweb\Api\Configuration;

class ClientTest extends TestCase
{
    public function testClientImplementsClientInterface(): void
    {
        $client = Client::fromConfig(Configuration::createUnauthorized());

        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testAuthorizedHttpRequestsWithoutCacheProviderThrowsException(): void
    {
        $client = Client::fromConfig(Configuration::createUnauthorized());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A cache provider is needed for requesting protected API endpoints. Please provide an implementation of "Psr\Cache\CacheItemPoolInterface".');

        $client->get('/endpoint');
    }
}
