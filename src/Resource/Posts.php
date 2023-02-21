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

namespace Youthweb\Api\Resource;

/**
 * Posts
 *
 * @see https://developer.youthweb.net/api_endpoint_posts.html
 */
final class Posts implements PostsInterface
{
    use ClientTrait;

    /**
     * Get a post
     *
     * @see https://developer.youthweb.net/api_endpoint_posts.html
     *
     * @param string $id
     *
     * @return array the post data
     */
    public function show(string $id)
    {
        return $this->client->get('/posts/' . strval($id));
    }

    /**
     * Get the author of a post
     *
     * @see https://developer.youthweb.net/api_endpoint_posts.html#beziehungen
     *
     * @param string $id
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function showAuthor(string $id)
    {
        return $this->client->get('/posts/' . strval($id) . '/author');
    }

    /**
     * Get the author relationship of a post
     *
     * @see https://developer.youthweb.net/api_endpoint_posts.html#beziehungen
     *
     * @param string $id
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function showAuthorRelationship(string $id)
    {
        return $this->client->get('/posts/' . strval($id) . '/relationships/author');
    }

    /**
     * Get the parent of a post
     *
     * @see https://developer.youthweb.net/api_endpoint_posts.html#beziehungen
     *
     * @param string $id
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function showParent(string $id)
    {
        return $this->client->get('/posts/' . strval($id) . '/parent');
    }

    /**
     * Get the parent relationship of a post
     *
     * @see https://developer.youthweb.net/api_endpoint_posts.html#beziehungen
     *
     * @param string $id
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function showParentRelationship(string $id)
    {
        return $this->client->get('/posts/' . strval($id) . '/relationships/parent');
    }
}
