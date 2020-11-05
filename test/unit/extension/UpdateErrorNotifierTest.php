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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types = 1);

namespace oat\tao\test\unit\extension;

use common_exception_Error;
use common_report_Report;
use oat\generis\test\TestCase;
use oat\tao\model\extension\UpdateErrorNotifier;
use oat\tao\model\externalNotifiers\ExternalNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use Zend\ServiceManager\ServiceLocatorInterface;

class UpdateErrorNotifierTest extends TestCase
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocatorMock;
    /**
     * @var externalNotifierMock|MockObject
     */
    private $externalNotifierMock;

    public function setUp(): void
    {
        define('ROOT_URL', 'https://test.test.com');

        $this->externalNotifierMock = $this->createMock(ExternalNotifier::class);
        $this->serviceLocatorMock = $this->getServiceLocatorMock([
            'test/notifier' => $this->externalNotifierMock,
        ]);
    }

    /**
     * @throws common_exception_Error
     */
    public function testCheckReportErrorWithErrorReport()
    {
        $report = new common_report_Report(common_report_Report::TYPE_INFO, 'Report');
        $report->add(new common_report_Report(common_report_Report::TYPE_ERROR, 'Error report'));

        $this->externalNotifierMock
            ->expects($this->once())
            ->method('notify')
            ->with('Error at taoUpdate on ' . ROOT_URL, 'Error report' . PHP_EOL);

        $updateErrorNotifier = new UpdateErrorNotifier();
        $updateErrorNotifier->setServiceLocator($this->serviceLocatorMock);
        $updateErrorNotifier->setOption(UpdateErrorNotifier::OPTION_NOTIFIERS, ['test/notifier']);
        $updateErrorNotifier->checkReportError($report);
    }

    public function testCheckReportErrorWithoutOptions()
    {
        $reportMock = $this->createMock(common_report_Report::class);
        $reportMock->expects($this->never())
            ->method('containsError');
        $reportMock->expects($this->never())
            ->method('getErrors');

        $updateErrorNotifier = new UpdateErrorNotifier();
        $updateErrorNotifier->checkReportError($reportMock);
    }

    /**
     * @throws common_exception_Error
     */
    public function testCheckReportErrorWithoutErrorReport()
    {
        $report = new common_report_Report(common_report_Report::TYPE_INFO, 'Report');
        $report->add(new common_report_Report(common_report_Report::TYPE_INFO, 'Good report'));

        $this->externalNotifierMock
            ->expects($this->never())
            ->method('notify');

        $updateErrorNotifier = new UpdateErrorNotifier();
        $updateErrorNotifier->setServiceLocator($this->serviceLocatorMock);
        $updateErrorNotifier->setOption(UpdateErrorNotifier::OPTION_NOTIFIERS, ['test/notifier']);
        $updateErrorNotifier->checkReportError($report);
    }

}
