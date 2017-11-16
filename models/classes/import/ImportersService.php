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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\import;

use oat\oatbox\service\ConfigurableService;

/**
 * Class ImportersService
 *
 * @package oat\tao\model\import
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ImportersService extends ConfigurableService
{

    const SERVICE_ID = 'tao/Importers';
    const OPTION_IMPORTERS = 'importers';

    /**
     * @param $id
     * @return Importer
     * @throws ImporterNotFound
     */
    public function getImporter($id)
    {
        $result = null;
        if ($this->hasOption(self::OPTION_IMPORTERS)) {
            $importers = $this->getOption(self::OPTION_IMPORTERS);
            if (isset($importers[$id])) {
                /** @var Importer $result */
                $result = new $importers[$id]();
                $result->setServiceLocator($this->getServiceManager());
            }
        }
        if ($result === null) {
            throw new ImporterNotFound('Importer `' . $id . '` not found.');
        }
        return $result;
    }
}