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

namespace Youthweb\Api\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Youthweb\Api\Exception\ErrorResponseException;

class ErrorResponseExceptionTest extends TestCase
{
    public function testErrorResponseExceptionImplementsRuntimeException(): void
    {
        $exception = new ErrorResponseException();

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testFromResponseReturnsErrorResponseException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(500);

        $exception = ErrorResponseException::fromResponse($response, 'error message');

        $this->assertInstanceOf(ErrorResponseException::class, $exception);
        $this->assertSame('error message', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
    }

    public function testgetResponseReturnsResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(500);

        $exception = ErrorResponseException::fromResponse($response, 'error message');

        $this->assertSame($response, $exception->getResponse());
    }
}
