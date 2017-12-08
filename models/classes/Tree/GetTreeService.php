<?php

namespace oat\tao\model\Tree;

use common_exception_IsAjaxAction;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\GenerisTreeFactory;
use tao_helpers_Request;

class GetTreeService extends ConfigurableService
{
	const SERVICE_ID = 'tao/GetTree';

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
			$request->getFilters(),
			$request->getFiltersOptions(),
			[]
		);

		$treeWrapper = new TreeWrapper($factory->buildTree($request->getClass()));

		if ($request->isHideNode()) {
			$treeWrapper = $treeWrapper->getDefaultChildren();
		}

		return $treeWrapper;
	}
}