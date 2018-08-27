<?php

namespace oat\tao\model\Tree;

use common_Exception;
use core_kernel_classes_Class;
use oat\tao\helpers\TreeHelper;
use Request;
use tao_helpers_Uri;
use oat\generis\model\OntologyRdfs;

class GetTreeRequest
{
	const DEFAULT_LIMIT = 10;
	const DEFAULT_ORDERDIR = 'asc';

	/** @var  core_kernel_classes_Class */
	protected $class;

	/** @var  int */
	protected $limit;

	/** @var  int */
	protected $offset;

	/** @var  bool */
	protected $showInstance;

	/** @var  bool */
	protected $hideNode;

	/** @var  array */
	protected $openNodes;

	/** @var  array */
	protected $resourceUrisToShow;

	/** @var  array */
	protected $filters = [];

	/** @var  array */
	protected $filtersOptions = [];

	/**
	 * GetTreeRequest constructor.
	 * @param core_kernel_classes_Class $class
	 * @param int $limit
	 * @param int $offset
	 * @param bool $showInstance
	 * @param bool $hideNode
	 * @param array $openNodes
	 * @param array $resourceUrisToShow
	 * @param array $filtersOptions
	 * @param \core_kernel_classes_Property[] $filters Properties to filter tree resources
	 */
	public function __construct($class, $limit, $offset, $showInstance, $hideNode, array $openNodes, array $resourceUrisToShow = [], $filtersOptions = [], array $filters = [])
	{
		$this->class = $class;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->showInstance = $showInstance;
		$this->hideNode = $hideNode;
		$this->openNodes = $openNodes;
		$this->resourceUrisToShow = $resourceUrisToShow;
		$this->filters = $filters;
		$this->filtersOptions = $filtersOptions;
	}

	/**
	 * @param Request $request
	 * @return GetTreeRequest
	 *
	 * @throws common_Exception
	 */
	public static function create(Request $request)
	{

		if ($request->hasParameter('classUri')) {
			$classUri = tao_helpers_Uri::decode($request->getParameter('classUri'));
			$class = new core_kernel_classes_Class($classUri);
			$hideNode = true;
		} elseif ($request->hasParameter('rootNode')) {
			$class = new core_kernel_classes_Class($request->getParameter('rootNode'));
			$hideNode = false;
		} else {
			throw new common_Exception('Missing node information for ' . __FUNCTION__);
		}

		$openNodes = array($class->getUri());
		$openNodesParameter = $request->getParameter('openNodes');
		$openParentNodesParameter = $request->getParameter('openParentNodes');

		if ($request->hasParameter('openNodes') && is_array($openNodesParameter)) {
			$openNodes = array_merge($openNodes, $openNodesParameter);
		} else if ($request->hasParameter('openParentNodes') && is_array($openParentNodesParameter)) {
			$childNodes = $openParentNodesParameter;
			$openNodes = TreeHelper::getNodesToOpen($childNodes, $class);
		}

		$limit = $request->hasParameter('limit') ? $request->getParameter('limit') : self::DEFAULT_LIMIT;
		$offset = $request->hasParameter('offset') ? $request->getParameter('offset') : 0;
		$showInstance = $request->hasParameter('hideInstances') ? !$request->getParameter('hideInstances') : true;
		$orderProp = $request->hasParameter('order') ? $request->getParameter('order') : OntologyRdfs::RDFS_LABEL;
		$orderDir = $request->hasParameter('orderdir') ? $request->getParameter('orderdir') : self::DEFAULT_ORDERDIR;

        $filterProperties = [];
		if ($request->hasParameter('filterProperties')) {
		    $filterProperties = $request->getParameter('filterProperties');
		    if (!is_array($filterProperties)) {
		        $filterProperties = [];
            }
        }

		return new self(
		    $class,
            $limit,
            $offset,
            $showInstance,
            $hideNode,
            $openNodes,
            [],
            [
                'order' => [
                    $orderProp => $orderDir
                ]
            ],
            $filterProperties
        );
	}

	/**
	 * @return core_kernel_classes_Class
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @return bool
	 */
	public function isShowInstance()
	{
		return $this->showInstance;
	}

	/**
	 * @return bool
	 */
	public function isHideNode()
	{
		return $this->hideNode;
	}

	/**
	 * @return array
	 */
	public function getOpenNodes()
	{
		return $this->openNodes;
	}

	/**
	 * @return array
	 */
	public function getResourceUrisToShow()
	{
		return $this->resourceUrisToShow;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @param array $filters
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	/**
	 * @return array
	 */
	public function getFiltersOptions()
	{
		return $this->filtersOptions;
	}

	/**
	 * @param array $filtersOptions
	 */
	public function setFiltersOptions($filtersOptions)
	{
		$this->filtersOptions = $filtersOptions;
	}
}