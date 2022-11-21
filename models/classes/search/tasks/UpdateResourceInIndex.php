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
 * Copyright (c) 2018-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_Exception;
use common_exception_InconsistentData;
use common_exception_MissingParameter;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\reporting\Report;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\taoAdvancedSearch\model\Index\Service\ResourceReferencesService;
use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Exception;

/**
 * @author Ilya Yarkavets <ilya@taotesting.com>
 */
class UpdateResourceInIndex implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    /** @var string[] */
    private $resourceUris = [];

    /**
     * @throws common_Exception
     * @throws common_exception_InconsistentData
     * @throws common_exception_MissingParameter
     */
    public function __invoke($params): Report
    {
        if (empty($params[0])) {
            throw new common_exception_MissingParameter();
        }

        $resourceUris = is_array($params[0]) ? $params[0] : [$params[0]];

        return $this->getReport(
            $this->getSearchProxy()->index(
                $this->getIndexDocuments($resourceUris)
            )
        );
    }

    /**
     * @throws common_exception_InconsistentData
     * @throws common_Exception
     */
    private function getIndexDocuments(array $resourceUris): array
    {
        $documents = [];

        foreach ($resourceUris as $resourceUri) {
            $this->resourceUris[] = $resourceUri;
            $documents[] = $this->getDocumentFor($resourceUri);
        }

        return $documents;
    }

    /**
     * @throws common_exception_InconsistentData
     * @throws common_Exception
     * @throws Exception
     */
    private function getDocumentFor(string $resourceUri): IndexDocument
    {
        $resource = $this->getResource($resourceUri);
        $document =  $this->getDocumentBuilder()->createDocumentFromResource(
            $resource
        );

        return new IndexDocument(
            $document->getId(),
            $this->getBody($resource, $document),
            $document->getIndexProperties(),
            $document->getDynamicProperties(),
            $document->getAccessProperties()
        );
    }

    private function getBody(
        core_kernel_classes_Resource $resource,
        IndexDocument $document
    ): array {
        if ($this->hasReferencesService()) {
            return $this->getReferencesService()->getBodyWithReferences(
                $resource,
                $document
            );
        }

        return $document->getBody();
    }

    private function getReport($numberOfIndexed): Report
    {
        $expectedIndexations = count($this->resourceUris);
        $resourceUris = implode(',', $this->resourceUris);

        if ($numberOfIndexed === $expectedIndexations) {
            $message = sprintf('Document(s) "%s" successfully indexed', $resourceUris);

            $this->logInfo($message);

            return Report::createSuccess($message);
        }

        if ($numberOfIndexed === 0) {
            $message = sprintf('Expecting document(s) to be indexed (got zero) for ID(s) "%s"', $resourceUris);

            $this->logError($message);

            return Report::createError($message);
        }

        $message = sprintf(
            'Expecting "%s" document(s) to be indexed (got %s) for ID(s) "%s"',
            $expectedIndexations,
            $numberOfIndexed,
            $resourceUris
        );

        $this->logWarning($message);

        return Report::createWarning($message);
    }

    private function getDocumentBuilder(): IndexDocumentBuilder
    {
        $documentBuilder = $this->getIndexService()->getDocumentBuilder();
        $documentBuilder->setServiceLocator($this->getServiceLocator());

        return $documentBuilder;
    }

    private function getSearchProxy(): SearchProxy
    {
        return $this->getService(SearchProxy::SERVICE_ID);
    }

    private function getIndexService(): IndexService
    {
        return $this->getService(IndexService::SERVICE_ID);
    }

    private function getReferencesService(): ResourceReferencesService
    {
        return $this->getService(ResourceReferencesService::class);
    }

    private function hasReferencesService(): bool
    {
        return $this->getContainer()->has(ResourceReferencesService::class);
    }

    private function getService(string $serviceId)
    {
        return $this->getContainer()->get($serviceId);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceLocator()->getContainer();
    }
}
