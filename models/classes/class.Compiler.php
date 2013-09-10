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
    private $resoure;
    
    public function __construct(core_kernel_classes_Resource $resource) {
        $this->resoure = $resource;
    }
    
    /**
     * @return core_kernel_classes_Resource
     */
    protected function getResource() {
        return $this->resoure;
    }
    
    /**
     * Creates an appropriate subdirectory for a resource's compilation
     * 
     * @param core_kernel_file_File $directory
     * @param core_kernel_classes_Resource $resource
     * @throws taoItems_models_classes_CompilationFailedException
     * @return core_kernel_versioning_File
     */
    protected function createSubDirectory(core_kernel_file_File $directory, core_kernel_classes_Resource $resource)
    {
        $subDirectory = substr($resource->getUri(), strpos($resource->getUri(), '#') + 1);
        $relPath = $directory->getRelativePath().DIRECTORY_SEPARATOR.$subDirectory;
        $absPath = $directory->getAbsolutePath().DIRECTORY_SEPARATOR.$subDirectory;
    
        if (! is_dir($absPath)) {
            if (! mkdir($absPath)) {
                throw new taoItems_models_classes_CompilationFailedException('Could not create sub directory \'' . $absPath . '\'');
            }
        }
    
        return $directory->getFileSystem()->createFile('', $relPath);
    }
    
    /**
     * Compile the resource into a runnable service
     * using the provided directory as storage
     * 
     * @param core_kernel_file_File $destinationDirectory
     * @return tao_models_classes_service_ServiceCall
     */
    public abstract function compile(core_kernel_file_File $destinationDirectory);
}