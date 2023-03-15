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
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Youthweb\Api\Authentication\Psr17RequestFactoryAdapter;

class Psr17RequestFactoryAdapterTest extends TestCase
{
    public function testGetRequestCallsThePsr17RequestFactoryCorrectly(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method('withProtocolVersion')->willReturn($request);
        $request->expects($this->once())->method('withAddedHeader')->with('foo', 'bar')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('GET', 'https://example.com', ['foo' => 'bar']),
        );
    }

    public function testGetRequestCallsThePsr17UriFactoryCorrectly(): void
    {
        $uri = $this->createMock(UriInterface::class);

        $uriFactory = $this->createMock(UriFactoryInterface::class);
        $uriFactory->expects($this->once())->method('createUri')->with('https://example.com')->willReturn($uri);

        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->with('GET', $uri)->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            $uriFactory,
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('GET', 'https://example.com', []),
        );
    }

    public function testGetRequestWithNullCallsThePsr17StreamFactoryCorrectly(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->never())->method('createStream');
        $streamFactory->expects($this->never())->method('createStreamFromResource');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $streamFactory,
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('POST', 'https://example.com', [], null),
        );
    }

    public function testGetRequestWithEmptyStringCallsThePsr17StreamFactoryCorrectly(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->never())->method('createStream');
        $streamFactory->expects($this->never())->method('createStreamFromResource');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $streamFactory,
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('POST', 'https://example.com', [], ''),
        );
    }

    public function testGetRequestWithStringCallsThePsr17StreamFactoryCorrectly(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->once())->method('createStream')->willReturn($this->createMock(StreamInterface::class));
        $streamFactory->expects($this->never())->method('createStreamFromResource');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $streamFactory,
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('POST', 'https://example.com', [], '{}'),
        );
    }

    public function testGetRequestWithResourceCallsThePsr17StreamFactoryCorrectly(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->never())->method('createStream');
        $streamFactory->expects($this->once())->method('createStreamFromResource')->willReturn($this->createMock(StreamInterface::class));

        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $streamFactory,
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('POST', 'https://example.com', [], fopen(__FILE__, 'r')),
        );
    }

    public function testGetRequestWithStreamCallsThePsr17StreamFactoryCorrectly(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->never())->method('createStream');
        $streamFactory->expects($this->never())->method('createStreamFromResource');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withProtocolVersion')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $adapter = Psr17RequestFactoryAdapter::createFromPsr17(
            $requestFactory,
            $streamFactory,
            $this->createMock(UriFactoryInterface::class),
        );

        $this->assertInstanceOf(
            RequestInterface::class,
            $adapter->getRequest('POST', 'https://example.com', [], $this->createMock(StreamInterface::class)),
        );
    }
}
