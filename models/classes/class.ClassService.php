<?php
/*  
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * This service represents the actions applicable from a root class
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
abstract class tao_models_classes_ClassService
    extends tao_models_classes_GenerisService
{
	/**
	 * Returns the root class of this service
	 * 
	 * @return core_kernel_classes_Class
	 */
	abstract public function getRootClass();
	
	/**
	 * Delete a resource
	 * 
	 * @param core_kernel_classes_Resource $resource
	 * @return boolean
	 */
    public function deleteResource(core_kernel_classes_Resource $resource)
	{
	    return $resource->delete();
	}

	/**
	 * Delete a subclass
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param  core_kernel_classes_Class $clazz
	 * @return boolean
	 */
	public function deleteClass(core_kernel_classes_Class $clazz)
	{
	    $returnValue = (bool) false;

        $subclasses = $clazz->getSubClasses(true);
        if($clazz->isSubClassOf($this->getRootClass()) && !$clazz->equals($this->getRootClass())) {
            /** @var core_kernel_classes_Class $subclass */
            foreach($subclasses as $subclass){
                /** @var core_kernel_classes_Property $classProperty */
                foreach($subclass->getProperties() as $classProperty){
                    $returnValue = $this->deleteClassProperty($classProperty);
                }
                if($returnValue){
                    $returnValue = $subclass->delete();
                }
            }
            if(count($subclasses) === 0 || $returnValue){
                $returnValue = $clazz->delete();
            }
        } else {
            common_Logger::w('Tried to delete class '.$clazz->getUri().' as if it were a subclass of '.$this->getRootClass()->getUri());
        }
	
	    return (bool) $returnValue;
	}


    /**
     * remove a class property and associate indexes
     * @param core_kernel_classes_Property $property
     * @return bool
     */
    public function deleteClassProperty(core_kernel_classes_Property $property){
        $indexes = $property->getPropertyValues(new core_kernel_classes_Property(INDEX_PROPERTY));

        //delete property and the existing values of this property
        if($returnValue = $property->delete(true)){
            //delete index linked to the property
            foreach($indexes as $indexUri){
                $index = new core_kernel_classes_Resource($indexUri);
                $returnValue = $this->deleteIndexProperty($index);
            }
        }

        return $returnValue;
    }

    /**
     * * remove an index property
     * @param core_kernel_classes_Resource $index
     * @return bool
     */
    public function deleteIndexProperty(core_kernel_classes_Resource $index){
        return $index->delete(true);
    }
}