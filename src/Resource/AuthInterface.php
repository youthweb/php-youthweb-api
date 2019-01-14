<?php
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
