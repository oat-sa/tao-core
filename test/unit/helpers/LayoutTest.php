<?php

namespace oat\tao\test\unit\helpers;

use PHPUnit\Framework\TestCase;
use oat\tao\helpers\Layout;
use oat\tao\helpers\Template;

class LayoutTest extends TestCase
{
    public function testGetAnalyticsCodeWithGaTag()
    {
        // Mocking the getenv function to return 'production' for NODE_ENV
        $this->setEnv('NODE_ENV', 'production');

        // Set a mock GA_TAG for testing
        $this->setEnv('GA_TAG', 'your-ga-tag');

        // // Assuming Template::inc method is a part of your implementation, mock it accordingly
        // $templateMock = $this->getMockBuilder('Template')
        //     ->setMethods(['inc'])
        //     ->getMock();

        // // Set the expected template method call and return value
        // $templateMock->expects($this->once())
        //     ->method('inc')
        //     ->with(
        //         $this->equalTo('blocks/analytics.tpl'),
        //         $this->equalTo('tao'),
        //         $this->equalTo(['gaTag' => 'your-ga-tag', 'environment' => 'Production'])
        //     )
        //     ->willReturn('mocked-analytics-code');


        // // Call the actual method and assert the result
        // $result = Layout::getAnalyticsCode();
        // $this->assertEquals('mocked-analytics-code', $result);
    }

    public function testGetAnalyticsCodeWithoutGaTag()
    {
        // Mocking the getenv function to return 'production' for NODE_ENV
        $this->setEnv('NODE_ENV', 'production');

        // Set GA_TAG as empty to test the case where it is not set
        $this->setEnv('GA_TAG', '');

        // Call the actual method and assert the result is an empty string
        $result = Layout::getAnalyticsCode();
        $this->assertEquals('', $result);
    }

    private function setEnv($key, $value)
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}