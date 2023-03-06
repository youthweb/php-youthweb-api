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

use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Youthweb\Api\Cache\NullCacheItemPool;

class NullCacheItemPoolTest extends TestCase
{
    public function testNullCacheItemPoolImplementsCacheItemPoolInterface(): void
    {
        $pool = new NullCacheItemPool();

        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
    }

    public function testGetItemReturnsCacheItemInterface(): void
    {
        $pool = new NullCacheItemPool();

        $this->assertInstanceOf(CacheItemInterface::class, $pool->getItem('name'));
    }

    public function testGetItemReturnsSameCacheItem(): void
    {
        $pool = new NullCacheItemPool();
        $item = $pool->getItem('name');

        $this->assertSame($item, $pool->getItem('name'));
    }

    public function testGetItemWithEmptyKeyThrowsInvalidArgumentException(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(InvalidArgumentException::class);

        $pool->getItem('');
    }

    public function testGetItemWithInvalidKeyThrowsInvalidArgumentException(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(InvalidArgumentException::class);

        $pool->getItem('-');
    }

    public function testDeleteItemWithLoadedItemReturnsTrue(): void
    {
        $pool = new NullCacheItemPool();
        $item = $pool->getItem('name');

        $this->assertTrue($pool->deleteItem('name'));
    }

    public function testDeleteItemWithUnloadedItemReturnsFalse(): void
    {
        $pool = new NullCacheItemPool();

        $this->assertFalse($pool->deleteItem('name'));
    }

    public function testGetItemsIsNotImplemented(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::getItems() is not implemented.');

        $pool->getItems([]);
    }

    public function testHasItemIsNotImplemented(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::hasItem() is not implemented.');

        $pool->hasItem('name');
    }

    public function testClearIsNotImplemented(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::clear() is not implemented.');

        $pool->clear();
    }

    public function testDeleteItemsIsNotImplemented(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::deleteItems() is not implemented.');

        $pool->deleteItems([]);
    }

    public function testSaveThrowsLogicExcpetion(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A cache provider is needed for requesting protected API endpoints. Please provide an implementation of "Psr\Cache\CacheItemPoolInterface".');

        $pool->save($this->createMock(CacheItemInterface::class));
    }

    public function testSaveDeferredIsNotImplemented(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::saveDeferred() is not implemented.');

        $pool->saveDeferred($this->createMock(CacheItemInterface::class));
    }

    public function testCommitIsNotImplemented(): void
    {
        $pool = new NullCacheItemPool();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('::commit() is not implemented.');

        $pool->commit();
    }
}
