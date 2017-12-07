<?php

namespace oat\tao\model\Tree;

use oat\generis\model\kernel\persistence\smoothsql\search\filter\FilterOperator;

class TreeWrapper
{
	/** @var  array */
	protected $treeArray;

	/**
	 * @param array $treeArray
	 */
	public function __construct(array $treeArray)
	{
		$this->treeArray = $treeArray;
	}

	/**
     * @deprecated tree nodes will be sorted by name by default
	 * @return TreeWrapper
	 */
	public function getSortedTreeByName()
 	{
 		return $this;
 	}

	/**
	 * @param []Filter
	 * @return TreeWrapper
	 */
	public function filterTree(array $filters)
	{
		$filteredArray = $this->treeArray;

		$childrenMatch = [];

		foreach ($filters as $filter) {

		    if (empty($filteredArray['children'])) {
		        continue;
            }

			foreach ($filteredArray['children'] as $node) {
				$endDate = (int)(string)$node['attributes'][$filter->getKey()];

				if ($filter->getOperator() === FilterOperator::GREATER_THAN_EQUAL
					&& ($filter->getValue() < $endDate || $endDate === 0)) {

					$childrenMatch[] = $node;
					continue;
				}
			}
		}

		$filteredArray['children'] = $childrenMatch;
		$filteredArray['count']  = count($childrenMatch);

		return new self($filteredArray);
	}

	/**
	 * @param $limit
	 * @param $offset
	 * @return TreeWrapper
	 */
	public function applyLimitAndOffset($limit, $offset)
	{
		$array = $this->treeArray;

		$array['children'] = array_slice($array['children'], $offset, $limit);

		return new self($array);
	}

	/**
	 * @return TreeWrapper
	 */
	public function getDefaultChildren()
	{
		$treeArray = $this->treeArray;

		$treeArray['children'] = isset($treeArray['children']) ? $treeArray['children'] : array();

		return new self($treeArray['children']);
	}

	/**
	 * @return array
	 */
	public function getTreeArray()
	{
		return $this->treeArray;
	}

	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	protected function sortTreeNodes($a, $b)
	{
		if (isset($a['data']) && isset($b['data'])) {
			if ($a['type'] != $b['type']) {
				return ($a['type'] == 'class') ? -1 : 1;
			} else {
				return strcasecmp($a['data'], $b['data']);
			}
		}
	}
}