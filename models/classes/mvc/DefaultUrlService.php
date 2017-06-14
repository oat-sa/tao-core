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
namespace oat\tao\model\mvc;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\mvc\DefaultUrlModule\RedirectResolveInterface;

class DefaultUrlService extends ConfigurableService
{
    const SERVICE_ID = 'tao/urlroute';

    /**
     *
     * @param string $name
     * @return array
     */
    public function getUrlRoute($name) {
        return $this->getOption($name);
    }

    public function getUrl($name , array $params = array()) {
        $route = $this->getOption($name);
        return _url($route['action'], $route['controller'], $route['ext'], $params);
    }

    /**
     *
     * @return string
     */
    public function getLoginUrl(array $params = array()) {
        return $this->getUrl('login' , $params);
    }

    /**
     *
     * @return string
     */
    public function getLogoutUrl(array $params = array()) {
        return $this->getUrl('logout' , $params);
    }

    /**
     *
     * @return string
     */
    public function getDefaultUrl(array $params = array()) {
        return $this->getUrl('default' , $params);
    }

    /**
     * Get the config associated to given $name
     *
     * @param $name
     * @return mixed
     * @throws \common_Exception
     */
    public function getRoute($name)
    {
        if (! $this->hasOption($name)) {
            throw new \common_Exception('Route ' . $name . ' not found into UrlService config');
        }
        return $this->getOption($name);
    }

    /**
     * Get all routes from the configuration
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->getOptions();
    }

    /**
     * Set the config associated to given $name
     *
     * @param $name
     * @param array $value
     */
    public function setRoute($name, array $value)
    {
        $this->setOption($name, $value);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getRedirectUrl($name) {
        if($this->hasOption($name)) {
            $options = $this->getOption($name);
            if(array_key_exists('redirect', $options)) {
                if(is_string($options['redirect']) && filter_var($options['redirect'] ,FILTER_VALIDATE_URL )) {
                    \common_Logger::w('deprecated usage or redirect');
                    return $options['redirect'];
                }
                return $this->resolveRedirect($options['redirect']);
            }
        }
        return '';
    }

    /**
     * @param array $redirectParams
     * @return string
     * @throws \common_exception_Error
     */
    protected function resolveRedirect(array $redirectParams) {
        $redirectAdapterClass = $redirectParams['class'];
        if(is_a($redirectAdapterClass , RedirectResolveInterface::class , true )) {
            /**
             * @var RedirectResolveInterface $redirectAdapter
             */
            $redirectAdapter = new $redirectAdapterClass();
            return $redirectAdapter->resolve($redirectParams['options']);
        }
        throw new \common_exception_Error('invalid redirect resolver class ' . $redirectAdapterClass . '. it must implements ' . RedirectResolveInterface::class);
    }

}

