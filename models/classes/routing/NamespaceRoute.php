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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\routing;

/**
 * A simple router, that maps a relative Url to
 * namespaced Controller class
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class NamespaceRoute implements Route
{
    /**
     * Name of the class
     * 
     * @var string
     */
    private $controller;
    
    /**
     * Methode to call
     * 
     * @var string
     */
    private $method;
    
    /**
     * Resolves a request to a method
     * 
     * @param string $routePath
     * @param string $namespace
     * @param string $relativeUrl
     */
    public function __construct($routePath, $namespace, $relativeUrl) {
        $rest = trim(substr($relativeUrl, strlen($routePath)), '/');
        if (!empty($rest)) {
            $parts = explode('/', $rest, 3);
            $this->controller = rtrim($namespace, '\\').'\\'.$parts[0];
            $this->method = $parts[1];
        } elseif (defined('DEFAULT_MODULE_NAME')) {
            $this->controller = rtrim($namespace, '\\').'\\'.DEFAULT_MODULE_NAME;
            $this->method = null;
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\routing\Route::getControllerName()
     */
    public function getControllerName() {
        return $this->controller;
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\routing\Route::getMethodName()
     */
    public function getMethodName() {
        return $this->method;
    }

}
