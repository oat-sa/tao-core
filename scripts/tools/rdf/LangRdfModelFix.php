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
use oat\generis\model\data\ModelManager;
use oat\generis\model\kernel\persistence\file\FileIterator;
use oat\oatbox\extension\AbstractAction;
use tao_install_utils_Exception;
use tao_install_utils_ModelCreator;


/**
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\rdf\LangRdfModelFix' [--notLocal]
 **/
class LangRdfModelFix extends AbstractAction
{
    /**
     * @param $params
     * @return Report
     * @throws tao_install_utils_Exception
     */
    public function __invoke ($params)
    {
        $models = (new tao_install_utils_ModelCreator(LOCAL_NAMESPACE))->getLanguageModels();
        $rdf = ModelManager::getModel()->getRdfInterface();
        $total = 0;
        $forceModelId = (isset($params[0]) && $params[0] == '--notLocal') ? null : 1;

        foreach (array_shift($models) as $file) {
            $iterator = new FileIterator($file, $forceModelId);
            foreach ($iterator as $triple) {
                $rdf->remove($triple);
                $rdf->add($triple);
                $total++;
            }
        }
        return Report::createInfo(sprintf('%s languages statements were updated', $total));
    }
}
