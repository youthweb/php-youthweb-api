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
use RuntimeException;
use Youthweb\Api\Exception\UnauthorizedException;

class UnauthorizedExceptionTest extends TestCase
{
    public function testUnauthorizedExceptionImplementsRuntimeException(): void
    {
        $exception = new UnauthorizedException();

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testFromAuthorizationUrlReturnsUnauthorizedException(): void
    {
        $exception = UnauthorizedException::fromAuthorizationUrl('', '');

        $this->assertInstanceOf(UnauthorizedException::class, $exception);
        $this->assertSame(401, $exception->getCode());
    }

    public function testGetAuthorizationUrlReturnsAuthorizationUrl(): void
    {
        $exception = UnauthorizedException::fromAuthorizationUrl('', 'https://example.com/auth');

        $this->assertSame('https://example.com/auth', $exception->getAuthorizationUrl());
    }
}
