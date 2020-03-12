<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\envProcessor;

use InvalidArgumentException;
use ReflectionException;

class ParameterBag
{
    protected $parameters = [];
    private $getEnv;
    private $resolving;
    private $processor;

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->add($parameters);
        $this->initProcessor();
    }

    /**
     * @param $name
     * @return array|bool|false|int|mixed|string|null
     * @throws ReflectionException
     */
    protected function getEnv($name)
    {
        if (isset($this->resolving[$envName = "env($name)"])) {
            throw new ParameterCircularReferenceException(array_keys($this->resolving));
        }

        if (!$this->getEnv) {
            $this->getEnv = new \ReflectionMethod($this, __FUNCTION__);
            $this->getEnv->setAccessible(true);
            $this->getEnv = $this->getEnv->getClosure($this);
        }

        if (false !== $i = strpos($name, ':')) {
            $prefix = substr($name, 0, $i);
            $localName = substr($name, 1 + $i);
        } else {
            $prefix = 'string';
            $localName = $name;
        }

        $this->resolving[$envName] = true;
        try {
            return $this->processor->getEnv($prefix, $localName, $this->getEnv);
        } finally {
            unset($this->resolving[$envName]);
        }
    }

    /**
     * @return self
     */
    public function resolve()
    {
        array_walk_recursive($this->parameters, array($this, 'resolveString'));

        return $this;
    }

    /**
     * @param string $value
     * @return array|bool|false|int|mixed|string|null
     * @throws ReflectionException
     */
    public function resolveString(string &$value)
    {
        if (preg_match('/^%([^%\s]+)%$/', $value, $match)) {
            $key = $match[1];

            $value = $this->get($key);
        }

        return $value;
    }

    /**
     * @param string $name
     * @return array|bool|false|int|mixed|string|null
     * @throws ReflectionException
     */
    public function get(string $name)
    {
        if ('env()' !== $name && 0 === strpos($name, 'env(') && ')' === substr($name, -1)) {
            $env = substr($name, 4, -1);

            if (!preg_match('/^(?:\w*+:)*+\w++$/', $env)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid %s name: only "word" characters are allowed.', $name)
                );
            }

            return $this->getEnv($env);
        }

        return $name;
    }

    /**
     * Clears all parameters.
     */
    public function clear()
    {
        $this->parameters = [];
    }

    /**
     * @param array $parameters An array of parameters
     */
    public function add(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * @param string $name The parameter name
     * @param mixed $value The parameter value
     */
    public function set(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return \array_key_exists((string)$name, $this->parameters);
    }

    /**
     * Removes a parameter.
     *
     * @param string $name The parameter name
     */
    public function remove(string $name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * @return EnvVarProcessorInterface
     */
    private function initProcessor()
    {
        if ($this->processor === null) {
            $this->processor = new EnvVarProcessor();
        }

        return $this->processor;
    }
}
