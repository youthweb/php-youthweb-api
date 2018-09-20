<?php

namespace Youthweb\Api\Resource;

@trigger_error(__NAMESPACE__ . '\Auth is deprecated since version 0.5 and will be removed in 1.0. Use OAuth 2.0 instead', E_USER_DEPRECATED);

use DateInterval;
use Youthweb\Api\JsonObject;

/**
 * Auth
 *
 * @see http://docs.youthweb.apiary.io/#reference/auth
 */
final class Auth implements AuthInterface
{
    use ClientTrait;

    /**
     * Get the Bearer Token
     *
     * @deprecated Since Youthweb-API 0.6
     * @see http://docs.youthweb.apiary.io/#reference/auth
     *
     * @throws InvalidArgumentException if no user or client credentials are set
     *
     * @return string The Bearer token incl. type e.g. "Bearer jcx45..."
     */
    public function getBearerToken()
    {
        if ($this->client->getUserCredential('username') === '' or $this->client->getUserCredential('token_secret') === '') {
            throw new \InvalidArgumentException;
        }

        $cache_item = $this->client->getCacheItem('bearer_token');

        if (! $cache_item->isHit()) {
            $meta = new JsonObject;
            $meta->username = $this->client->getUserCredential('username');
            $meta->token_secret = $this->client->getUserCredential('token_secret');

            $body = new JsonObject;
            $body->meta = $meta;

            $document = $this->client->postUnauthorized('/auth/token', ['body' => $body]);

            if ($document->has('meta.token') and $document->has('meta.token_type') and $document->get('meta.token_type') === 'Bearer') {
                $cache_item->set($document->get('meta.token'));
                $cache_item->expiresAfter(new DateInterval('PT1H'));

                $this->client->saveCacheItem($cache_item);
            }
        }

        return $cache_item->get();
    }
}
