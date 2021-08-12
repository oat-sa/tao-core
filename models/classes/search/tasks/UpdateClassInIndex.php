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
 * Copyright (c) 2018-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_exception_Error;
use common_exception_MissingParameter;
use oat\oatbox\reporting\Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\search\index\IndexIteratorFactory;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UpdateClassInIndex implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    /** @var IndexIteratorFactory */
    private $indexIteratorFactory;

    public function __construct(IndexIteratorFactory $indexIteratorFactory = null)
    {
        $this->indexIteratorFactory = $indexIteratorFactory;
    }

    /**
     * @throws common_exception_MissingParameter
     * @throws common_exception_Error
     */
    public function __invoke($params): Report
    {
        if (empty($params) || empty($params[0])) {
            throw new common_exception_MissingParameter();
        }

        /** @var SearchProxy $searchService */
        $searchService = $this->getServiceLocator()->get(SearchProxy::SERVICE_ID);
        $numberOfIndexedResources = $searchService->index(
            $this->getIndexIteratorFactory()->create($params)
        );

        $this->logInfo($numberOfIndexedResources . ' resources have been indexed by ' . static::class);

        $type = Report::TYPE_SUCCESS;
        $message = "Documents in index were successfully updated.";

        if ($numberOfIndexedResources < 1) {
            $type = Report::TYPE_INFO;
            $message = "Zero documents were added/updated in index.";
        }

        return new Report($type, $message);
    }

    private function getIndexIteratorFactory(): IndexIteratorFactory
    {
        if (null === $this->indexIteratorFactory) {
            $this->indexIteratorFactory = new IndexIteratorFactory($this->getServiceLocator());
        }

        return $this->indexIteratorFactory;
    }
}
