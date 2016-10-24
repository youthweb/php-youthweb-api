<?php

namespace Youthweb\Api\Resource;

use Youthweb\Api\JsonObject;

/**
 * Auth
 *
 * @link http://docs.youthweb.apiary.io/#reference/auth
 */
final class Auth implements AuthInterface
{
	use ClientTrait;

	/**
	 * Get the Bearer Token
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @link http://docs.youthweb.apiary.io/#reference/auth
	 *
	 * @return string The Bearer token incl. type e.g. "Bearer jcx45..."
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

			$document = $this->client->postUnauthorized('/auth/token', ['body' => $body]);

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
