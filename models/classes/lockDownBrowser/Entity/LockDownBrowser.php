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

namespace oat\tao\model\lockDownBrowser\Entity;

final class LockDownBrowser
{
    public const ONTOLOGY_NAMESPACE_URI = 'http://www.tao.lu/Ontologies/TAOLockDownBrowser.rdf';

    public const PROPERTY_LOCK_DOWN_BROWSER = self::ONTOLOGY_NAMESPACE_URI . '#LockDownBrowser';

    public const PROVIDER_KIOSKED = 'kiosked';

    public const PROVIDER_NONE = '';

    /** Trace variable keys from kioskLockdown runner plugin (`storeTraceData` / variables_storage). */
    public const TRACE_BREACH_KEY_PREFIX = 'kiosk-breach-';

    private function __construct()
    {
    }
}
