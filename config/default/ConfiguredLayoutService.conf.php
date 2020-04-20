<?php

declare(strict_types=1);

use oat\tao\model\layout\configuredLayout\LayoutPageTitleService;
use oat\tao\model\layout\ConfiguredLayoutService;

return new ConfiguredLayoutService([
    ConfiguredLayoutService::OPTION_PAGE_TITLE_SERVICE => LayoutPageTitleService::class,
]);
