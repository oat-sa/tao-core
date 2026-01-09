<?php

namespace oat\test\model\Tree;

use oat\tao\model\Tree\GetTreeRequest;
use Request;
use PHPUnit\Framework\TestCase;

class GetTreeRequestTest extends TestCase
{
    public function testCreateSuccessfullyWithClassUri()
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();

        $requestMock
            ->expects($this->any())
            ->method('hasParameter')
            ->will($this->onConsecutiveCalls(true, true, true, true, true));

        $requestMock
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->onConsecutiveCalls('http://clasuri', ['childNode1', 'childNode1'], [], 20, 1, true));

        $treeRequest = GetTreeRequest::create($requestMock);

        $this->assertSame('http://clasuri', $treeRequest->getClass()->getUri());
        $this->assertSame(['http://clasuri', 'childNode1', 'childNode1'], $treeRequest->getOpenNodes());
        $this->assertSame(true, $treeRequest->isHideNode());
        $this->assertSame(20, $treeRequest->getLimit());
        $this->assertSame(1, $treeRequest->getOffset());
        $this->assertSame(false, $treeRequest->isShowInstance());
        $this->assertSame([], $treeRequest->getResourceUrisToShow());
    }
}
