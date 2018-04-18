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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA;
 */
namespace oat\tao\model\modules;

use oat\oatbox\AbstractRegistry;

/**
 * Store the <b>available</b> plugins modules, even if not activated, plugins have to be registered.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
abstract class AbstractModuleRegistry extends AbstractRegistry
{
    /**
     * Register a plugin
     * @param DynamicModule $module the plugin to register
     * @return boolean true if registered
     */
    public function register(DynamicModule $module)
    {
        if(!is_null($module) && ! empty($module->getModule()) ) {

            self::getRegistry()->set($module->getModule(), $module->toArray());

            return true;
        }
        return false;
    }
}
