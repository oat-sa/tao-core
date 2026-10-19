<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\lockDownBrowser\Entity;

use JsonSerializable;

final class LockDownBrowserConfiguration implements JsonSerializable
{
    public const ONTOLOGY_NAMESPACE_URI = 'http://www.tao.lu/Ontologies/TAOLockDownBrowser.rdf';
    public const PROPERTY_LOCK_DOWN_BROWSER = self::ONTOLOGY_NAMESPACE_URI . '#LockDownBrowser';
    public const PROVIDER_KIOSKED = 'kiosked';
    public const PROVIDER_MOCK = 'ldbMock';

    public function __construct(
        public readonly ?int $processCheckInterval,
        public readonly bool $processCheckOnTestNavigation,
        public readonly array $processBlackList,
        public readonly array $mockActiveProcesses = [],
        public readonly bool $simulateBreach = false
    ) {
    }

    public static function isProviderAllowed(string $provider): bool
    {
        return $provider === self::PROVIDER_KIOSKED || $provider === self::PROVIDER_MOCK;
    }

    public function jsonSerialize(): array
    {
        return [
            'processCheckInterval' => $this->processCheckInterval,
            'processCheckOnTestNavigation' => $this->processCheckOnTestNavigation,
            'processBlackList' => $this->processBlackList,
            'mockActiveProcesses' => $this->mockActiveProcesses,
            'simulateBreach' => $this->simulateBreach,
        ];
    }
}
