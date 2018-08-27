<?php

namespace oat\test\model\Tree;

use oat\tao\model\Tree\TreeWrapper;
use PHPUnit_Framework_TestCase;

class TreeWrapperTest extends PHPUnit_Framework_TestCase
{

	public function testGetDefaultChildren()
	{
		$treeWrapper = new TreeWrapper(['bla']);
		$default = $treeWrapper->getDefaultChildren();

		$this->assertEquals([], $default->getTreeArray());
	}

}