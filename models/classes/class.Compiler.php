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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * An abstract compiler
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoDelivery
 * @subpackage models_classes
 */
abstract class tao_models_classes_Compiler
{
    /**
     * Resource to be compiled
     * @var core_kernel_classes_Resource
     */
    private $resoure;
    
    /**
     * @var tao_models_classes_service_FileStorage
     */
    private $compilationStorage = null;
    
    /**
     * 
     * @param core_kernel_classes_Resource $resource
     */
    public function __construct(core_kernel_classes_Resource $resource, $storage = null) {
        $this->resoure = $resource;
        $this->compilationStorage = $storage;
    }
    
    public function setStorage(tao_models_classes_service_FileStorage $storage) {
        $this->compilationStorage = $storage;
    }
    
    public function getStorage() {
        return $this->compilationStorage;
    }
    
    /**
     * @return core_kernel_classes_Resource
     */
    protected function getResource() {
        return $this->resoure;
    }
    
    protected function spawnPublicDirectory() {
        if (is_null($this->compilationStorage)) {
            throw new common_Exception('No storage defined for compiler');
        }
        return $this->compilationStorage->spawnDirectory(true);
    }
    
    protected function spawnPrivateDirectory() {
        if (is_null($this->compilationStorage)) {
            throw new common_Exception('No storage defined for compiler');
        }
        return $this->compilationStorage->spawnDirectory(false);
    }
    
    protected abstract function getSubCompilerClass($resource);
    
    protected function subCompile($resource) {
        $compilerClass = $this->getSubCompilerClass($resource);
        if (!class_exists($compilerClass)) {
            throw new common_exception_Error('Class '.$compilerClass.' not found while instanciating Compiler');
        }
        if (!is_subclass_of($compilerClass, __CLASS__)) {
            throw new common_exception_Error('Compiler class '.$compilerClass.' is not a compiler');
        }
        $compiler = new $compilerClass($resource, $this->getStorage());
        return $compiler->compile();
    }
    
    /**
     * Compile the resource into a runnable service
     * using the provided directory as storage
     * 
     * @return tao_models_classes_service_ServiceCall
     * @throws tao_models_classes_CompilationFailedException
     */
    public abstract function compile();
}