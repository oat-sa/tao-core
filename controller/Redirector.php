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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\controller;

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\routing\ResourceUrlBuilder;

class Redirector extends \tao_actions_CommonModule
{
    const PARAMETER_TASK_ID = 'taskId';

    use OntologyAwareTrait;

    /**
     * Redirect to a resource generated in a task.
     */
    public function redirectTaskToInstance()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_TASK_ID)) {
            throw new \common_exception_MissingParameter(self::PARAMETER_TASK_ID, $this->getRequestURI());
        }

        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

        $entity = $taskLogService->getByIdAndUser(
            $this->getRequestParameter(self::PARAMETER_TASK_ID),
            $this->getSession()->getUserUri(),
            true // in Sync mode, task is archived straightaway
        );

        $uri = $entity->getResourceUriFromReport();

        /** @var ResourceUrlBuilder $urlBuilder */
        $urlBuilder = $this->getServiceLocator()->get(ResourceUrlBuilder::SERVICE_ID);
        $resource = $this->getResource($uri);

        if ($resource->exists()) {
            return $this->returnJson([
                'success' => true,
                'data'    => $urlBuilder->buildUrl($resource)
            ]);
        }

        return $this->returnJson([
            'success' => false,
            'errorCode' => 202,
            'errorMessage' => __('The requested resource does not exist or has been deleted')
        ], 202);
    }
}
