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

namespace Youthweb\Api\Authentication;

use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Youthweb\OAuth2\Client\Provider\Youthweb as Oauth2Provider;

/**
 * Interface for authenticator
 */
class NativeAuthenticator implements Authenticator
{
    private string $apiVersion = '0.20';

    private string $apiDomain = 'https://api.youthweb.net';

    private string $authDomain = 'https://youthweb.net';

    private string $clientId = '';

    private string $clientSecret = '';

    private string $redirectUrl = '';

    private Oauth2Provider $oauth2Provider;

    /**
     * Constructs the Authenticator
     *
     * @param array $options       an array of options to set on the client.
     *                             Options include `apiDomain`, `authDomain`, `clientId`,
     *                             `clientSecret` and `redirectUrl`
     * @param array $collaborators An array of collaborators that may be used to
     *                             override this provider's default behavior. Collaborators include
     *                             `oauth2Provider`.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $allowed_options = [
            'apiVersion',
            'apiDomain',
            'authDomain',
            'clientId',
            'clientSecret',
            'redirectUrl',
        ];

        foreach ($options as $option => $value) {
            if (in_array($option, $allowed_options)) {
                $value = strval($value);

                $this->{$option} = $value;
            }
        }

        if (empty($collaborators['oauth2Provider'])) {
            $collaborators['oauth2Provider'] = new Oauth2Provider([
                'clientId'     => $this->clientId,
                'clientSecret' => $this->clientSecret,
                'redirectUri'  => $this->redirectUrl,
                'apiVersion'   => $this->apiVersion,
                'apiDomain'    => $this->apiDomain,
                'domain'       => $this->authDomain,
            ]);
        }

        $this->setOauth2Provider($collaborators['oauth2Provider']);
    }

    /**
     * get the authorization url
     *
     * @param array $options
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->getOauth2Provider()->getAuthorizationUrl($options);
    }

    /**
     * get a random state
     *
     * @return string Could be empty
     */
    public function getState(): string
    {
        $state = $this->getOauth2Provider()->getState();

        // Workaround, if no state was generated so far
        if ($state === '') {
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
    public function getAccessToken(string $grant, array $params = []): AccessTokenInterface
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
     * @param Oauth2Provider $oauth2Provider the oauth2 provider
     */
    private function setOauth2Provider(Oauth2Provider $oauth2Provider): void
    {
        $this->oauth2Provider = $oauth2Provider;
    }

    /**
     * Get the oauth2 provider
     *
     * @return Oauth2Provider the oauth2 provider
     */
    private function getOauth2Provider(): Oauth2Provider
    {
        return $this->oauth2Provider;
    }
}
