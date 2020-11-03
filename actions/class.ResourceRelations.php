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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\http\Controller;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\resources\relation\exception\NestedClassLimitExceededException;
use oat\tao\model\resources\relation\FindAllQuery;
use oat\tao\model\resources\relation\service\ResourceRelationServiceInterface;
use oat\tao\model\resources\relation\service\ResourceRelationServiceProxy;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class tao_actions_ResourceRelations extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use HttpJsonResponseTrait;
    use OntologyAwareTrait;

    public function index(): void
    {
        $queryParams = $this->getPsrRequest()->getQueryParams();

        try {
            $this->setSuccessJsonResponse(
                [
                    'relations' => $this->getResourceRelationService()->findRelations(
                        new FindAllQuery(
                            $queryParams['sourceId'] ?? null,
                            $queryParams['classId'] ?? null,
                            $queryParams['type'] ?? null
                        )
                    )->jsonSerialize()
                ]
            );
        } catch (NestedClassLimitExceededException $exception) {
            $this->setErrorJsonResponse($exception->getMessage(), $exception->getCode());
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage());
        }
    }

    private function getResourceRelationService(): ResourceRelationServiceInterface
    {
        return $this->getServiceLocator()->get(ResourceRelationServiceProxy::SERVICE_ID);
    }
}
