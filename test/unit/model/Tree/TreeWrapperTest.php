<?php

namespace oat\test\model\Tree;

use oat\tao\model\Tree\TreeWrapper;
use oat\generis\test\TestCase;

class TreeWrapperTest extends TestCase
{
    public function testGetDefaultChildren()
    {
        $treeWrapper = new TreeWrapper(['bla']);
        $default = $treeWrapper->getDefaultChildren();

        $this->assertEquals([], $default->getTreeArray());
    }
}
