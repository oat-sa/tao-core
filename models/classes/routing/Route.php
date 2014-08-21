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
 */
namespace oat\tao\model\routing;

/**
 * Interface of a router, that based on a relative Url
 * and its configuration provided as $routeData
 * decides which methode of which controller should be executed
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
interface Route
{
    /**
     * 
     * @param string $routePath
     * @param mixed $routeData
     * @param string $realtiveUrl
     */
    public function __construct($routePath, $routeData, $realtiveUrl);
    
    /**
     * Returns the name of the controller class
     * 
     * @return string
     */
    public function getControllerName();
    
    /**
     * Returns the name of the method to be called
     */
    public function getMethodName();
}