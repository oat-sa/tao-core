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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <olexander.zagovorychev@1pt.com>
 */

namespace oat\tao\model\auth;


use oat\oatbox\service\ConfigurableService;

abstract class AbstractAuthService extends ConfigurableService
{
    const OPTION_TYPES = 'types';
    const OPTION_DEFAULT_TYPE = 'type';

    /**
     * Get all available type from config
     *
     * @return array of the auth types from the configuration file
     */
    public function getTypes()
    {
        return $this->hasOption(self::OPTION_TYPES) ? $this->getOption(self::OPTION_TYPES) : [];
    }

    /**
     * Get the authType form config
     *
     * @param \core_kernel_classes_Resource|null $resource
     * @return AbstractAuthType
     * @throws \common_Exception
     */
    public function getAuthType(\core_kernel_classes_Resource $resource = null)
    {
        if ($resource) {
            $authTypeUri = $resource->getUri();
            foreach ($this->getOption(self::OPTION_TYPES) as $type) {
                if ($type instanceof AbstractAuthType && $type->getAuthClass()->getUri() == $authTypeUri) {
                    $authType = $type;
                }
            }
        } else {
            $authType = $this->getOption(self::OPTION_DEFAULT_TYPE);
        }

        if (!isset($authType) || !is_a($authType, AbstractAuthType::class)) {
            throw new \common_Exception('Auth type not defined');
        }

        return $authType;
    }
}
