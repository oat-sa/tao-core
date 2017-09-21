<?php

namespace oat\tao\model\Tree;

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
	 * @return TreeWrapper
	 */
	public function getSortedTreeByName()
	{
		$sortedArray = $this->treeArray;

		if (isset($sortedArray['children'])) {
			usort($sortedArray['children'], array($this, 'sortTreeNodes'));
		} elseif(array_values($sortedArray) === $sortedArray) {//is indexed array
			usort($sortedArray, array($this, 'sortTreeNodes'));
		}

		return new self($sortedArray);
	}

	/**
	 * @return TreeWrapper
	 */
	public function getDefaultChildren()
	{
		$treeArray = isset($this->treeArray['children']) ? $this->treeArray['children'] : array();

		return new self($treeArray);
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
	protected function sortTreeNodes($a, $b) {
		if (isset($a['data']) && isset($b['data'])) {
			if ($a['type'] != $b['type']) {
				return ($a['type'] == 'class') ? -1 : 1;
			} else {
				return strcasecmp($a['data'], $b['data']);
			}
		}
	}
}