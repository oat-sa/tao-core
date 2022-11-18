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
use common_exception_MissingParameter;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\reporting\Report;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\TaoOntology;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * @author Ilya Yarkavets <ilya@taotesting.com>
 */
class UpdateResourceInIndex implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    /** @var array  */
    private $resourceUris = [];

    /**
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

    private function getIndexDocuments(array $resourceUris): array
    {
        $documents = [];
        $builder = $this->getIndexDocumentBuilder();

        foreach ($resourceUris as $resourceUri) {
            $this->resourceUris[] = $resourceUri;

            $resource = $this->getResource($resourceUri);

            if ($this->shouldBeHandled($resource)) {
                $documents[] = $this->getDocumentFor($builder, $resource);
            }
        }

        return $documents;
    }

    /**
     * As this Action is triggered from the ResourceWatcher, we check here if
     * the resource is from a known type and misses the referenced_resources
     * data: If that's the case, we need to recompute its value (as the
     * document builder does not include it).
     *
     * @param core_kernel_classes_Resource $resource
     * @return bool
     */
    private function shouldBeHandled(
        core_kernel_classes_Resource $resource
    ): bool {
        $isItem = $this->isOfType(TaoOntology::CLASS_URI_ITEM, $resource);

        if ($isItem  && !$resource->isClass()) {
            $results = iterator_to_array(
                $this->getAdvancedSearch()->queryByDocumentId(
                    $resource->getUri(),
                    'items'
                )
            );

            // Check if we need to (re)add the referenced resources
            //
            if (!isset($results[0]['referenced_resources'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws common_exception_InconsistentData
     * @throws common_Exception
     */
    private function getDocumentFor(
        IndexDocumentBuilder $documentBuilder,
        core_kernel_classes_Resource $resource
    ): IndexDocument {
        $document =  $documentBuilder->createDocumentFromResource($resource);

        return new IndexDocument(
            $document->getId(),
            $this->getBody($document, $resource),
            $document->getIndexProperties(),
            $document->getDynamicProperties(),
            $document->getAccessProperties()
        );
    }

    /**
     * @throws common_Exception
     */
    private function getBody(
        IndexDocument $document,
        core_kernel_classes_Resource $resource
    ): array {
        $body = $document->getBody();

        if (!$this->isAdvancedSearchEnabled()) {
            // No advanced search means nothing to do here
            return $body;
        }

        $references = [];
        $this->getLogger()->critical("TODO getBody should compute refs");

        if ($this->isOfType(TaoOntology::CLASS_URI_ITEM, $resource)) {
            $this->getLogger()->critical("This is an item");

            //$references = $this->getResourceURIsFromItem($resource);
        }

        /*if (isset($body['referenced_resources'])) {
            $this->logger->warning(
                "There was a prev value for referenced_resources: ".
                var_export($body['referenced_resources'], true)
            );
        }*/

        $body['referenced_resources'] = $references;

        return $body;
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

    public function isOfType(
        string $type,
        core_kernel_classes_Resource $resource
    ): bool {
        $rootClass = $resource->getModel()->getClass($type);

        foreach ($resource->getTypes() as $type) {
            if ($type->equals($rootClass) || $type->isSubClassOf($rootClass)) {
                return true;
            }
        }

        return false;
    }

    private function getIndexDocumentBuilder(): IndexDocumentBuilder
    {
        $documentBuilder = $this->getIndexService()->getDocumentBuilder();
        $documentBuilder->setServiceLocator($this->getServiceLocator());

        return $documentBuilder;
    }

    private function isAdvancedSearchEnabled(): bool
    {
        return ($this->getService(AdvancedSearchChecker::class)->isEnabled());
    }

    private function getAdvancedSearch(): SearchInterface
    {
        return $this->getSearchProxy()->getAdvancedSearch();
    }

    private function getSearchProxy(): SearchProxy
    {
        return $this->getService(SearchProxy::SERVICE_ID);
    }

    private function getIndexService(): IndexService
    {
        return $this->getService(IndexService::SERVICE_ID);
    }

    private function getService(string $serviceId)
    {
        return $this->getServiceLocator()->getContainer()->get($serviceId);
    }
}
