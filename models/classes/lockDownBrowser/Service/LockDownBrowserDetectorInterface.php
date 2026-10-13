<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\lockDownBrowser\Service;

interface LockDownBrowserDetectorInterface
{
    /**
     * @param mixed|null $clientKioskApiOk Explicit value from Runner/init (preferred over superglobals)
     *
     * @return string Entity\LockDownBrowser::PROVIDER_* value
     */
    public function detect($clientKioskApiOk = null): string;
}
