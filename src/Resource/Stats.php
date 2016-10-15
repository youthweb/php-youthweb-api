<?php

namespace Youthweb\Api\Resource;

/**
 * Get the stats
 *
 * @link docs.youthweb.apiary.io/#reference/stats
 */
final class Stats extends AbstractResource
{
	/**
	 * Get the stats
	 *
	 * @link docs.youthweb.apiary.io/#reference/stats
	 *
	 * @param string $id
	 * @return array the stats
	 */
	public function show($id)
	{
		$ids = array(
			'account' => 'account',
			'forum'   => 'forum',
			'groups'  => 'groups',
		);

		if ( ! isset($ids[$id]) )
		{
			throw new \InvalidArgumentException('The ressource id "' . $id . '" does not exists.');
		}

		return $this->getUnauthorized('/stats/' . $ids[$id]);
	}
}
