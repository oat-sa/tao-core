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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\search;

use oat\oatbox\extension\AbstractAction;
use oat\tao\model\search\index\IndexService;
use common_report_Report as Report;

/**
 * Command to reindex all resources
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 */
class RunIndexing extends AbstractAction
{
    /**
     * @param $params
     * @return mixed
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        /** @var IndexService $indexService */
        $indexService = $this->getServiceManager()->get(IndexService::SERVICE_ID);

        $this->logInfo('Run resources indexing');
        if ($indexService->runIndexing()) {
            return new Report(Report::TYPE_SUCCESS, __('Resources successfully indexed'));
        }

        return new Report(Report::TYPE_WARNING, __('Resources was not indexed'));
    }
}
