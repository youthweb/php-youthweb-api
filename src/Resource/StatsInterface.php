<?php

namespace Youthweb\Api\Resource;

/**
 * Get the stats
 *
 * @link docs.youthweb.apiary.io/#reference/stats
 */
interface StatsInterface extends ResourceInterface
{
	/**
	 * Get the stats
	 *
	 * @link docs.youthweb.apiary.io/#reference/stats
	 *
	 * @param string $id Can be `account`, `forum` or `groups`
	 * @return array the stats
	 */
	public function show($id);
}
