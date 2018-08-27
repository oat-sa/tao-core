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

namespace oat\tao\model\session\restSessionFactory\builder;

use oat\tao\model\session\restSessionFactory\SessionBuilder;
use \oat\oatbox\user\LoginFailedException;

class HttpBasicAuthBuilder implements SessionBuilder
{
    /**
     * In RestController context, always applicable
     *
     * @param \common_http_Request $request
     * @return bool
     */
    public function isApplicable(\common_http_Request $request)
    {
        return true;
    }

    /**
     * Create a rest session based on request credentials
     *
     * @param \common_http_Request $request
     * @return \common_session_RestSession|\common_session_Session
     * @throws LoginFailedException
     */
    public function getSession(\common_http_Request $request)
    {
        $authAdapter = new \tao_models_classes_HttpBasicAuthAdapter($request);
        $user = $authAdapter->authenticate();
        return new \common_session_RestSession($user);
    }

}