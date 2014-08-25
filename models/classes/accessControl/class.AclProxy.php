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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

use oat\tao\model\accessControl\data\AclProxy as DataProxy;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\routing\Resolver;

/**
 * Proxy for the Acl Implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class tao_models_classes_accessControl_AclProxy
{
    /**
     * Returns whenever or not the current user has access to a specified link
     *
     * @param string $action
     * @param string $controller
     * @param string $extension
     * @param array $parameters
     * @return boolean
     */
    public static function hasAccess($action, $controller, $extension, $parameters = array()) {
        $user = common_session_SessionManager::getSession()->getUserUri();
        return AclProxy::hasAccess($user, self::findClassName($extension, $controller), $action, $parameters);
    }
    
    private static function findClassName($extension, $controller) {
        $url = _url('index', $controller, $extension);
        $route = new Resolver(new common_http_Request($url));
        $class = $route->getControllerClass();
        if (is_null($class)) {
            throw new common_exception_Error('The pair '.$extension.'::'.$controller.' addressed by "'.$url.'" could not be mapped to a controller');
        }
        return $class;
    }
}