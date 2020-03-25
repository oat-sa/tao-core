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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\install;

use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\security\SignatureGenerator;

class RegisterSignatureGenerator extends InstallAction
{
    /**
     * @param $params
     * @return common_report_Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $signatureGenerator = new SignatureGenerator([
            SignatureGenerator::OPTION_SALT => uniqid(mt_rand(), true)
        ]);

        $this->getServiceManager()->register(SignatureGenerator::SERVICE_ID, $signatureGenerator);

        return new common_report_Report(common_report_Report::TYPE_SUCCESS, 'SignatureGenerator service is registered');
    }
}
