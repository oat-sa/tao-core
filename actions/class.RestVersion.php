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

/**
 * Class tao_actions_RestVersion
 * @author Mikhail Kamarouski, <mikhail.kamarouski@1pt.com>
 * @OA\Info(
 *     title="Provides platform version information",
 *     version="0.1.0",
 *     @OA\License(name="GPL-2.0-only")
 *    )
 */
class tao_actions_RestVersion extends tao_actions_RestClass
{
    /**
     * @OA\Get(
     *      path="/tao/RestVersion",
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
     */
    public function index()
    {
        if ($this->getRequest()->isGet()) {
            return $this->returnJson([
                'version' => TAO_VERSION
            ]);
        }
        throw new common_exception_RestApi();
    }
}
