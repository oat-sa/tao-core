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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\user\import;

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\user\UserRdf;
use oat\tao\model\import\service\OntologyMapper;
use tao_models_classes_LanguageService;

class OntologyUserMapper extends OntologyMapper implements UserMapperInterface
{
    use OntologyAwareTrait;

    /** @var string */
    protected $plainPassword;

    /**
     * @param string $key
     * @param string $value
     * @return string
     * @throws \oat\generis\model\user\PasswordConstraintsException
     */
    protected function formatValue($key, $value)
    {
        switch ($key) {
            case UserRdf::PROPERTY_PASSWORD:
                $this->plainPassword = $value;
                return $this->getPasswordHashService()->encrypt($value);
            case UserRdf::PROPERTY_UILG:
            case UserRdf::PROPERTY_DEFLG:
                $val = $this->getLanguageService()->getLanguageByCode($value);
                return $val === null ? $value : $val->getUri();
            default:
                return $value;
        }
    }

    /**
     * @return \helpers_PasswordHash
     */
    protected function getPasswordHashService()
    {
        return \core_kernel_users_Service::getPasswordHash();
    }

    /**
     * @return tao_models_classes_LanguageService
     */
    protected function getLanguageService()
    {
        return tao_models_classes_LanguageService::singleton();
    }

    /**
     * Get the plain password
     *
     * @return string|null
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }
}