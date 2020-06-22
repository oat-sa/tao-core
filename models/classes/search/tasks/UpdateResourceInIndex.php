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
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\Search;
use oat\tao\model\TaoOntology;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use \common_report_Report as Report;

/**
 * Class UpdateResourceInIndex
 *
 * @author Ilya Yarkavets <ilya@taotesting.com>
 * @package oat\tao\model\search\tasks
 */
class UpdateResourceInIndex implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;

    public function __invoke($params): Report
    {
        if (empty($params) || empty($params[0])) {
            throw new \common_exception_MissingParameter();
        }

        $createdResource = $this->getResource($params[0]);
        $resourceType = $this->getResourceRootType($createdResource);

        /** @var IndexService $indexService */
        $indexService = $this->getServiceLocator()->get(IndexService::SERVICE_ID);

        $factory = $indexService->getDocumentBuilderFactory();

        $documentBuilder = $factory->getDocumentBuilderByResourceType($resourceType);

        $indexDocument = $documentBuilder->createDocumentFromResource($createdResource, $resourceType);

        /** @var Search $searchService */
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);

        $numberOfIndexed = $searchService->index([$indexDocument]);

        if ($numberOfIndexed === 0) {
            $type = Report::TYPE_ERROR;
            $message = "Zero documents were added/updated in index.";
        } elseif ($numberOfIndexed === 1) {
            $type = Report::TYPE_SUCCESS;
            $message = "Document in index was successfully updated.";
        } else {
            $type = Report::TYPE_WARNING;
            $message = "The number or indexed documents is different than the expected total";
        }

        return new Report($type, $message);
    }
    
    private function getResourceRootType(\core_kernel_classes_Resource $resource): string
    {
        if ($resource->isInstanceOf($this->getClass(TaoOntology::CLASS_URI_ITEM))) {
            $rootClass = $this->getClass(TaoOntology::CLASS_URI_ITEM)->getUri();
        } elseif ($resource->isInstanceOf($this->getClass(TaoOntology::CLASS_URI_TEST))) {
            $rootClass = $this->getClass(TaoOntology::CLASS_URI_TEST)->getUri();
        } elseif ($resource->isInstanceOf($this->getClass(TaoOntology::CLASS_URI_SUBJECT))) {
            $rootClass = $this->getClass(TaoOntology::CLASS_URI_SUBJECT)->getUri();
        } elseif ($resource->isInstanceOf($this->getClass(TaoOntology::CLASS_URI_GROUP))) {
            $rootClass = $this->getClass(TaoOntology::CLASS_URI_GROUP)->getUri();
        } elseif ($resource->isInstanceOf($this->getClass(TaoOntology::CLASS_URI_DELIVERY))) {
            $rootClass = $this->getClass(TaoOntology::CLASS_URI_DELIVERY)->getUri();
        } else {
            $rootClass = current(array_keys($resource->getTypes()));
        }
        
        return $rootClass;
    }
}
