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
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Lionel Lecaque  <lionel@taotesting.com>
 * @license GPLv2
 */

declare(strict_types=1);

class tao_install_utils_DbalConfigCreator
{
    public function createDbalConfig($installData)
    {
        if ($installData['db_driver'] == 'pdo_oci') {
            $installData['db_name'] = $installData['db_host'];
            $installData['db_host'] = '';
        }

        $dbConnectionParams = [
            'driver' => $installData['db_driver'],
            'driverClass' => $installData['db_driverClass'] ?? null,
            'instance' => $installData['db_instance'] ?? null,
            'host' => $installData['db_host'],
            'dbname' => $installData['db_name'],
            'user' => $installData['db_user'],
            'password' => $installData['db_pass'],
            'driverOptions' => $installData['db_driverOptions'] ?? [],
        ];

        $hostParts = explode(':', $installData['db_host']);
        if (count($hostParts) == 2) {
            $dbConnectionParams['host'] = $hostParts[0];
            $dbConnectionParams['port'] = $hostParts[1];
        }

        if ($installData['db_driver'] == 'pdo_oci') {
            $dbConnectionParams['wrapperClass'] = 'Doctrine\DBAL\Portability\Connection';
            $dbConnectionParams['portability'] = \Doctrine\DBAL\Portability\Connection::PORTABILITY_ALL;
            $dbConnectionParams['fetch_case'] = PDO::CASE_LOWER;
        }

        return [
            'driver' => 'dbal',
            'connection' => $dbConnectionParams,
        ];
    }
}
