<?php

declare(strict_types=1);
/*
 * PHP Youthweb API is an object-oriented wrapper for PHP of the Youthweb API.
 * Copyright (C) 2015-2019  Youthweb e.V.  https://youthweb.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Youthweb\Api;

use InvalidArgumentException;
use Youthweb\OAuth2\Client\Provider\Youthweb as Oauth2Provider;

/**
 * Interface for authenticator
 */
class YouthwebAuthenticator implements AuthenticatorInterface
{
    /**
     * @var string
     */
    private $api_version = '0.15';

    /**
     * @var string
     */
    private $api_domain = 'https://api.youthweb.net';

    /**
     * @var string
     */
    private $auth_domain = 'https://youthweb.net';

    /**
     * @var string
     */
    private $client_id;

    /**
     * @var string
     */
    private $client_secret;

    /**
     * @var string
     */
    private $redirect_url = '';

    /**
     * @var Oauth2Provider
     */
    private $oauth2_provider;

    /**
     * Constructs the Authenticator
     *
     * @param array $options       an array of options to set on the client.
     *                             Options include `api_domain`, `auth_domain`, `client_id`,
     *                             `client_secret` and `redirect_url`
     * @param array $collaborators An array of collaborators that may be used to
     *                             override this provider's default behavior. Collaborators include
     *                             `oauth2_provider`.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $allowed_options = [
            'api_version',
            'api_domain',
            'auth_domain',
            'client_id',
            'client_secret',
            'redirect_url',
        ];

        foreach ($options as $option => $value) {
            if (in_array($option, $allowed_options)) {
                $value = strval($value);

                $this->{$option} = $value;
            }
        }

        if (empty($collaborators['oauth2_provider'])) {
            $collaborators['oauth2_provider'] = new Oauth2Provider([
                'clientId'     => $this->client_id,
                'clientSecret' => $this->client_secret,
                'redirectUri'  => $this->redirect_url,
                'apiVersion'   => $this->api_version,
                'apiDomain'    => $this->api_domain,
                'domain'       => $this->auth_domain,
            ]);
        }

        $this->setOauth2Provider($collaborators['oauth2_provider']);
    }

    /**
     * get the authorization url
     *
     * @param array $options
     *
     * @return string
     */
    public function getAuthorizationUrl(array $options = [])
    {
        return $this->getOauth2Provider()->getAuthorizationUrl($options);
    }

    /**
     * get a random state
     *
     * @return string
     */
    public function getState()
    {
        $state = $this->getOauth2Provider()->getState();

        // Workaround, if no state was generated so far
        if ($state === null) {
            // get the url so a new state will be generated
            $this->getAuthorizationUrl();

            // get the generated state
            $state = $this->getOauth2Provider()->getState();
        }

        return $state;
    }

    /**
     * Get an access token
     *
     * @param string $grant  e.g. `authorization_code`
     * @param array  $params for authorization code:
     *                       [
     *                       'code' => 'authorization_code_from_callback_url...',
     *                       'state' => 'state_from_callback_url_for_csrf_protection',
     *                       ]
     *
     * @throws InvalidArgumentException If a wrong state or grant was set
     */
    public function getAccessToken(string $grant, array $params = [])
    {
        $allowed_grants = [
            'authorization_code',
        ];

        if (! in_array($grant, $allowed_grants)) {
            throw new InvalidArgumentException('Unsupported grant "' . strval($grant) . '"');
        }

        return $this->getOauth2Provider()->getAccessToken($grant, $params);
    }

    /**
     * Set a oauth2 provider
     *
     * @param Oauth2Provider $oauth2_provider the oauth2 provider
     */
    private function setOauth2Provider(Oauth2Provider $oauth2_provider): void
    {
        $this->oauth2_provider = $oauth2_provider;
    }

    /**
     * Get the oauth2 provider
     *
     * @return Oauth2Provider the oauth2 provider
     */
    private function getOauth2Provider(): Oauth2Provider
    {
        return $this->oauth2_provider;
    }
}
