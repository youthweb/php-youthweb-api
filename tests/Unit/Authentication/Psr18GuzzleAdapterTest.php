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

namespace Youthweb\Api\Tests\Unit\Authentication;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Youthweb\Api\Authentication\Psr18GuzzleAdapter;

class Psr18GuzzleAdapterTest extends TestCase
{
    public function testSendIsNotImplemented(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($request)->willReturn(
            $this->createMock(ResponseInterface::class)
        );

        $adapter = new Psr18GuzzleAdapter($client);

        $adapter->send($request);
    }

    public function testSendAsyncIsNotImplemented(): void
    {
        $adapter = new Psr18GuzzleAdapter($this->createMock(ClientInterface::class));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::sendAsync() is not implemented.');

        $adapter->sendAsync($this->createMock(RequestInterface::class));
    }

    public function testRequestIsNotImplemented(): void
    {
        $adapter = new Psr18GuzzleAdapter($this->createMock(ClientInterface::class));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::request() is not implemented.');

        $adapter->request('GET', '');
    }

    public function testRequestAsyncIsNotImplemented(): void
    {
        $adapter = new Psr18GuzzleAdapter($this->createMock(ClientInterface::class));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::requestAsync() is not implemented.');

        $adapter->requestAsync('GET', '');
    }

    public function testGetConfigIsNotImplemented(): void
    {
        $adapter = new Psr18GuzzleAdapter($this->createMock(ClientInterface::class));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::getConfig() is not implemented.');

        $adapter->getConfig();
    }
}
