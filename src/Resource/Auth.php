<?php

namespace Youthweb\Api\Resource;

use Youthweb\Api\JsonObject;

/**
 * Auth
 *
 * @link http://docs.youthweb.apiary.io/#reference/auth
 */
final class Auth extends AbstractResource
{
	/**
	 * Get the Bearer Token
	 *
	 * @link http://docs.youthweb.apiary.io/#reference/auth
	 *
	 * @return array the stats
	 */
	public function getBearerToken()
	{
		$cache_item_key = $this->client->buildCacheKey('bearer_token');

		$cache_item = $this->client->getCacheProvider()
			->getItem($cache_item_key);

		if ( ! $cache_item->isHit() )
		{
			$meta = new JsonObject;
			$meta->username = $this->client->getUserCredential('username');
			$meta->token_secret = $this->client->getUserCredential('token_secret');

			$body = new JsonObject;
			$body->meta = $meta;

			$document = $this->postUnauthorized('/auth/token', $body);

			if ( $document->has('meta.token') and $document->has('meta.token_type') )
			{
				$bearer_token = $document->get('meta.token_type') . ' ' . $document->get('meta.token');

				$cache_item->set($bearer_token);

				$this->client->getCacheProvider()->saveDeferred($cache_item);
			}
		}

		return $cache_item->get();
	}
}
