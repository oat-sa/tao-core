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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

/**
 * Abstraction for controllers serving single page application.
 *
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 * @package oat\tao\actions
 *
 */
abstract class tao_actions_SinglePageModule extends \tao_actions_CommonModule
{
    /**
     * This header is added to the response to inform the client a forward occurs
     */
    const FORWARD_HEADER = 'X-Tao-Forward';

    /**
     * A list of parameters to provide to the client controller
     * @var array
     */
    protected $clientParams = [];

    /**
     * Sets the route to be used by the client controller
     * @param string $route
     */
    protected function setClientRoute($route) {
        header(self::FORWARD_HEADER . ': ' . $route);
        $this->setClientParam('forwardTo', $route);
    }

    /**
     * Add a parameter to provide to the client controller
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    protected function setClientParam($name, $value)
    {
        $this->clientParams[$name] = $value;
        return $this;
    }

    /**
     * Gets the path to the layout
     * @return array
     */
    protected function getLayout()
    {
        return ['layout.tpl', 'tao'];
    }

    /**
     * Main method to render a view using a particular template.
     * Detects whether the client only need JSON content.
     * You still need to set the main view, however it must be set
     * before to call this method as this view may be overridden.
     *
     * @param string [$scope] - A CSS class name that scope the view
     * @param array [$data] - An optional data set to forward to the view
     * @param String [$template] - Defines the path of the view, default to 'pages/index.tpl'
     * @param String [$extension] - Defines the extension that should contain the template
     * @throws \common_exception_Error
     */
    protected function composeView($scope = '', $data = array(), $template = '', $extension = '')
    {
        if (!is_array($data)) {
            $data = [];
        }

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->returnJson([
                'success' => true,
                'data' => $data,
            ]);
        } else {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = json_encode($value);
                }
            }
            $this->setData('data', $data);
            $this->setPage($scope, $template, $extension);
        }
    }

    /**
     * Assigns the template to render.
     * @param string [$scope] - A CSS class name that scope the view
     * @param String [$template] - Defines the path of the view, default to 'pages/index.tpl'
     * @param String [$extension] - Defines the extension that should contain the template
     */
    protected function setPage($scope = '', $template = '', $extension = '')
    {
        $template = empty($template) ? 'pages/index.tpl' : $template;
        $extension = empty($extension) ? \Context::getInstance()->getExtensionName() : $extension;

        $this->defaultData();
        $this->setData('scope', $scope);

        if ($this->isXmlHttpRequest()) {
            $this->setView($template, $extension);
        } else {
            $this->setData('content-template', [$template, $extension]);

            $layout = (array)$this->getLayout();
            $this->setView($layout[0], isset($layout[1]) ? $layout[1] : null);
        }
    }

    /**
     * Retrieve the data from the url and make the base initialization
     *
     * @return void
     */
    protected function defaultData()
    {
        parent::defaultData();

        $this->setData('client_params', $this->clientParams);
    }
}
