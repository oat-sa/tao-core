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


    public function createTarget(){

    }

    public function register($name, $targets = array() )
    {
        $path = StringUtils::removeSpecChars($name);
        $id = StringUtils::camelize($path);
        $value = array(
            'path' => $path,
            'name' => $name,
            'targets' => $targets
        );


        $this->set($id, $value);
    }

    public function getAvailableThemes(){

        $returnValue = array();
        foreach (ThemeRegistry::getRegistry()->getMap() as $id => $conf ){
            $array = array(
                    'id' => $id,
                    'name' => $conf['name'],
                    'path' => $conf['path']
                );
            if(isset($conf['targets']) &&  !empty($conf['targets']) ){
                foreach ($conf['targets'] as $target) {
                    $returnValue[$target][] = $array;
                }
            }
            else {
                // do we support default mode ?
                //$returnValue['all'][] = $array;
            }
        }
        return $returnValue;
    }
}

?>