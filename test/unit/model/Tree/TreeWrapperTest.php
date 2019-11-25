<?php

namespace oat\test\model\Tree;

use oat\generis\test\TestCase;
use oat\tao\model\Tree\TreeWrapper;

class TreeWrapperTest extends TestCase
{
    public function testGetDefaultChildren()
    {
        $treeWrapper = new TreeWrapper(['bla']);
        $default = $treeWrapper->getDefaultChildren();

        $this->assertEquals([], $default->getTreeArray());
    }
}
