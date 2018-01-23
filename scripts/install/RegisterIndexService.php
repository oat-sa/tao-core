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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
namespace oat\tao\scripts\install;

use common_Logger;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\search\index\implementation\OntologyIndex;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\TaoOntology;

class RegisterIndexService extends AbstractAction
{

    public function __invoke($params)
    {

        $this->getServiceManager()->register(IndexService::SERVICE_ID, new IndexService([
            'rootClasses' => [
                TaoOntology::CLASS_URI_ITEM => [
                    IndexService::PROPERTY_FIELDS => []
                ],
                TaoOntology::CLASS_URI_TEST => [
                    IndexService::PROPERTY_FIELDS => []
                ],
                TaoOntology::CLASS_URI_SUBJECT => [
                    IndexService::PROPERTY_FIELDS => []
                ],
                TaoOntology::CLASS_URI_GROUP => [
                    IndexService::PROPERTY_FIELDS => []
                ]
            ]
        ]));

    }
}