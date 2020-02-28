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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Yuri Filippovich
 */

namespace oat\tao\scripts\tools\rdf;

use common_report_Report as Report;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\scripts\update\OntologyUpdater;

class LangRdfModelFix extends ScriptAction
{
    /**
     * @return Report
     */
    protected function run()
    {
        OntologyUpdater::syncModels();
        Report::createInfo('Syncronised model including language definitions');
    }

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Tool to recreate statements for lang.rdf files. By default in dry-run';
    }

    /**
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'dryRun' => [
                'longPrefix' => 'dryRun',
                'required' => false,
                'description' => 'dry run',
                'defaultValue' => 0
            ],
            'useLocal' => [
                'longPrefix' => 'useLocal',
                'description' => 'Use Local Namespace',
                'required' => false,
                'defaultValue' => 1
            ]
        ];
    }
}
