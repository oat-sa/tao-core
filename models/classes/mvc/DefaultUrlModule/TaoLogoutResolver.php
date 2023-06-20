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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\mvc\DefaultUrlModule;

use Zend\ServiceManager\ServiceLocatorAwareTrait;

class TaoLogoutResolver implements RedirectResolveInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param array $options
     * @return string
     */
    public function resolve(array $options): string
    {
        if (isset($_ENV['REDIRECT_AFTER_LOGOUT_URL'])) {
            return $_ENV['REDIRECT_AFTER_LOGOUT_URL'];
        }

        $redirectAdapterClass = $options['class'] ?? '';
        if ($redirectAdapterClass !== null) {
            return (new $redirectAdapterClass())->resolve($options['options'] ?? []);
        }

        return '';
    }
}
