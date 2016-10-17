<?php

namespace Youthweb\Api\Resource;

/**
 * Users
 *
 * @link docs.youthweb.apiary.io/#reference/users
 */
interface UsersInterface extends ResourceInterface
{
	/**
	 * Get a user
	 *
	 * @link docs.youthweb.apiary.io/#reference/users
	 *
	 * @param string $id
	 * @return array the stats
	 */
	public function show($id);
}
