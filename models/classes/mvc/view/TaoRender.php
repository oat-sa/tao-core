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

use common_Exception;
use oat\tao\helpers\Template;
use oat\tao\model\mvc\view\base\RenderInterface;
use Renderer;

/**
 * Description of Render
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class TaoRender implements RenderInterface {

    /**
     * render engine
     * any render library
     * @var mixed 
     */
    protected $engine;

    /**
     * Constructor with optional parameters
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param string $templatePath template to use
     * @param array $variables
     */
    public function __construct($templatePath = null, $variables = array()) {
        $this->engine = new Renderer($templatePath, $variables);
    }

    /**
     * sets the template to be used
     *
     * @access public
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param  string templatePath
     * @return $this
     */
    public function setTemplate($templatePath) {
        $this->engine->setTemplate($templatePath);
        return $this;
    }

    /**
     * adds or replaces the data for a specific key
     *
     * @access public
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param  string key
     * @param  mixed value
     * @return $this
     */
    public function setData($key, $value) {
        $this->engine->setData($key, $value);
        return $this;
    }

    /**
     * adds or replaces the data for multiple keys
     *
     * @access public
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param  array array associativ array of data
     */
    public function setMultipleData($array) {
        $this->engine->setMultipleData($array);
        return $this;
    }

    /**
     * Whenever or not a template has been specified
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @return boolean
     */
    public function hasTemplate() {
        return $this->engine->hasTemplate();
    }
    /**
     * set up a new helper
     * @param type $name
     * @param ViewHelperInterface $helper
     * @return TaoRender
     */
    public function registerHelper($name, ViewHelperInterface $helper) {
        Template::addHelper($name, $helper);
        return $this;
    }
    
    /**
     * return render string
     * @return type
     * @throws common_Exception
     */
    public function render() {
        
        return $this->engine->render();
    }

}
