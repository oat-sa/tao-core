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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\requiredAction\implementation;

use oat\tao\model\requiredAction\RequiredActionAbstract;
use oat\tao\model\routing\FlowController;

/**
 * Class RequiredAction
 *
 * RequiredAction is action which should be executed by user before performing any activities in the TAO
 *
 * @package oat\tao\model\requiredAction\implementation
 */
class RequiredActionRedirectUrlPart extends RequiredActionAbstract
{
    /**
     * Route to be ignored
     *
     * @var array
     */
    protected $excludedRoutes = [
        [
            'extension' => 'tao',
            'module' => 'ClientConfig',
            'action' => 'config',
        ]
    ];

    /**
     * Array of url parts
     *
     * @var array
     */
    protected $url;

    /**
     * RequiredActionRedirectUrlPart constructor.
     * @param string $name
     * @param array $rules
     * @param array $url
     */
    public function __construct($name, array $rules, array $url)
    {
        parent::__construct($name, $rules);
        $this->url = $url;
    }

    /**
     * Execute an action
     *
     * @param array $params
     * @return string The callback url
     */
    public function execute(array $params = [])
    {
        $context = \Context::getInstance();
        $excludedRoutes = $this->getExcludedRoutes();
        $currentRoute = [
            'extension' => $context->getExtensionName(),
            'module' => $context->getModuleName(),
            'action' => $context->getActionName(),
        ];

        if (! in_array($currentRoute, $excludedRoutes)) {
            $currentUrl = \common_http_Request::currentRequest()->getUrl();

            $transformedUrl = $this->getTransformedUrl($params);
            $url = $transformedUrl . (parse_url($transformedUrl, PHP_URL_QUERY) ? '&' : '?') . 'return_url=' . urlencode($currentUrl);

            $flowController = new FlowController();
            $flowController->redirect($url);
        }
    }

    /**
     * @see \oat\oatbox\PhpSerializable::__toPhpCode()
     */
    public function __toPhpCode()
    {
        $class = get_class($this);
        $name = $this->name;
        $rules = \common_Utils::toHumanReadablePhpString($this->getRules());
        $url = \common_Utils::toHumanReadablePhpString($this->url);
        return "new $class(
            '$name',
            $rules,
            $url
        )";
    }

    /**
     * Get url string from $this->url
     *
     * @param array $params
     * @return string
     */
    protected function getTransformedUrl(array $params = [])
    {
        return call_user_func_array('_url', array_merge($this->url, [$params]));
    }

    /**
     * Some actions should not be redirected (such as retrieving requireJs config)
     *
     * @return array
     */
    protected function getExcludedRoutes()
    {
        $result = $this->excludedRoutes;
        $resolver = new \Resolver($this->getTransformedUrl());

        $result[] = [
            'extension' => $resolver->getExtensionFromURL(),
            'module' => $resolver->getModule(),
            'action' => $resolver->getAction(),
        ];

        return $result;
    }
}