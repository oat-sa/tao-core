<?php

declare(strict_types=1);

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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\integration\import;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\import\ImportersService;
use oat\tao\test\integration\import\samples\FakeImporter;
use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * Class ImportersServiceTest
 *
 * @package oat\tao\test\integration\import
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ImportersServiceTest extends TaoPhpUnitTestRunner
{
    public function testGetImporter(): void
    {
        $importersService = new ImportersService([
            ImportersService::OPTION_IMPORTERS => [
                'testImporter' => new FakeImporter(),
            ],
        ]);

        $importersService->setServiceLocator(ServiceManager::getServiceManager());
        $this->assertInstanceOf('\oat\tao\test\integration\import\samples\FakeImporter', $importersService->getImporter('testImporter'));
    }

    /**
     * @expectedException \oat\tao\model\import\ImporterNotFound
     */
    public function testGetImporterException(): void
    {
        $importersService = new ImportersService([]);
        $importersService->setServiceLocator(ServiceManager::getServiceManager());
        $importersService->getImporter('testImporter');
    }
}
