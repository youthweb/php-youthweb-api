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

namespace Youthweb\Api\Tests\Unit\Cache;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;
use Youthweb\Api\Cache\NullCacheItem;

class NullCacheItemTest extends TestCase
{
    public function testGetKeyReturnsKey(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame('name', $item->getKey());
    }

    public function testGetWithoutValueReturnsNull(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame(null, $item->get());
    }

    public function testGetWithValueReturnsValue(): void
    {
        $value = new stdClass();

        $item = new NullCacheItem('name');
        $item->set($value);

        $this->assertSame($value, $item->get());
    }

    public function testIsHitWithoutValueReturnsFalse(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame(false, $item->isHit());
    }

    public function testIsHitWithValueReturnsTrue(): void
    {
        $value = new stdClass();

        $item = new NullCacheItem('name');
        $item->set($value);

        $this->assertSame(true, $item->isHit());
    }

    public function testSetReturnsSelf(): void
    {
        $value = new stdClass();

        $item = new NullCacheItem('name');

        $this->assertSame($item, $item->set($value));
    }

    public function testExpiresAtWithDateTimeReturnsSelf(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame($item, $item->expiresAt(new DateTimeImmutable()));
    }

    public function testExpiresAtWithNullReturnsSelf(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame($item, $item->expiresAt(null));
    }

    public function testExpiresAfterWithIntegerReturnsSelf(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame($item, $item->expiresAfter(1));
    }

    public function testExpiresAfterWithDateIntervalReturnsSelf(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame($item, $item->expiresAfter(new DateInterval('PT1S')));
    }

    public function testExpiresAfterWithNullReturnsSelf(): void
    {
        $item = new NullCacheItem('name');

        $this->assertSame($item, $item->expiresAfter(null));
    }
}
