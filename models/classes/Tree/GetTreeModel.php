<?php

namespace oat\tao\model\Tree;

use common_exception_IsAjaxAction;
use oat\tao\model\GenerisTreeFactory;
use tao_helpers_Request;

class GetTreeModel
{
	/** @var array */
	private $filterCollection;

	/** @var array */
	private $optionsFilter;

	/**
	 * @param array $filterCollection
	 * @param array $optionsFilter
	 */
	public function __construct(array $filterCollection = [], array $optionsFilter = [])
	{
		$this->filterCollection = $filterCollection;
		$this->optionsFilter = $optionsFilter;
	}

	/**
	 * @param GetTreeRequest $request
	 * @return TreeWrapper
	 *
	 * @throws common_exception_IsAjaxAction
	 */
	public function handle(GetTreeRequest $request)
	{
		if(!tao_helpers_Request::isAjax()){
			throw new common_exception_IsAjaxAction(__FUNCTION__);
		}

		$factory = new GenerisTreeFactory(
			$request->isShowInstance(),
			$request->getOpenNodes(),
			$request->getLimit(),
			$request->getOffset(),
			$request->getResourceUrisToShow(),
			$this->filterCollection,
			$this->optionsFilter
		);

		$treeWrapper = new TreeWrapper($factory->buildTree($request->getClass()));
		$treeWrapper = $treeWrapper->getSortedTreeByName();


		if ($request->isHideNode()) {
			$treeWrapper = $treeWrapper->getDefaultChildren();
		}

		return $treeWrapper;
	}
}