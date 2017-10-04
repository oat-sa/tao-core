<?php

namespace oat\test\model\Tree;

use oat\tao\model\Tree\TreeWrapper;
use PHPUnit_Framework_TestCase;

class TreeWrapperTest extends PHPUnit_Framework_TestCase
{
	public function testGetSortedTreeByName()
	{
		$sortedShouldBe = [
			'children' => [
				[
					'data' => 'ZBase 1',
					'type' => 'class',
				],
				[
					'data' => 'ABase 1',
					'type' => 'instance',
				],
				[
					'data' => 'Base 1',
					'type' => 'instance',
				],
			]
		];

		$rawArray =[
			'children' => [
				[
					'data' => 'Base 1',
					'type' => 'instance',
				],
				[
					'data' => 'ABase 1',
					'type' => 'instance',
				],
				[
					'data' => 'ZBase 1',
					'type' => 'class',
				],
			]
		];
		$treeWrapper = new TreeWrapper($rawArray);

		$treeWrapperSorted = $treeWrapper->getSortedTreeByName();

		$this->assertEquals($sortedShouldBe, $treeWrapperSorted->getTreeArray());
	}

	public function testGetDefaultChildren()
	{
		$treeWrapper = new TreeWrapper(['bla']);
		$default = $treeWrapper->getDefaultChildren();

		$this->assertEquals([], $default->getTreeArray());
	}

}