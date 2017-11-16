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

/**
 * tao renderer using clear FW renderer
 */
class TaoViewRender extends \oat\oatbox\service\ConfigurableService implements ViewRenderInterface 
{
    /**
     * @var \Renderer 
     */
    protected $renderer;
    
    public function getRender() {
        if(is_null($this->renderer)) {
            $this->renderer = new \Renderer();
        }
        return $this->renderer;
    }

        /**
     * @inheritDoc
     */
    public function hasTemplate() {
        
        return $this->getRender()->hasTemplate();
    }
    /**
     * @inheritDoc
     */
    public function render() {
        return $this->getRender()->render();
    }
    /**
     * @inheritDoc
     */
    public function setData($key, $value) {
        return $this->getRender()->setData($key, $value);
    }
    /**
     * @inheritDoc
     */
    public function setMultipleData($array) {
        return $this->getRender()->setMultipleData($array);
    }
    /**
     * @inheritDoc
     */
    public function setTemplate($templatePath) {
        return $this->getRender()->setTemplate($templatePath);
    }

}

