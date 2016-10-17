<?php

namespace Youthweb\Api\Resource;

/**
 * Auth
 *
 * @link http://docs.youthweb.apiary.io/#reference/auth
 */
interface AuthInterface extends ResourceInterface
{
	/**
	 * Get the Bearer Token
	 *
	 * @link http://docs.youthweb.apiary.io/#reference/auth
	 *
	 * @return array the stats
	 */
	public function getBearerToken();
}
