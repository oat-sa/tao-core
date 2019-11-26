<?php

declare(strict_types=1);

namespace oat\test\model\Tree;

use oat\generis\test\TestCase;
use oat\tao\model\Tree\TreeWrapper;

class TreeWrapperTest extends TestCase
{
    public function testGetDefaultChildren(): void
    {
        $treeWrapper = new TreeWrapper(['bla']);
        $default = $treeWrapper->getDefaultChildren();

        $this->assertSame([], $default->getTreeArray());
    }
}
