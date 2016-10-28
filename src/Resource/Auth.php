<?php

namespace Youthweb\Api\Resource;

use DateInterval;
use Youthweb\Api\Exception\MissingCredentialsException;
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
	 * @throws MissingCredentialsException If no user or client credentials are set
	 *
	 * @return string The Bearer token incl. type e.g. "Bearer jcx45..."
	 */
	public function getBearerToken()
	{
		if ( $this->client->getUserCredential('username') === '' or $this->client->getUserCredential('token_secret') === '' )
		{
			throw new MissingCredentialsException;
		}

		$cache_item = $this->client->getCacheItem('bearer_token');

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
				$cache_item->expiresAfter(new DateInterval('PT1H'));

				$this->client->saveCacheItem($cache_item);
			}
		}

		return $cache_item->get();
	}
}
