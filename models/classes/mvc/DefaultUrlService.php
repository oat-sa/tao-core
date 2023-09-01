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

use common_Exception;
use common_exception_Error;
use common_Logger;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\mvc\DefaultUrlModule\RedirectResolveInterface;

class DefaultUrlService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/urlroute';
    private const ENV_TAO_LOGIN_URL = 'TAO_LOGIN_URL';

    private const ENV_REDIRECT_AFTER_LOGOUT_URL = 'REDIRECT_AFTER_LOGOUT_URL';

    private const REDIRECTS_WITH_ENV_VAR_SUPPORT = [
        'logout' => self::ENV_REDIRECT_AFTER_LOGOUT_URL,
    ];

    /**
     *
     * @param string $name
     * @return array
     */
    public function getUrlRoute($name)
    {
        return $this->getOption($name);
    }

    public function getUrl($name, array $params = [])
    {
        $route = $this->getOption($name);
        return _url($route['action'], $route['controller'], $route['ext'], $params);
    }

    /**
     *
     * @return string
     */
    public function getLoginUrl(array $params = [])
    {
        if (isset($_ENV[self::ENV_TAO_LOGIN_URL])) {
            return $_ENV[self::ENV_TAO_LOGIN_URL];
        }

        return $this->getUrl('login', $params);
    }

    /**
     *
     * @return string
     */
    public function getLogoutUrl(array $params = [])
    {
        return $this->getUrl('logout', $params);
    }

    /**
     *
     * @return string
     */
    public function getDefaultUrl(array $params = [])
    {
        return $this->getUrl('default', $params);
    }

    /**
     * Get the config associated to given $name
     *
     * @param $name
     * @return mixed
     * @throws common_Exception
     */
    public function getRoute($name)
    {
        if (!$this->hasOption($name)) {
            throw new common_Exception('Route ' . $name . ' not found into UrlService config');
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
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     */
    public function getRedirectUrl($name)
    {
        if ($this->hasOption($name)) {
            $redirectViaEnvVar = $this->getRedirectByEnvVar($name);

            if ($redirectViaEnvVar !== null) {
                return $redirectViaEnvVar;
            }
            $options = $this->getOption($name);
            if (array_key_exists('redirect', $options)) {
                return $this->createRedirect($options['redirect']);
            }
        }
        return '';
    }

    /**
     * @param array $redirectParams
     * @return string
     * @throws common_exception_Error
     * @throws InvalidServiceManagerException
     */
    protected function resolveRedirect(array $redirectParams)
    {
        $redirectAdapterClass = $redirectParams['class'];
        if (is_a($redirectAdapterClass, RedirectResolveInterface::class, true)) {
            /**
             * @var RedirectResolveInterface $redirectAdapter
             */
            $redirectAdapter = new $redirectAdapterClass();
            if ($redirectAdapter instanceof ServiceLocatorAwareInterface) {
                $redirectAdapter->setServiceLocator($this->getServiceManager());
            }

            return $redirectAdapter->resolve($redirectParams['options']);
        }
        throw new common_exception_Error(
            'invalid redirect resolver class ' . $redirectAdapterClass . '. it must implements '
            . RedirectResolveInterface::class
        );
    }

    /**
     * @param string|array $redirect
     * @return string
     * @throws common_exception_Error
     * @throws InvalidServiceManagerException
     */
    public function createRedirect($redirect)
    {
        if (is_string($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)) {
            common_Logger::w('deprecated usage or redirect');
            return $redirect;
        }
        return $this->resolveRedirect($redirect);
    }

    private function getRedirectByEnvVar(string $name): ?string
    {
        $redirectUrl = null;
        if (array_key_exists($name, self::REDIRECTS_WITH_ENV_VAR_SUPPORT)) {
            $redirectUrl = $_ENV[self::REDIRECTS_WITH_ENV_VAR_SUPPORT[$name]] ?? null;
        }
        return $redirectUrl;
    }
}
