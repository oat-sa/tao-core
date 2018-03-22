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

use oat\generis\model\OntologyRdfs;
use oat\generis\model\user\UserRdf;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\user\Import\OntologyUserMapper;
use oat\tao\model\user\Import\RdsUserImportService;

class RegisterUserImporter extends InstallAction
{
    /**
     * @param $params
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $mapper = new OntologyUserMapper([
            OntologyUserMapper::OPTION_SCHEMA => [
                OntologyUserMapper::OPTION_SCHEMA_MANDATORY => [
                    'label' => OntologyRdfs::RDFS_LABEL,
                    'interface language' => UserRdf::PROPERTY_UILG,
                    'login' => UserRdf::PROPERTY_LOGIN,
                    'roles' => UserRdf::PROPERTY_ROLES,
                    'password' => UserRdf::PROPERTY_PASSWORD,
                ],
                OntologyUserMapper::OPTION_SCHEMA_OPTIONAL => [
                    'interface language' => UserRdf::PROPERTY_DEFLG,
                    'first name' => UserRdf::PROPERTY_FIRSTNAME,
                    'last name' =>UserRdf::PROPERTY_LASTNAME,
                    'mail' => UserRdf::PROPERTY_MAIL,
                ]
            ]
        ]);

        $this->getServiceManager()->register(OntologyUserMapper::SERVICE_ID, $mapper);

        $importService = new RdsUserImportService([
            RdsUserImportService::OPTION_USER_MAPPER => OntologyUserMapper::SERVICE_ID,
        ]);

        $this->getServiceManager()->register(RdsUserImportService::SERVICE_ID, $importService);
    }
}