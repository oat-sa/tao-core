<?php

use oat\tao\model\clientDetector\ClientDetectorService;
use oat\tao\model\clientDetector\detector\WebBrowserDetector;
use oat\tao\model\clientDetector\detector\OSDetector;

return new ClientDetectorService(array(
    ClientDetectorService::OPTION_WEB_BROWSER_DETECTOR => new WebBrowserDetector(),
    ClientDetectorService::OPTION_OS_DETECTOR => new OSDetector()
));