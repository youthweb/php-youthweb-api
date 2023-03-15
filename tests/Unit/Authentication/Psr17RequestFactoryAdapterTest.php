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

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Youthweb\Api\Authentication\Psr17RequestFactoryAdapter;

class Psr17RequestFactoryAdapterTest extends TestCase
{
    public function testGetRequestReturnsRequestInterface(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(RequestInterface::class, $adapter->getRequest('GET', ''));
    }
}
