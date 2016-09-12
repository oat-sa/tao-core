<?php
/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *  
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 *  Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\mvc\view;

use oat\oatbox\service\ConfigurableService;

/**
 * Description of ViewService
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ViewFactoryService extends ConfigurableService 
{
    
    /**
     * return rendering engine from configured engine name
     * @param string $engine
     * @return oat\tao\model\mvc\view\base\RenderInterface
     */
    public function getRenderer($engine) {
        
        if($this->hasOption($engine)) {
            
            $option = $this->getOption($engine);
            $renderClass = $option['class'];
            
            /*@var $renderer \oat\tao\model\mvc\view\base\RenderInterface */
            $renderer = new $renderClass();
            if(array_key_exists('template', $option)) {
                $renderer->setTemplate($option['template']);
            }
            if(array_key_exists('variables', $option)) {
                $renderer->setMultipleData($option['variables']);
            }
            return $renderer;
        }
        throw  new \InvalidArgumentException('unconfigured rendering engine ' . $engine);
    }
    
}
