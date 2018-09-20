<?php

namespace Youthweb\Api\Resource;

@trigger_error(__NAMESPACE__ . '\Auth is deprecated since version 0.5 and will be removed in 1.0. Use OAuth 2.0 instead', E_USER_DEPRECATED);

/**
 * Auth
 *
 * @see http://docs.youthweb.apiary.io/#reference/auth
 */
interface AuthInterface extends ResourceInterface
{
    /**
     * Get the Bearer Token
     *
     * @see http://docs.youthweb.apiary.io/#reference/auth
     *
     * @return array the stats
     */
    public function getBearerToken();
}
