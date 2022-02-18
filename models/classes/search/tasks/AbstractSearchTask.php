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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use common_exception_Error;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilderInterface;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\search\Search;
use oat\tao\model\search\SearchInterface;
use oat\tao\model\search\SearchProxy;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\oatbox\reporting\Report;


abstract class AbstractSearchTask
    implements ServiceLocatorAwareInterface
{
    use LoggerAwareTrait;
    use ServiceLocatorAwareTrait;

    protected function getIndexService(): IndexService
    {
        return $this->getServiceLocator()->get(IndexService::SERVICE_ID);
    }

    protected function getIndexUpdater(): IndexUpdaterInterface
    {
        return $this->getServiceLocator()->get(IndexUpdaterInterface::SERVICE_ID);
    }

    protected function getDocumentBuilder(): IndexDocumentBuilderInterface
    {
        $builder = $this->getIndexService()->getDocumentBuilder();
        if(method_exists($builder, 'setServiceLocator')) {
            $builder->setServiceLocator($this->getServicelocatior());
        }

        return $builder;
    }

    protected function getSearchService(): SearchInterface
    {
        return $this->getServiceLocator()->get(SearchProxy::SERVICE_ID);
    }

    protected function getLegacySearchService(): Search
    {
        return $this->getServiceLocator()->get(Search::SERVICE_ID);
    }

    /**
     * @throws common_exception_Error
     */
    protected function buildSuccessReport(string $message) : ReportInterface
    {
        return new Report(ReportInterface::TYPE_SUCCESS, $message);
    }

    /**
     * @throws common_exception_Error
     */
    protected function buildInformationReport(string $message) : ReportInterface
    {
        return new Report(ReportInterface::TYPE_INFO, $message);
    }

    /**
     * @throws common_exception_Error
     */
    protected function buildWarningReport(string $message) : ReportInterface
    {
        return new Report(ReportInterface::TYPE_WARNING, $message);
    }

    /**
     * @throws common_exception_Error
     */
    protected function buildErrorReport(
        string $message,
        array $children = [],
        $data = null
    ) : ReportInterface {
        return new Report(ReportInterface::TYPE_ERROR, $message, $data, $children);
    }
}
