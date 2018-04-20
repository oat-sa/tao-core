<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\session\restSessionFactory;

use oat\oatbox\user\LoginFailedException;
use oat\tao\model\routing\Resolver;

interface SessionBuilder
{
    /**
     * Check if the current builder is able to load the session.
     *
     * The $request and $resolver is used to know the context
     *
     * @param \common_http_Request $request
     * @return boolean
     */
    public function isApplicable(\common_http_Request $request);

    /**
     * Construct the session based on request
     *
     * @param \common_http_Request $request
     * @return \common_session_Session
     * @throws LoginFailedException
     */
    public function getSession(\common_http_Request $request);
}