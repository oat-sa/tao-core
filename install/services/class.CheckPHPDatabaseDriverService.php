<?php

/*
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg
 *                         (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

/**
 * A Service implementation aiming at checking the existence and the availability of
 * a Database Driver on the host system.
 *
 * Please refer to tao/install/api.php for more information about how to call this service.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao

 */
class tao_install_services_CheckPHPDatabaseDriverService extends tao_install_services_Service implements
    tao_install_services_CheckService
{
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct(tao_install_services_Data $data)
    {
        parent::__construct($data);
    }

    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute()
    {
        $ext = self::buildComponent($this->getData());
        $report = $ext->check();
        $this->setResult(self::buildResult($this->getData(), $report, $ext));
    }

    protected function checkData()
    {
        // Check data integrity.
        $content = json_decode($this->getData()->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] !== 'CheckPHPDatabaseDriver') {
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPDatanaseDriver'.");
        } elseif (!isset($content['value']) || empty($content['value'])) {
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        } elseif (!isset($content['value']['id']) || empty($content['value']['id'])) {
            throw new InvalidArgumentException("Missing data: 'id' must be provided.");
        } elseif (!isset($content['value']['name'])) {
            throw new InvalidArgumentException("Missing data: 'name' must be provided.");
        }
    }

    public static function buildComponent(tao_install_services_Data $data)
    {
        $content = json_decode($data->getContent(), true);
        $extensionName = $content['value']['name'];
        if (isset($content['value']['optional'])) {
            $optional = $content['value']['optional'];
        } else {
            $optional = false;
        }

        return common_configuration_ComponentFactory::buildPHPDatabaseDriver($extensionName, $optional);
    }

    public static function buildResult(
        tao_install_services_Data $data,
        common_configuration_Report $report,
        common_configuration_Component $component
    ) {

        $content = json_decode($data->getContent(), true);
        $id = $content['value']['id'];

        $data = ['type' => 'PHPDatabaseDriverReport',
                      'value' => ['status' => $report->getStatusAsString(),
                                       'message' => $report->getMessage(),
                                       'optional' => $component->isOptional(),
                                       'name' => $component->getName(),
                                       'id' => $id]];

        return new tao_install_services_Data(json_encode($data));
    }
}
