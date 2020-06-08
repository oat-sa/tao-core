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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\AbstractMigration as DoctrineAbstractMigration;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\log\TaoLoggerAwareInterface;
use \common_report_Report as Report;
use \helpers_Report as ReportHelper;

/**
 * Class AbstractMigration
 *
 * The base class for all TAO Extension Migrations.
 *
 * @package oat\tao\scripts\tools\migrations
 */
abstract class AbstractMigration
    extends DoctrineAbstractMigration
    implements ServiceManagerAwareInterface, TaoLoggerAwareInterface
{
    use ServiceManagerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @return ServiceManager
     */
    public function getServiceLocator(): ServiceManager
    {
        return ServiceManager::getServiceManager();
    }

    /**
     * Add a Report
     *
     * Will write to output the given $report Report object. This method
     * enables developers to communicate important information about the execution
     * of their migrations e.g. to Operations.
     *
     * @param Report $report
     */
    protected function addReport(Report $report): void
    {
        $this->write(ReportHelper::renderToCommandLine($report, false));
    }
}