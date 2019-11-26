<?php

declare(strict_types=1);

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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
use oat\tao\model\service\ServiceFileStorage;

/**
 * An abstract compiler
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoDelivery
 */
abstract class tao_models_classes_Compiler implements \Zend\ServiceManager\ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * Resource to be compiled
     * @var core_kernel_classes_Resource
     */
    private $resource;

    /**
     * @var tao_models_classes_service_FileStorage
     */
    private $compilationStorage = null;

    /**
     * A context object the compiler can use
     * @var mixed
     */
    private $context;

    /**
     * @param core_kernel_classes_Resource $resource
     * @param ServiceFileStorage $storage
     */
    public function __construct(core_kernel_classes_Resource $resource, ServiceFileStorage $storage)
    {
        $this->resource = $resource;
        $this->compilationStorage = $storage;
    }

    /**
     * Gets the context object the compiler can use
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the context object the compiler can use
     * @param mixed $context
     * @return tao_models_classes_Compiler
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Compile the resource into a runnable service
     * and returns a report that if successful contains the service call
     *
     * @return common_report_Report
     * @throws tao_models_classes_CompilationFailedException
     */
    abstract public function compile();

    /**
     * Returns the storage to be used during compilation
     *
     * @return tao_models_classes_service_FileStorage
     */
    protected function getStorage()
    {
        return $this->compilationStorage;
    }

    /**
     * @return core_kernel_classes_Resource
     */
    protected function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns a directory that is accessible to the client
     *
     * @return tao_models_classes_service_StorageDirectory
     */
    protected function spawnPublicDirectory()
    {
        return $this->compilationStorage->spawnDirectory(true);
    }

    /**
     * Returns a directory that is not accessible to the client
     *
     * @return tao_models_classes_service_StorageDirectory
     */
    protected function spawnPrivateDirectory()
    {
        return $this->compilationStorage->spawnDirectory(false);
    }

    /**
     * helper to create a fail report
     *
     * @param string $userMessage
     * @return common_report_Report
     */
    protected function fail($userMessage)
    {
        return new common_report_Report(
            common_report_Report::TYPE_ERROR,
            $userMessage
        );
    }

    /**
     * Determin the compiler of the resource
     *
     * @param core_kernel_classes_Resource $resource
     * @return string the name of the compiler class
     */
    abstract protected function getSubCompilerClass(core_kernel_classes_Resource $resource);

    /**
     * Compile a subelement of the current resource
     *
     * @param core_kernel_classes_Resource $resource
     * @return common_report_Report returns a report that if successful contains the service call
     */
    protected function subCompile(core_kernel_classes_Resource $resource)
    {
        $compilerClass = $this->getSubCompilerClass($resource);
        if (! class_exists($compilerClass)) {
            common_Logger::e('Class ' . $compilerClass . ' not found while instanciating Compiler');
            return $this->fail(__('%s is of a type that cannot be published', $resource->getLabel()));
        }
        if (! is_subclass_of($compilerClass, __CLASS__)) {
            common_Logger::e('Compiler class ' . $compilerClass . ' is not a compiler');
            return $this->fail(__('%s is of a type that cannot be published', $resource->getLabel()));
        }
        /** @var tao_models_classes_Compiler $compiler */
        $compiler = new $compilerClass($resource, $this->getStorage());
        $compiler->setServiceLocator($this->getServiceLocator());
        $compiler->setContext($this->getContext());
        return $compiler->compile();
    }
}
