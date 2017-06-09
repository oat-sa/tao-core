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
 *
 */

namespace oat\tao\model\cliArgument\argument\implementation\verbose;

use oat\oatbox\action\Action;
use oat\oatbox\log\ColoredVerboseLogger;
use oat\oatbox\log\LoggerService;
use oat\oatbox\log\VerboseLogger;
use oat\oatbox\PhpSerializable;
use oat\oatbox\PhpSerializeStateless;
use oat\tao\model\cliArgument\argument\Argument;
use Psr\Log\LoggerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class Verbose implements Argument, ServiceLocatorAwareInterface, PhpSerializable
{
    use ServiceLocatorAwareTrait;
    use PhpSerializeStateless;

    /**
     * @var bool
     */
    protected $hideColors = false;

    /**
     * Sets the output color's visibility.
     *
     * @param array $params
     */
    protected function setOutputColorVisibility(array $params)
    {
        if ($this->hasParameter($params, '-nc') || $this->hasParameter($params, '--no-color')) {
            $this->hideColors = true;
        }
    }

    /**
     * Return the Psr3 logger level minimum to send log to logger
     *
     * @return string
     */
    abstract protected function getMinimumLogLevel();

    /**
     * Propagate the argument process to Action
     * To load a verbose logger, a check is done Action interfaces to find LoggerInterface
     * The verbose logger is loaded with the minimum level requires
     *
     * @param Action $action
     */
    public function load(Action $action)
    {
        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger(
                $this->getServiceLocator()->get(LoggerService::SERVICE_ID)
                    ->addLogger(
                        $this->hideColors
                            ? new VerboseLogger($this->getMinimumLogLevel())
                            : new ColoredVerboseLogger($this->getMinimumLogLevel())
                    )
            );
        }
    }

    /**
     * Find a parameter $name into $params arguments
     * If $value is defined, check if following parameter equals to given $value
     *
     * @param array $params
     * @param $name
     * @param null $value
     * @return bool
     */
    protected function hasParameter(array $params, $name, $value = null)
    {
        $found = in_array($name, $params);
        if (is_null($value) || !$found) {
            return $found;
        }
        $paramValueIndex = array_search($name, $params) + 1;
        return isset($params[$paramValueIndex]) && ($params[$paramValueIndex] == $value);
    }
}