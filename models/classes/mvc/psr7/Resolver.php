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
 *  Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
namespace oat\tao\model\mvc\psr7;

use common_http_Request;
use oat\tao\model\mvc\psr7\Exception\ResolverException;
use oat\tao\model\routing\Route;
use Psr\Http\Message\ServerRequestInterface;
use oat\tao\model\routing\Resolver as TaoResolver;

class Resolver extends TaoResolver
{

    /**
     * Request to be resolved
     *
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var string
     */
    protected $relativeUrl;

    protected $extensionId;

    protected $controller;

    protected $action;

    /**
     * Resolver constructor.
     * declared to remove required request from contructor
     */
    public function __construct() {

    }

    /**
     * @return string
     */
    public function getRelativeUrl()
    {
        return trim($this->relativeUrl, '/');
    }

    /**
     * @param string $relativeUrl
     * @return $this
     */
    public function setRelativeUrl($relativeUrl)
    {

        $this->relativeUrl = $relativeUrl;
        return $this;
    }


    /**
     * Resolver set PSR7 request.
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request) {
        $this->request = $request;
    }

    public function getExtensionId() {
        if (is_null($this->extensionId)) {
            $this->resolve();
        }
        return $this->extensionId;
    }

    public function getControllerClass() {
        if (is_null($this->controller)) {
            $this->resolve();
        }
        return $this->controller;
    }

    public function getMethodName() {
        if (is_null($this->action)) {
            $this->resolve();
        }
        return $this->action;
    }

    /**
     * Get the controller short name as used into the URL
     * @return string the name
     */
    public function getControllerShortName() {

        $parts = explode('/', $this->getRelativeUrl());
        if(count($parts) == 3){
            return $parts[1];
        }
        return null;
    }

    /**
     * Tries to resolve the current request using the routes first
     * and then falls back to the legacy controllers
     * @return bool
     * @throws ResolverException
     */
    protected function resolve() {
        $relativeUrl =  $this->getRelativeUrl();

        if(!empty($relativeUrl)) {
            foreach ($this->getRouteMap() as $entry) {
                /**
                 * @var Route $called
                 */
                $route = $entry['route'];
                $called = $route->resolve($relativeUrl);
                if (!is_null($called)) {
                    list($controller, $action) = explode('@', $called);
                    $this->controller = $controller;
                    $this->action = $action;
                    $this->extensionId = $entry['extId'];
                    return true;
                }
            }

            throw new ResolverException('Unable to resolve '.$this->request->getUri());
        }
    }

}