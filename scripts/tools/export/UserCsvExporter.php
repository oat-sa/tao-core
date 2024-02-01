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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\export;

use core_kernel_classes_Class;
use oat\generis\model\user\UserRdf;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\export\implementation\CsvExporter;
use oat\tao\model\TaoOntology;
use oat\oatbox\reporting\Report;

class UserCsvExporter extends ScriptAction
{
    private const OPTION_OUTPUT_PATH = 'output-path';

    private Report $report;

    protected function provideOptions()
    {
        return [
            self::OPTION_OUTPUT_PATH => [
                'prefix' => 'out',
                'longPrefix' => self::OPTION_OUTPUT_PATH,
                'required' => true,
                'description' => 'Path to export the user CSV file.',
            ],
        ];
    }

    protected function provideDescription()
    {
        return "Exports Users in CSV format.";
    }

    protected function run()
    {
        $this->report = Report::createInfo('User CSV export started');

        $userClass = new core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
        $properties = $this->findUserPropertyList($userClass);

        $dataForExport = $this->collectDataFromUsers($userClass->getInstances(), $properties);
        $columnNames = $this->findColumnNames($properties);
        array_unshift($dataForExport, $columnNames);

        $exporter = new CsvExporter($dataForExport);

        $outputFile = $this->getOption(self::OPTION_OUTPUT_PATH);
        if (file_put_contents($outputFile, $exporter->export())) {
            $this->report->add(Report::createSuccess("Operation completed successfully"));
        } else {
            $this->report->add(Report::createError("Failed to write prepared CSV to the specified path"));
        }

        return $this->report;
    }

    private function findUserPropertyList($userClass): array
    {
        $properties = [];
        $properties[UserRdf::PROPERTY_LOGIN] = $userClass->getProperty(UserRdf::PROPERTY_LOGIN);
        $properties[UserRdf::PROPERTY_FIRSTNAME] = $userClass->getProperty(UserRdf::PROPERTY_FIRSTNAME);
        $properties[UserRdf::PROPERTY_LASTNAME] = $userClass->getProperty(UserRdf::PROPERTY_LASTNAME);
        $properties[UserRdf::PROPERTY_MAIL] = $userClass->getProperty(UserRdf::PROPERTY_MAIL);
        $properties[UserRdf::PROPERTY_TIMEZONE] = $userClass->getProperty(UserRdf::PROPERTY_TIMEZONE);
        $properties[UserRdf::PROPERTY_UILG] = $userClass->getProperty(UserRdf::PROPERTY_UILG);
        $properties[UserRdf::PROPERTY_DEFLG] = $userClass->getProperty(UserRdf::PROPERTY_DEFLG);

        return $properties;
    }

    private function collectDataFromUsers($users, $properties): array
    {
        $result = [];
        foreach ($users as $user) {
            $userData = [$user->getUri()];
            foreach ($properties as $property) {
                $userData[] = (string) $user->getOnePropertyValue($property);
            }
            $result[] = $userData;
        }

        return $result;
    }

    private function findColumnNames(array $properties): array
    {
        $columnNames = ['URI'];
        foreach ($properties as $property) {
            $columnNames[] = $property->getLabel();
        }

        return $columnNames;
    }
}
