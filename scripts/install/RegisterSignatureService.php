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

use oat\tao\model\security\SignatureGenerator;
use oat\taoLti\scripts\install\InstallServices;

class RegisterSignatureService extends InstallServices
{
    public function __invoke($params)
    {
        $taskSerializer = new SignatureGenerator([
            SignatureGenerator::SALT => uniqid(mt_rand(), true)
        ]);

        $this->getServiceManager()->register(SignatureGenerator::SERVICE_ID, $taskSerializer);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'SignatureGenerator service is registered');
    }
}
