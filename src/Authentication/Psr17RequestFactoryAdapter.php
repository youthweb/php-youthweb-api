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

use League\OAuth2\Client\Tool\RequestFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Adapter for PSR-17 Request factory to implement league/oauth2-client RequestFactory
 *
 * @internal
 */
final class Psr17RequestFactoryAdapter extends RequestFactory
{
    public static function createFromPsr17(
        RequestFactoryInterface $psr17RequestFactory,
        StreamFactoryInterface $psr17StreamFactory,
        UriFactoryInterface $psr17UriFactory,
    ): self {
        $factory = new self();
        $factory->psr17RequestFactory = $psr17RequestFactory;
        $factory->psr17StreamFactory = $psr17StreamFactory;
        $factory->psr17UriFactory = $psr17UriFactory;

        return $factory;
    }

    private RequestFactoryInterface $psr17RequestFactory;

    private StreamFactoryInterface $psr17StreamFactory;

    private UriFactoryInterface $psr17UriFactory;

    /**
     * Creates a PSR-7 Request instance.
     *
     * @param  null|string $method HTTP method for the request.
     * @param  null|string $uri URI for the request.
     * @param  array $headers Headers for the message.
     * @param  string|resource|StreamInterface $body Message body.
     * @param  string $version HTTP protocol version.
     *
     * @return RequestInterface
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        // return new Request($method, $uri, $headers, $body, $version);
        $request = $this->psr17RequestFactory->createRequest(
            strval($method),
            $this->psr17UriFactory->createUri(strval($uri)),
        );
        $request = $request->withProtocolVersion($version);

        foreach ($headers as $key => $value) {
            $request = $request->withAddedHeader($key, $value);
        }

        if ($body !== '' && $body !== null) {
            if (is_string($body)) {
                $request = $request->withBody(
                    $this->psr17StreamFactory->createStream($body)
                );
            } elseif (is_resource($body)) {
                $request = $request->withBody(
                    $this->psr17StreamFactory->createStreamFromResource($body)
                );
            } elseif ($body instanceof StreamInterface) {
                $request = $request->withBody($body);
            }
        }

        return $request;
    }
}
