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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\routing;

/**
 * Class AbstractApiRoute
 * Route for RestApi controllers
 * @package oat\tao\model\routing
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractApiRoute extends Route
{
    /**
     * @param $relativeUrl
     * @return string
     */
    public function resolve($relativeUrl)
    {
        try {
            return $this->getController($relativeUrl) . '@' . $this->getAction();
        } catch (\common_exception_BadRequest $e) {
            return null;
        }
    }

    /**
     * @param $relativeUrl
     * @return string
     * @throws \common_exception_BadRequest
     */
    protected function getController($relativeUrl)
    {
        $parts = explode('/', $relativeUrl);
        $prefix = $this->getControllerPrefix();
        if (!isset($parts[2])) {
            throw new \common_exception_BadRequest('Missed controller name in uri: ' . $relativeUrl);
        }

        if (!class_exists($prefix . ucfirst($parts[2]))) {
            throw new \common_exception_BadRequest('Controller ' . $parts[2] . ' does not exists');
        }

        return $prefix . ucfirst($parts[2]);
    }

    /**
     * @return string
     * @throws \common_exception_BadRequest
     */
    protected function getAction()
    {
        $method = \Context::getInstance()->getRequest()->getMethod();

        switch ($method) {
            case "GET":
                $action = 'get';
                break;
            case "PUT":
                $action = 'put';
                break;
            case "POST":
                $action = 'post';
                break;
            case "DELETE":
                $action = 'delete';
                break;
            default:
                throw new \common_exception_BadRequest('Method `' . $method . ' is not supported');
        }

        return $action;
    }

    /**
     * Get controller namespace prefix
     * @return string
     */
    abstract protected function getControllerPrefix();
}
