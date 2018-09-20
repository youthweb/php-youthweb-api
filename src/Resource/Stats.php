<?php

namespace Youthweb\Api\Resource;

/**
 * Get the stats
 *
 * @see docs.youthweb.apiary.io/#reference/stats
 */
final class Stats implements StatsInterface
{
    use ClientTrait;

    /**
     * Get the stats
     *
     * @see docs.youthweb.apiary.io/#reference/stats
     *
     * @param string $id
     *
     * @return array the stats
     */
    public function show($id)
    {
        $ids = [
            'account' => 'account',
            'forum'   => 'forum',
            'groups'  => 'groups',
        ];

        if (! isset($ids[$id])) {
            throw new \InvalidArgumentException('The ressource id "' . $id . '" does not exists.');
        }

        return $this->client->getUnauthorized('/stats/' . $ids[$id]);
    }
}
