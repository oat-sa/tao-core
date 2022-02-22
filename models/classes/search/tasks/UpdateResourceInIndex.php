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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\log\LogBuffer;
use oat\tao\model\search\tasks\log\SearchTaskLogTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use common_report_Report as Report;

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
    use SearchTaskLogTrait;

    public function __invoke($params): Report
    {
        if (empty($params) || empty($params[0])) {
            throw new \common_exception_MissingParameter();
        }

        $createdResource = $this->getResource($params[0]);

        /** @var IndexService $indexService */
        $indexService = $this->getServiceLocator()->get(IndexService::SERVICE_ID);

        /** @var IndexDocumentBuilder $documentBuilder */
        $documentBuilder = $indexService->getDocumentBuilder();
        $documentBuilder->setServiceLocator($this->getServiceLocator());

        $indexDocument = $documentBuilder->createDocumentFromResource($createdResource);

        /** @var Search $searchService */
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);

        $logBuffer = new LogBuffer();
        $formerLogger = $this->setupLogInterceptor($searchService, $logBuffer);

        $numberOfIndexed = $searchService->index([$indexDocument]);

        if ($numberOfIndexed === 1) {
            $type = Report::TYPE_SUCCESS;
            $message = sprintf(
                "Document in index was successfully updated for resource %s",
                $indexDocument->getId()
            );
        } elseif ($numberOfIndexed === 0) {
            $type = Report::TYPE_ERROR;
            $message = $this->getErrorMessage(
                'Expecting a single document to be indexed (got zero)',
                $indexDocument,
                $logBuffer
            );
        } else {
            $type = Report::TYPE_WARNING;
            $message = $this->getErrorMessage(
                sprintf(
                    'Expecting a single document to be indexed (got %d)',
                    $numberOfIndexed
                ),
                $indexDocument,
                $logBuffer
            );
        }

        $this->removeLogInterceptor($searchService, $formerLogger);

        return new Report($type, $message);
    }
}
