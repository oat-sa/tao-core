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
 *
 * Factory to prepare the ontology data for the
 * javascript generis tree
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
namespace oat\tao\model;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\helpers\TreeHelper;
use tao_helpers_Uri;

class GenerisTreeFactory
{
	/**
	 * All siblings of this resource will be loaded, independent of current limit
	 * @var core_kernel_classes_Resource|null
	 */
	private $resourceToShow;
	/**
	 * @var int
	 */
	private $limit;
	/**
	 * @var int
	 */
	private $offset;
	/**
	 * @var array
	 */
	private $propertyFilter = array();
	private $openNodes = array();
	/**
	 * @var bool
	 */
	private $showResources;

	/**
	 * @var core_kernel_classes_Class
	 */
	private $class;

	/**
	 * @param core_kernel_classes_Class $class
	 * @param boolean $showResources
	 * @param array $openNodes
	 * @param int $limit
	 * @param int $offset
	 * @param array $propertyFilter filter resources based on properties uri => value
	 * @param string $resourceUriToShow All siblings of this resource will be loaded, independent of current limit
	 */
	public function __construct(core_kernel_classes_Class $class, $showResources, array $openNodes = array(), $limit = 10, $offset = 0, array $propertyFilter = array(), $resourceUriToShow = null)
	{
		$this->class          = $class;
		$this->limit          = (int) $limit;
		$this->offset         = (int) $offset;
		$this->propertyFilter = $propertyFilter;
		$this->openNodes      = $openNodes;
		$this->showResources  = $showResources;

		if ($resourceUriToShow) {
			$this->resourceToShow = new core_kernel_classes_Resource($resourceUriToShow);
		}
	}

	/**
	 * builds the data for a generis tree
	 * @return array
	 */
    public function buildTree() {
	    return $this->classToNode($this->class, null);
    }

	/**
	 * Builds a class node including it's content
	 *
	 * @param core_kernel_classes_Class $class
	 * @param core_kernel_classes_Class $parent
	 *
	 * @return array
	 */
    private function classToNode(core_kernel_classes_Class $class, core_kernel_classes_Class $parent = null) {
    	$label = $class->getLabel();
        $label = empty($label) ? __('no label') : $label;
        $returnValue = $this->buildClassNode($class, $parent);

        $instancesCount = (int) $class->countInstances();
        
        // allow the class to be opened if it contains either instances or subclasses
        if ($instancesCount > 0 || count($class->getSubClasses(false)) > 0) {
	        if (in_array($class->getUri(), $this->openNodes)) {
                    $returnValue['state']	= 'open';

		            $returnValue['children'] = $this->buildChildNodes($class);

            } else {
                    $returnValue['state']	= 'closed';
            }

            // only show the resources count if we allow resources to be viewed
	        if ($this->showResources) {
                if(!empty($propertyFilter)){
                     $returnValue['count'] = count($class->searchInstances($propertyFilter, array('recursive' => false)));
                } else  {
                    $returnValue['count'] = $instancesCount;
                }
            }
        }
        return $returnValue;
    }

	/**
	 * Builds the content of a class node including it's content
	 *
	 * @param core_kernel_classes_Class $class
	 *
	 * @return array
	 */
    private function buildChildNodes(core_kernel_classes_Class $class) {
    	$childs = array();
    	// subclasses
		foreach ($class->getSubClasses(false) as $subclass) {
			$childs[] = $this->classToNode($subclass, $class);
		}
		// resources
	    if ($this->showResources) {

		    $limit = $this->limit;

		    //load all instances of currently opened class if we have resource specified to be shown
		    if ($this->resourceToShow && $this->resourceToShow->hasType($class)) {
			    $limit = 0;
		    }

		    $searchResult = $class->searchInstances($this->propertyFilter, array(
				'limit'		=> $limit,
				'offset'	=> $this->offset,
				'recursive'	=> false
			));
			
			foreach ($searchResult as $instance){
				$childs[] = TreeHelper::buildResourceNode($instance, $class);
			}
		}
		return $childs;
    }

	/**
	 * generis tree representation of a class node
	 * without it's content
	 *
	 * @param core_kernel_classes_Class $class
	 * @param core_kernel_classes_Class $parent
	 *
	 * @return array
	 */
    private function buildClassNode(core_kernel_classes_Class $class, core_kernel_classes_Class $parent = null) {
    	$label = $class->getLabel();
		$label = empty($label) ? __('no label') : $label;
		return array(
			'data' 	=> _dh($label),
			'type'	=> 'class',
			'attributes' => array(
				'id' => tao_helpers_Uri::encode($class->getUri()),
				'class' => 'node-class',
			    'data-uri' => $class->getUri(),
			    'data-classUri' => is_null($parent) ? null : $parent->getUri(),
			)
		);
    }

}
