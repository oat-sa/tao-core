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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\tools;

use core_kernel_classes_Property;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\extension\AbstractAction;

class CleanClassLabels extends AbstractAction
{

    /**
     * @inheritDoc
     */
    public function __invoke($params)
    {
        try {
            if (!isset($params[0])) {
                return \common_report_Report::createFailure(
                    'CLASS_URI not provided' . PHP_EOL . 'Usage: php index.php ' . __CLASS__ . ' [CLASS_URI]'
                );
            }

            $class = new \core_kernel_classes_Class($params[0]);

            if (!$class->exists()) {
                return \common_report_Report::createFailure('Invalid CLASS_URI provided');
            }

            $label = new core_kernel_classes_Property(OntologyRdfs::RDFS_LABEL);
            $instances = $class->getInstances(true);
            $cleaned = 0;

            foreach ($instances as $instance) {
                $labels = $instance->getPropertyValues($label);
                if (count($labels) > 1) {
                    $instance->setLabel(array_shift($labels));
                    $cleaned++;
                }
            }

            return \common_report_Report::createSuccess(
                sprintf('instances with duplicates labels cleaned %u', $cleaned)
            );
        } catch (\Exception $e) {
            return \common_report_Report::createFailure($e->getMessage());
        }
    }
}
