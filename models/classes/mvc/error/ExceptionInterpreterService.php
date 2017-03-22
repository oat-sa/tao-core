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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\tao\model\mvc\error;

use oat\oatbox\service\ConfigurableService;

/**
 * Registry for exception interpreter
 *
 * @access public
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package tao
 */
class ExceptionInterpreterService extends ConfigurableService
{
    const SERVICE_ID = 'tao/ExceptionInterpreterService';

    const OPTION_INTERPRETERS = 'interpreters';

    /**
     * @param \Exception $e
     * @return ExceptionInterpretor
     */
    public function getExceptionInterpreter(\Exception $e)
    {
        $interpreters = $this->hasOption(self::OPTION_INTERPRETERS) ?
            $this->getOption(self::OPTION_INTERPRETERS) : [];

        $exceptionClassesHierarchy = $this->getClassesHierarchy($e);

        $foundInterpreters = [];
        foreach ($interpreters as $configuredExceptionClass => $configuredInterpreterClass) {
            $configuredExceptionClass = trim($configuredExceptionClass, '\\');
            if (isset($exceptionClassesHierarchy[$configuredExceptionClass])) {
                $foundInterpreters[$exceptionClassesHierarchy[$configuredExceptionClass]] = $configuredInterpreterClass;
            }
        }

        $interpreterClass = $foundInterpreters[min(array_keys($foundInterpreters))];
        $result = new $interpreterClass;

        $result->setException($e);
        $result->setServiceLocator($this->getServiceManager());
        return $result;
    }

    /**
     * Function calculates exception classes hierarchy
     *
     * Example:
     *
     * Given hierarchy:
     * class B extends A {}
     * class A extends Exception {}
     * //B => A => Exception
     *
     * $this->getClassesHierarchy(new B)
     *
     * Result:
     * [
     *   'B' => 0,
     *   'A' => 1,
     *   'Exception' => 2,
     * ]
     *
     *
     * @param \Exception $e
     * @return array where key is class name and value is index in the hierarchy
     */
    protected function getClassesHierarchy(\Exception $e)
    {
        $exceptionClass = get_class($e);
        $exceptionClassesHierarchy = array_values(class_parents($exceptionClass));
        array_unshift($exceptionClassesHierarchy, $exceptionClass);
        $exceptionClassesHierarchy = array_flip($exceptionClassesHierarchy);
        return $exceptionClassesHierarchy;
    }
}