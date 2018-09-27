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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\tools\import;

use oat\generis\model\GenerisRdf;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\import\UserCsvImporterFactory;

/**
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\import\ImportUsersCsv' -t test-taker -f /txt.csv
 */
class ImportUsersCsv extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'user-type' => [
                'prefix' => 't',
                'longPrefix' => 'user-type',
                'required' => true,
                'description' => 'Type of user to import (test-taker, administrator, proctor, sync-manager).',
            ],
            'file-path' => [
                'prefix' => 'f',
                'longPrefix' => 'file-path',
                'required' => true,
                'description' => 'File path location.',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Import users (test-taker, administrator, proctor, sync-manager).';
    }

    /**
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function run()
    {
        /** @var UserCsvImporterFactory $userImporter */
        $userImporter = $this->getServiceLocator()->get(UserCsvImporterFactory::SERVICE_ID);
        $importer = $userImporter->create($this->getOption('user-type'));

        return $importer->import($this->getOption('file-path'));
    }

}