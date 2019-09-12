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

use oat\tao\model\tusUpload\TusUploadServerService;
use function GuzzleHttp\Psr7\stream_for;

abstract class tao_actions_TusUpload extends \tao_actions_CommonModule
{

    /**
     * Main entry point for all requests.
     */
    public function index()
    {
        /** @var TusUploadServerService $tusUploadService */
        $tusUploadService = $this->getServiceLocator()->get(TusUploadServerService::SERVICE_ID);
        $responseData = $tusUploadService->serve($this->getPsrRequest());

        if ($responseData['uploadComplete']) {
            $this->completeAction($responseData);
        }
        $this->prepareResponse($responseData['content'], $responseData['status'], $responseData['headers']);
    }

    /**
     *
     * @param $content
     * @param int $status
     * @param array $headers
     */
    protected function prepareResponse($content, $status = 200, $headers = [])
    {
        $response = $this->getPsrResponse();

        if (is_array($content)) {
            $content = json_encode($content);
        }
        foreach ($headers as $key => $value) {
            $response->withHeader($key, $value);
        }
        $response->withBody(stream_for($content))->withStatus($status);
    }

    /**
     *  Additional method to implement TUS protocol in TAO.
     *  Generate and send unique key that will be used instead of `path/filename`.
     *
     */
    public function getKey()
    {
        //not implemented yet.
    }

    /**
     * @param array $responseData
     * @return mixed
     */
    abstract protected function completeAction($responseData);
}
