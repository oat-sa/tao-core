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
 * view engine base interface
 */
interface ViewRenderInterface {
    
    /**
     * set template path
     * @param string $templatePath
     * @return $this
     */
    public function setTemplate($templatePath);
    
    /**
     * set view data
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setData($key, $value);
    
    /**
     * set up view data from a key-value array
     * @param array $array
     * @return $this
     */
    public function setMultipleData($array);
    
    /**
     * verify if template has been set up
     * @return boolean
     */
    public function hasTemplate();
    
    /**
     * process view render
     * @return string
     */
    public function render();
    
    
    
}