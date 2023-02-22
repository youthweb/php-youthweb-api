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

namespace Youthweb\Api\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ErrorResponseException extends RuntimeException
{
    public static function fromResponse(ResponseInterface $response, string $message): self
    {
        $e = new self($message, $response->getStatusCode());
        $e->response = $response;

        return $e;
    }

    private ResponseInterface $response;

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
