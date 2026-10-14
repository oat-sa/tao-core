<?php

declare(strict_types=1);

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;

/**
 * @deprecated Services are registered via LockDownBrowserServiceProvider (Symfony DI).
 */
class RegisterLockDownBrowserService extends InstallAction
{
    public function __invoke($params)
    {
        return Report::createSuccess(
            __('Lock down browser services are registered via LockDownBrowserServiceProvider')
        );
    }
}
