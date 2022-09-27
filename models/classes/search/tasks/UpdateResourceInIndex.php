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

use common_exception_MissingParameter;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\reporting\Report;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\SearchProxy;
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

        $numberOfIndexed = $this->getSearchProxy()->index($this->getIndexDocuments($params));
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

    private function getIndexDocuments(array $params): array
    {
        $documents = [];
        $resourceUris = is_array($params[0]) ? $params[0] : [$params[0]];
        $documentBuilder = $this->getIndexDocumentBuilder();

        foreach ($resourceUris as $resourceUri) {
            $this->resourceUris[] = $resourceUri;

            $createdResource = $this->getResource($resourceUri);

            $documents[] = $documentBuilder->createDocumentFromResource($createdResource);
        }

        return $documents;
    }

    private function getIndexDocumentBuilder(): IndexDocumentBuilder
    {
        $documentBuilder = $this->getIndexService()->getDocumentBuilder();
        $documentBuilder->setServiceLocator($this->getServiceLocator());

        return $documentBuilder;
    }

    private function getSearchProxy(): SearchProxy
    {
        return $this->getServiceLocator()->getContainer()->get(SearchProxy::SERVICE_ID);
    }

    private function getIndexService(): IndexService
    {
        return $this->getServiceLocator()->getContainer()->get(IndexService::SERVICE_ID);
    }
}
