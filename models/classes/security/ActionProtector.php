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
 * Copyright (c) 2015 – 2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\security;

use oat\tao\model\security\Business\Contract\SecuritySettingsRepositoryInterface;
use oat\tao\model\service\InjectionAwareService;

/**
 * Service that can be used to protect actions.
 *
 * @author Martijn Swinkels <martijn@taotesting.com>
 */
class ActionProtector extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/actionProtection';

    /** @var SecuritySettingsRepositoryInterface */
    private $repository;

    /** @var string */
    private $defaultHeaders;

    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(SecuritySettingsRepositoryInterface $repository, array $defaultHeaders = [])
    {
        $this->repository     = $repository;
        $this->defaultHeaders = $defaultHeaders;
    }

    public function setHeaders(): void
    {
        $this->setDefaultHeaders();
        $this->setFrameAncestorsHeader();
    }

    public function setDefaultHeaders(): void
    {
        foreach ($this->defaultHeaders as $defaultHeader) {
            header($defaultHeader);
        }
    }

    /**
     * Set the header that defines which sources are allowed to embed the pages.
     */
    public function setFrameAncestorsHeader(): void
    {
        $settings = $this->repository->findAll();

        $whitelistedSources = $settings->findContentSecurityPolicy()->getValue();

        if (!$whitelistedSources) {
            $whitelistedSources = ["'none'"];
        }

        // Wrap directives in quotes
        if (in_array($whitelistedSources, ['self', 'none'], true)) {
            $whitelistedSources = ["'" . $whitelistedSources . "'"];
        }

        if ($whitelistedSources === 'list') {
            $whitelistedSources = explode("\n", $settings->findContentSecurityPolicyWhitelist()->getValue());
        }

        header(
            sprintf(
                'Content-Security-Policy: frame-ancestors %s',
                implode(' ', (array)$whitelistedSources)
            )
        );
    }

    protected function getDependencies(): array
    {
        return [
            $this->repository,
            $this->defaultHeaders,
        ];
    }
}
