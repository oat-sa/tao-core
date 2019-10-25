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

use AppendIterator;
use common_report_Report as Report;
use oat\generis\model\data\ModelManager;
use oat\generis\model\kernel\persistence\file\FileIterator;
use oat\oatbox\extension\script\ScriptAction;
use tao_install_utils_ModelCreator;

class LangRdfModelFix extends ScriptAction
{
    /**
     * @return Report
     */
    protected function run()
    {
        try {
            $models = (new tao_install_utils_ModelCreator(LOCAL_NAMESPACE))->getLanguageModels();
            $rdf = ModelManager::getModel()->getRdfInterface();
            $total = 0;

            $dryRun = (boolean) $this->getOption('dryRun');
            $forceModelId = (boolean) $this->getOption('useLocal') ? 1 : null;
            $langModels = new AppendIterator();

            foreach (array_shift($models) as $file) {
                $langModels->append((new FileIterator($file, $forceModelId))->getIterator());
            }

            foreach ($langModels as $triple) {
                if (!$dryRun) {
                    $rdf->remove($triple);
                    $rdf->add($triple);
                }
                $total++;
            }
            return Report::createInfo(sprintf('%s languages statements were updated', $total));
        } catch (\Exception $e) {
            return new Report(Report::TYPE_ERROR, $e->getMessage());
        }
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
