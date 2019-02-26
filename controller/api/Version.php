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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\controller\api;

use common_exception_RestApi;
use tao_actions_CommonRestModule;

/**
 * @author Mikhail Kamarouski, <mikhail.kamarouski@1pt.com>
 */
class Version extends tao_actions_CommonRestModule
{
    /**
     * @OA\Get(
     *      path="/tao/api/Version",
     *      operationId="getVersion",
     *      tags={"platform"},
     *      @OA\Response(
     *         response="200",
     *         description="Returns current platform version",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "version": "3.3.0-sprint97",
     *                 }
     *             )
     *         ),
     *     ),
     * )
     * @param null $uri
     * @return void
     */
    public function get($uri = null)
    {
        return $this->returnJson([
            'version' => TAO_VERSION
        ]);
    }

    public function put($uri)
    {
        $this->returnFailure(new common_exception_RestApi('Not implemented'));
    }

    /**
     * @param string $uri
     * @return void
     * @throws \common_exception_NotImplemented
     */
    public function delete($uri = null)
    {
        $this->returnFailure(new common_exception_RestApi('Not implemented'));
    }

    /**
     * @return void
     * @throws \common_exception_NotImplemented
     */
    public function post()
    {
        $this->returnFailure(new common_exception_RestApi('Not implemented'));
    }

}
