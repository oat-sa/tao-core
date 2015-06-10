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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

 namespace oat\tao\model;


use oat\oatbox\AbstractRegistry;
use common_ext_ExtensionsManager;
use Jig\Utils\StringUtils;


class ThemeRegistry extends AbstractRegistry
{

    const THEME_BASE = 'taoQtiItem/views/css/qti-runner.css';
    const THEME_DEFAULT = 'tao';

    /**
     *
     * @see \oat\oatbox\AbstractRegistry::getExtension()
     */
    protected function getExtension()
    {
        return common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    }

    /**
     *
     * @see \oat\oatbox\AbstractRegistry::getConfigId()
     */
    protected function getConfigId()
    {
        return 'themes';
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param string $name
     * @param array $targets
     */
    public function register($name, $targets = array() )
    {
        foreach ($targets as $target) {
            if($this->isRegistered($target)){
                $array = $this->get($target);
            }
            else {
                $array = array(
                    'base'  => ThemeRegistry::THEME_BASE,
                    'default' => ThemeRegistry::THEME_DEFAULT,
                    'available' => array()
                );
            }

            $path = StringUtils::removeSpecChars($name);
            $id = StringUtils::camelize($path);


            $value = array(
                'id' => $id,
                'path' => $path,
                'name' => $name,
            );
            $array['available'][] = $value;

            $this->set($target, $array);
        }
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function getAvailableThemes(){
        return json_encode($this->getMap(),JSON_PRETTY_PRINT);
    }
}

?>