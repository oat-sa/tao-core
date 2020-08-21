<?php

use core_kernel_classes_Class as KernelClass;
use oat\tao\helpers\form\elements\TreeAware;
use oat\taoBackOffice\model\tree\TreeService;

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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-201 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 * Short description of class tao_helpers_form_elements_TreeBox
 *
 * @abstract
 * @access public
 * @author Mikhail Kamarouski, <kamarouski@1pt.com>
 * @package tao

 */
abstract class tao_helpers_form_elements_Treebox extends tao_helpers_form_elements_MultipleElement implements TreeAware
{
    public const WIDGET_ID = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeBox';

    public function rangeToTree(KernelClass $range, $recursive = false)
    {
        return TreeService::singleton()->getNestedStructure($range->getInstances(false));
    }
}
