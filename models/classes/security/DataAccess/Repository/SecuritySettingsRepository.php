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

namespace oat\tao\model\security\DataAccess\Repository;

use oat\tao\model\security\Business\Contract\SecuritySettingsRepositoryInterface;
use oat\tao\model\security\Business\Domain\Setting;
use oat\tao\model\security\Business\Domain\SettingsCollection;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\settings\SettingsStorageInterface;

class SecuritySettingsRepository extends InjectionAwareService implements SecuritySettingsRepositoryInterface
{
    public const CONTENT_SECURITY_POLICY           = 'cspHeader';
    public const CONTENT_SECURITY_POLICY_WHITELIST = 'cspHeaderList';
    public const STRICT_TRANSPORT_SECURITY         = 'tlsHeader';

    private const SETTING_KEYS = [
        self::CONTENT_SECURITY_POLICY,
        self::CONTENT_SECURITY_POLICY_WHITELIST,
        self::STRICT_TRANSPORT_SECURITY,
    ];

    private const MULTILINE_VALUE_KEYS = [
        self::CONTENT_SECURITY_POLICY_WHITELIST,
    ];

    /** @var SettingsStorageInterface */
    private $storage;

    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(SettingsStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function findAll(): SettingsCollection
    {
        return new SettingsCollection(
            ...array_map(
                   [$this, 'createSetting'],
                   self::SETTING_KEYS
               )
        );
    }

    /**
     * @inheritDoc
     */
    public function persist(SettingsCollection $settings): void
    {
        foreach ($settings as $setting) {
            $this->storage->set(
                $setting->getKey(),
                $this->extractPersistenceValue($setting)
            );
        }
    }

    private function createSetting(string $key): Setting
    {
        $rawValue = $this->storage->get($key) ?: '';

        if ($rawValue && $this->isMultilineValueKey($key)) {
            $rawValue = implode("\n", json_decode($rawValue, true) ?? []);
        }

        return new Setting($key, $rawValue);
    }

    private function isMultilineValueKey(string $key): bool
    {
        return in_array($key, self::MULTILINE_VALUE_KEYS, true);
    }

    private function extractPersistenceValue(Setting $setting): string
    {
        if (!$this->isMultilineValueKey($setting->getKey())) {
            return $setting->getValue();
        }

        return json_encode(
            array_filter(
                array_map('trim', explode("\n", $setting->getValue()))
            )
        );
    }
}
