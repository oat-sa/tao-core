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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\task;

use common_report_Report as Report;
use InvalidArgumentException;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\AbstractAction;
use Psr\Container\ContainerInterface;
use tao_models_classes_import_ImportHandler;

/**
 * General import task.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ImportByHandler extends AbstractAction
{
    use OntologyAwareTrait;

    public const PARAM_IMPORT_HANDLER = 'import_handler';
    public const PARAM_IMPORT_HANDLER_SERVICE_ID = 'import_handler_service_id';
    public const PARAM_FORM_VALUES = 'form_values';
    public const PARAM_PARENT_CLASS = 'parent_class_uri';
    public const PARAM_OWNER = 'owner';

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params)
    {
        return $this->getImporter($params)->import(
            $this->getClass($params[self::PARAM_PARENT_CLASS]),
            $params[self::PARAM_FORM_VALUES],
            $params[self::PARAM_OWNER]
        );
    }

    private function getImporter(array $params): tao_models_classes_import_ImportHandler
    {
        if (isset($params[self::PARAM_IMPORT_HANDLER_SERVICE_ID])) {
            /** @var tao_models_classes_import_ImportHandler $importer */
            $importer = $this->getContainer()->get($params[self::PARAM_IMPORT_HANDLER_SERVICE_ID]);

            return $importer;
        }

        if (!isset($params[self::PARAM_IMPORT_HANDLER]) || !class_exists($params[self::PARAM_IMPORT_HANDLER])) {
            throw new InvalidArgumentException('Please provide a valid import handler');
        }

        /** @var tao_models_classes_import_ImportHandler $importer */
        $importer = new $params[self::PARAM_IMPORT_HANDLER]();

        $this->propagate($importer);

        return $importer;
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }
}
