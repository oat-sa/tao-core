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
    private const DEPENDENT_SETTINGS = [
        SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => [
            SecuritySettingsRepository::CONTENT_SECURITY_POLICY => 'list',
        ],
    ];

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
        return new ArrayIterator(
            array_filter($this->settings, [$this, 'hasMetDependencies'])
        );
    }

    public function findContentSecurityPolicy(): Setting
    {
        return $this->extractSetting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY);
    }

    public function findContentSecurityPolicyWhitelist(): Setting
    {
        return $this->extractSetting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST);
    }

    public function findTransportSecurity(): Setting
    {
        return $this->settings[SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY];
    }

    private function extractKey(Setting $setting): string
    {
        return $setting->getKey();
    }

    private function extractSetting(string $key): Setting
    {
        return $this->settings[$key] ?? new Setting($key, '');
    }

    private function hasMetDependencies(Setting $setting): bool
    {
        foreach (self::DEPENDENT_SETTINGS[$setting->getKey()] ?? [] as $dependencyKey => $dependencyValue) {
            if ($this->extractSetting($dependencyKey)->getValue() !== $dependencyValue) {
                return false;
            }
        }

        return true;
    }
}
