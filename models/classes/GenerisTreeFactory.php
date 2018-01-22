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
 *               2017     (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
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
use oat\generis\model\kernel\persistence\smoothsql\search\filter\Filter;
use oat\oatbox\service\ServiceManager;
use oat\generis\model\OntologyRdfs;
use oat\tao\helpers\TreeHelper;
use tao_helpers_Uri;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\generis\model\OntologyAwareTrait;

class GenerisTreeFactory
{
    use OntologyAwareTrait;

    /**
     * All instances of those classes loaded, independent of current limit ( Contain uris only )
     * @var array
     */
    private $browsableTypes = array();

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
    private $openNodes = array();

    /**
      * @var bool
      */
    private $showResources;

    /**
      * @var array contains filters to apply to searchInstances
      */
    private $propertyFilter = [];

	/**
	 * @var array
	 */
    private $optionsFilter = [];

    /** @var array  */
    private $extraProperties = [];
	/**
	 * @param boolean $showResources If `true` resources will be represented in thee. Otherwise only classes.
	 * @param array $openNodes Class uris for which children array should be build as well
	 * @param int $limit Limit of resources to be shown in one class
	 * @param int $offset Offset for resources in one class
	 * @param array $resourceUrisToShow All siblings of this resources will be loaded, independent of current limit
	 * @param array $propertyFilter Additional property filters to apply to the tree
	 * @param array $optionsFilter
	 * @param array $extraProperties
	 */
    public function __construct($showResources, array $openNodes = [], $limit = 10, $offset = 0, array $resourceUrisToShow = [], array $propertyFilter = [], array $optionsFilter = [], array $extraProperties = [])
    {
        $this->limit          = (int) $limit;
        $this->offset         = (int) $offset;
        $this->openNodes      = $openNodes;
        $this->showResources  = $showResources;
        $this->propertyFilter = $propertyFilter;
        $this->optionsFilter  = $optionsFilter;
        $this->extraProperties = $extraProperties;

        $types = array();
        foreach ($resourceUrisToShow as $uri) {
            $resource = new core_kernel_classes_Resource($uri);
            $types[]  = $resource->getTypes();
        }

        if ($types) {
            $this->browsableTypes = array_keys(call_user_func_array('array_merge', $types));
        }
    }

    /**
     * builds the data for a generis tree
     * @param core_kernel_classes_Class $class
     * @return array
     */
    public function buildTree(core_kernel_classes_Class $class) {
        return $this->classToNode($class, null);
    }

    /**
     * Builds a class node including it's content
     *
     * @param core_kernel_classes_Class $class
     * @param core_kernel_classes_Class $parent
     * @return array
     * @throws
     */
    private function classToNode(core_kernel_classes_Class $class, core_kernel_classes_Class $parent = null) {
        $returnValue = $this->buildClassNode($class, $parent);

        $options = array_merge(['recursive' => false], $this->optionsFilter);
        $queryBuilder = $this->getQueryBuilder($class, $this->propertyFilter, $options);
        $instancesCount = $this->getSearchService()->getGateway()->count($queryBuilder);

        // allow the class to be opened if it contains either instances or subclasses
        $subclasses = $this->getSubClasses($class);
        if ($instancesCount > 0 || count($subclasses) > 0) {
	        if (in_array($class->getUri(), $this->openNodes)) {
                $returnValue['state']	= 'open';
                $returnValue['children'] = $this->buildChildNodes($class, $subclasses);
            } else {
                $returnValue['state']	= 'closed';
            }

            // only show the resources count if we allow resources to be viewed
	        if ($this->showResources) {
                $returnValue['count'] = $instancesCount;
            }
        }
        return $returnValue;
    }

    /**
     * Builds the content of a class node including it's content
     *
     * @param core_kernel_classes_Class $class
     * @param core_kernel_classes_Class[] $subclasses
     * @return array
     * @throws
     */
    private function buildChildNodes(core_kernel_classes_Class $class, $subclasses)
    {
        $children = [];

        // subclasses
        foreach ($subclasses as $subclass) {
            $children[] = $this->classToNode($subclass, $class);
        }
        // resources
        if ($this->showResources) {
            $limit = $this->limit;

            if (in_array($class->getUri(), $this->browsableTypes)) {
                $limit = 0;
            }

            $options = array_merge([
                'limit'     => $limit,
                'offset'    => $this->offset,
                'recursive' => false,
                'order'     => [OntologyRdfs::RDFS_LABEL => 'asc'],
            ], $this->optionsFilter);

            $queryBuilder = $this->getQueryBuilder($class, $this->propertyFilter, $options);
            $searchResult = $this->getSearchService()->getGateway()->search($queryBuilder);
            foreach ($searchResult as $instance){
                $children[] = TreeHelper::buildResourceNode($instance, $class, $this->extraProperties);
            }
        }
        return $children;
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
    private function buildClassNode(core_kernel_classes_Class $class, core_kernel_classes_Class $parent = null)
    {
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

    /**
     * @param $class
     * @param $propertyFilter
     * @param $options
     * @return \oat\search\QueryBuilder
     */
    private function getQueryBuilder($class, $propertyFilter, $options)
    {
        $search = $this->getSearchService();
        $queryBuilder = $search->query();
        $query = $search->searchType($queryBuilder, $class->getUri(), $options['recursive']);

        foreach ($propertyFilter as $filterProp => $filterVal) {

            if ($filterVal instanceof Filter) {
                $query->addCriterion($filterVal->getKey(), $filterVal->getOperator(), $filterVal->getValue());
                continue;
            }
            if (!is_array($filterVal)) {
                $filterVal = [];
            }
            foreach ($filterVal as $values) {
                if (is_array($values)) {
                    $query->addCriterion($filterProp, SupportedOperatorHelper::IN, $values);
                } elseif (is_string($values)) {
                    $query->addCriterion($filterProp, SupportedOperatorHelper::CONTAIN, $values);
                }
            }
        }

        $queryBuilder->setCriteria($query);
        if (isset($options['limit'])) {
            $queryBuilder->setLimit($options['limit']);
        }
        if (isset($options['offset'])) {
            $queryBuilder->setOffset($options['offset']);
        }
        if (isset($options['order'])) {
            $queryBuilder->sort($options['order']);
        }
        return $queryBuilder;
    }

    /**
     * @return ComplexSearchService
     */
    private function getSearchService()
    {
        return ServiceManager::getServiceManager()->get(ComplexSearchService::SERVICE_ID);
    }

    /**
     * @param core_kernel_classes_Class $class
     * @return core_kernel_classes_Class[]
     * @throws
     */
    private function getSubClasses(core_kernel_classes_Class $class)
    {
        $search = $this->getSearchService();
        $queryBuilder = $search->query();
        $query = $queryBuilder->newQuery()->add(OntologyRdfs::RDFS_SUBCLASSOF)->equals($class->getUri());
        $queryBuilder->setCriteria($query);
        //classes always sorted by label
        $order = [RDFS_LABEL => 'asc'];
        $queryBuilder->sort($order);
        $result = [];
        $search->setLanguage($queryBuilder, \common_session_SessionManager::getSession()->getInterfaceLanguage());
        foreach ($search->getGateway()->search($queryBuilder) as $subclass) {
            $result[] = $this->getClass($subclass->getUri());
        }
        return $result;
    }
}
