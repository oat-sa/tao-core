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
namespace oat\tao\model\mvc\view\base;

use oat\tao\model\mvc\view\ViewHelperInterface;

/**
 * TAO controller renderer interface
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
interface RenderInterface {
    
    /**
     * @param type $templatePath
     * @param type $variables
     * @author Christophe GARCIA <christopheg@taotesting.com>
     */
    public function __construct($templatePath = null, $variables = array());
    
    /**
     * 
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param string $templatePath
     * @return $this
     */
    public function setTemplate($templatePath);
    
    /**
     * set up a variable to view
     * @param value $key
     * @param mixed $value
     * @author Christophe GARCIA <christopheg@taotesting.com>
     */
    public function setData($key, $value);
    
    /**
     * a key value array
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param array $array
     */
    public function setMultipleData($array);
    
    /**
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @return boolean
     */
    public function hasTemplate();
    
    /**
     * 
     * @param string $name
     * @param \oat\tao\model\mvc\view\base\ViewHelperInterface $helper
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @return $this
     */
    public function registerHelper($name , ViewHelperInterface $helper);
    
    /**
     * return string content
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @return string
     */
    public function render();
}
