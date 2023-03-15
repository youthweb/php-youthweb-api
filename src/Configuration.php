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

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Youthweb\Api\Authentication\Authenticator;
use Youthweb\Api\Authentication\NativeAuthenticator;
use Youthweb\Api\Authentication\Psr17RequestFactoryAdapter;
use Youthweb\Api\Authentication\Psr18GuzzleAdapter;
use Youthweb\Api\Cache\NullCacheItemPool;
use Youthweb\OAuth2\Client\Provider\Youthweb as Oauth2Provider;

/**
 * Configuration class
 */
final class Configuration
{
    public static function createUnauthorized(): static
    {
        return self::create('', '', '', [], '');
    }

    public static function create(
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        array $scope,
        string $resourceOwnerId,
    ): static {
        $scopeValues = [];

        foreach ($scope as $value) {
            $scopeValues[] = strval($value);
        }

        return new self(
            $clientId,
            $clientSecret,
            $redirectUrl,
            $scopeValues,
            $resourceOwnerId,
            '0.20',
            'https://api.youthweb.net',
            'https://youthweb.net',
        );
    }

    private HttpClientInterface $httpClient;
    private Authenticator $authenticator;
    private CacheItemPoolInterface $cache;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private UriFactoryInterface $uriFactory;
    private ResourceFactoryInterface $resourceFactory;

    /**
     * @param string[] $scope
     */
    private function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUrl,
        private array $scope,
        private string $resourceOwnerId,
        private string $apiVersion,
        private string $apiDomain,
        private string $authDomain,
    ) {
    }

    public function setApiVersion(string $apiVersion): void
    {
        $this->apiVersion = $apiVersion;
    }

    public function setApiDomain(string $apiDomain): void
    {
        $this->apiDomain = rtrim($apiDomain, '/');
    }

    public function setAuthDomain(string $authDomain): void
    {
        $this->authDomain = rtrim($authDomain);
    }

    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function setAuthenticator(Authenticator $authenticator): void
    {
        $this->authenticator = $authenticator;
    }

    public function setCacheItemPool(CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function setRequestFactory(RequestFactoryInterface $requestFactory): void
    {
        $this->requestFactory = $requestFactory;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory): void
    {
        $this->streamFactory = $streamFactory;
    }

    public function setUriFactory(UriFactoryInterface $uriFactory): void
    {
        $this->uriFactory = $uriFactory;
    }

    public function setResourceFactory(ResourceFactoryInterface $resourceFactory): void
    {
        $this->resourceFactory = $resourceFactory;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function getResourceOwnerId(): string
    {
        return $this->resourceOwnerId;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getApiDomain(): string
    {
        return $this->apiDomain;
    }

    public function getHttpClient(): HttpClientInterface
    {
        if (! isset($this->httpClient)) {
            $this->httpClient = new GuzzleHttpClient([]);
        }

        return $this->httpClient;
    }

    public function getAuthenticator(): Authenticator
    {
        if (! isset($this->authenticator)) {
            $this->authenticator = new NativeAuthenticator(new Oauth2Provider(
                [
                    'clientId'     => $this->clientId,
                    'clientSecret' => $this->clientSecret,
                    'redirectUrl'  => $this->redirectUrl,
                    'apiVersion'   => $this->apiVersion,
                    'apiDomain'    => $this->apiDomain,
                    'domain'       => $this->authDomain,
                ],
                [
                    'httpClient'     => new Psr18GuzzleAdapter($this->getHttpClient()),
                    'requestFactory' => Psr17RequestFactoryAdapter::createFromPsr17(
                        $this->getRequestFactory(),
                        $this->getStreamFactory(),
                        $this->getUriFactory(),
                    ),
                ],
            ));
        }
        return $this->authenticator;
    }

    public function getCacheItemPool(): CacheItemPoolInterface
    {
        if (! isset($this->cache)) {
            $this->cache = new NullCacheItemPool();
        }

        return $this->cache;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        if (! isset($this->requestFactory)) {
            $this->requestFactory = new HttpFactory();
        }

        return $this->requestFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        if (! isset($this->streamFactory)) {
            $this->streamFactory = new HttpFactory();
        }

        return $this->streamFactory;
    }

    public function getUriFactory(): UriFactoryInterface
    {
        if (! isset($this->uriFactory)) {
            $this->uriFactory = new HttpFactory();
        }

        return $this->uriFactory;
    }

    public function getResourceFactory(): ResourceFactoryInterface
    {
        if (! isset($this->resourceFactory)) {
            $this->resourceFactory = new ResourceFactory();
        }

        return $this->resourceFactory;
    }
}
