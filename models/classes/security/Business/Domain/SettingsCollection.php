<?php declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\security\Business\Domain;

use ArrayIterator;
use IteratorAggregate;
use oat\tao\model\security\DataAccess\Repository\SecuritySettingsRepository;
use Traversable;

final class SettingsCollection implements IteratorAggregate
{
    /** @var Setting[] */
    private $settings;

    public function __construct(Setting ...$settings)
    {
        $this->settings = array_combine(
            array_map([$this, 'extractKey'], $settings),
            $settings
        );
    }

    /**
     * @return Traversable|Setting[]
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->settings);
    }

    public function findContentSecurityPolicy(): Setting
    {
        return $this->settings[SecuritySettingsRepository::CONTENT_SECURITY_POLICY];
    }

    public function findContentSecurityPolicyWhitelist(): Setting
    {
        return $this->settings[SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST];
    }

    private function extractKey(Setting $setting): string
    {
        return $setting->getKey();
    }
}
