<?php

namespace Youthweb\Api\Resource;

use Youthweb\Api\JsonObject;

/**
 * Auth
 *
 * @link http://docs.youthweb.apiary.io/#reference/auth
 */
class Auth extends AbstractResource
{
	/**
	 * Get the JWT
	 *
	 * @link http://docs.youthweb.apiary.io/#reference/auth
	 *
	 * @return array the stats
	 */
	public function getJwt()
	{
		$jwt_cache_item_key = $this->client->buildCacheKey('jwt');

		$jwt_cache_item = $this->client->getCacheProvider()
			->getItem($jwt_cache_item_key);

		if ( ! $jwt_cache_item->isHit() )
		{
			$meta = new JsonObject;
			$meta->username = $this->client->getUserCredential('username');
			$meta->token_secret = $this->client->getUserCredential('token_secret');

			$body = new JsonObject;
			$body->meta = $meta;

			$document = $this->post('/auth/token', $body);

			if ( $document->has('meta.token') )
			{
				$jwt_cache_item->set($document->get('meta.token'));

				$this->client->getCacheProvider()->saveDeferred($jwt_cache_item);
			}
		}

		return $jwt_cache_item->get();
	}
}
