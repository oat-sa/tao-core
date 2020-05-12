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
 * Copyright (c) 2020  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

declare(strict_types=1);

namespace oat\tao\model\layout\configuredLayout;

use oat\oatbox\service\ConfigurableService;
use Request;

/**
 * Class AbstractLayoutPageTitleService
 * @package oat\tao\model\layout\configuredLayout
 */
abstract class AbstractLayoutPageTitleService extends ConfigurableService
{
    /**
     * To have messages translated we need static programmed map
     * @return array
     *
     * @example
     * [
     *      'controllerName' => 'title for controller' or __('title') or [
     *          'actionName' => 'title for action' or __('title') or [
     *              'request' => [
     *                  'expectedParamKey' => 'expectedParamValue' // if request has parameter
     *              ],
     *              'title' => 'Title String' or __('title string'),
     *              'title' => '__self__::callMethodName' // will be called with params callMethodName($controller, $action, $request)
     *          ]
     *      ]
     *  ]
     */
    abstract protected function getMap(): array;

    /**
     * @param string $controller
     * @param string $action
     * @param Request $request
     * @return string|null
     */
    public function getTitle(
        string $controller,
        string $action,
        Request $request
    ): ?string
    {
        $title = null;

        $map = $this->getMap();
        if (array_key_exists($controller, $map)) {
            $title = $this->getTitleForController($map[$controller], $action, $request);
        }

        return $title;
    }

    /**
     * @param Request $request
     * @param array $map
     * @return bool
     */
    protected function isExpectedRequest(Request $request, array $map): bool
    {
        if (array_key_exists('request', $map)) {
            foreach ($map['request'] as $k => $v) {
                if (!$request->hasParameter($k) || $request->getParameter($k) !== $v) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $controllerMap
     * @param string $action
     * @param Request $request
     * @return string|null
     */
    protected function getTitleForController($controllerMap, string $action, Request $request): ?string
    {
        $title = null;
        if (is_string($controllerMap)) {
            $title = $controllerMap;
        } elseif (is_array($controllerMap)) {
            if(array_key_exists($action, $controllerMap)) {
                $title = $this->getTitleForAction($controllerMap[$action], $request);
            } elseif (
                array_key_exists('title', $controllerMap)
                && $this->isExpectedRequest($request, $controllerMap)
            ) {
                $title = $controllerMap['title'];
            }
        }
        return $title;
    }

    /**
     * @param $actionMap
     * @param Request $request
     * @return string|null
     */
    protected function getTitleForAction($actionMap, Request $request): ?string
    {
        $title = null;
        if (is_string($actionMap)) {
            $title = $actionMap;
        } elseif(is_array($actionMap)
            && array_key_exists('title', $actionMap)
            && $this->isExpectedRequest($request, $actionMap)
        ) {
            $title = $actionMap['title'];
        }
        return $title;
    }
}
