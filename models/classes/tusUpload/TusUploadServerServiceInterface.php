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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\tusUpload;

use Psr\Http\Message\ServerRequestInterface;

interface TusUploadServerServiceInterface extends TusInterface
{
    const SERVICE_ID = 'tao/tusUploadServer';

    /** @param ServerRequestInterface $request */
    public function setRequest($request);

    /**
     * Get request.
     *
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * Unique identifier of uploading.
     * @return string
     */
    public function getUploadKey();

    /**
     * Main entrypoint for server uploading.
     * @param ServerRequestInterface $request
     * @return array
     * [
     *   'status' => Response status,
     *   'content'=> Response body,
     *   'headers'=> Response headers[],
     *   'completed'=> Upload completion status
     * ]
     */
    public function serve(ServerRequestInterface $request);

}
