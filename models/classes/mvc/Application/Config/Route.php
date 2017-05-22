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
namespace oat\tao\model\mvc\Application\Config;

use oat\tao\model\mvc\Application\Resolution;
use oat\tao\model\mvc\middleware\ControllerRendering;
use oat\tao\model\mvc\middleware\LoadExtensionConstant;
use oat\tao\model\mvc\middleware\TaoAssetConfiguration;
use oat\tao\model\mvc\middleware\TaoAuthenticate;
use oat\tao\model\mvc\middleware\TaoControllerExecution;
use oat\tao\model\mvc\middleware\TaoErrorHandler;
use oat\tao\model\mvc\middleware\TaoInitUser;
use oat\tao\model\mvc\middleware\TaoRestAuthenticate;

class Route
{

    protected $defaultProcess =
        [
            'preProcess' =>
                [
                    TaoInitUser::class,
                    LoadExtensionConstant::class,
                    TaoRestAuthenticate::class,
                    TaoAuthenticate::class,
                    TaoAssetConfiguration::class,
                ],
            'process' =>
                [
                    TaoControllerExecution::class,

                ],
            'postProcess' =>
                [
                    ControllerRendering::class,
                ],
        ];

    protected $extension;

    protected $routeClass;

    protected $preProcess    = [];

    protected $process       = [];

    protected $postProcess   = [];

    protected $routeOptions  = [];

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouteClass()
    {
        return $this->routeClass;
    }

    /**
     * @param mixed $routeClass
     * @return $this
     */
    public function setRouteClass($routeClass)
    {
        $this->routeClass = $routeClass;
        return $this;
    }

    /**
     * @return array
     */
    public function getPreProcess()
    {
        return array_merge($this->defaultProcess['preProcess'] , $this->preProcess);
    }

    /**
     * @param array $preProcess
     * @return $this
     */
    public function setPreProcess($preProcess)
    {
        $this->preProcess = $preProcess;
        return $this;
    }

    /**
     * @return array
     */
    public function getProcess() {
        return array_merge($this->defaultProcess['process'] , $this->process);
    }

    /**
     * @param array $process
     * @return $this
     */
    public function setProcess(array $process) {
        $this->process = $process;
        return $this;
    }

    /**
     * @return array
     */
    public function getPostProcess()
    {
        return array_merge($this->defaultProcess['postProcess']  , $this->postProcess);
    }

    /**
     * @param array $postProcess
     * @return $this
     */
    public function setPostProcess($postProcess)
    {
        $this->postProcess = $postProcess;
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteOptions()
    {
        return $this->routeOptions;
    }

    /**
     * @param array $routeOptions
     * @return $this;
     */
    public function setRouteOptions($routeOptions)
    {
        $this->routeOptions = $routeOptions;
        return $this;
    }



    /**
     * @param $relativeUrl
     * @return bool
     */
    public function match($relativeUrl) {
        return !is_null($this->resolve($relativeUrl));
    }

    /**
     * @param string $relativeUrl
     * @return Resolution
     */
    public function resolve($relativeUrl) {

        $className = $this->getRouteClass();
        $extension = new \common_ext_Extension($this->getExtension());
        /**
         * @var $router \oat\tao\model\routing\Route
         */
        $router = new $className($extension , $this->getExtension() , $this->getRouteOptions());
        $routeResult = $router->resolve($relativeUrl);

        if(is_null($routeResult)) {
            return null;
        }
        return new Resolution($this->getExtension() , $routeResult , $relativeUrl , $this);
    }

}