<?php

namespace Youthweb\Api\Resource;

/**
 * Users
 *
 * @see docs.youthweb.apiary.io/#reference/users
 */
interface UsersInterface extends ResourceInterface
{
    /**
     * Get a user
     *
     * @see http://docs.youthweb.apiary.io/#reference/users/user
     *
     * @param string $id
     *
     * @return Art4\JsonApiClient\Document
     */
    public function show($id);

    /**
     * Get the resource owner
     *
     * @see http://docs.youthweb.apiary.io/#reference/users/me
     *
     * @return Art4\JsonApiClient\Document
     */
    public function showMe();
}
