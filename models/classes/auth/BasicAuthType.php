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


use oat\tao\helpers\Template;

class BasicAuthType extends AbstractAuthType implements BasicAuth
{
    public function getAuthClass()
    {
        return $this->getClass(self::CLASS_BASIC_AUTH);
    }

    public function getAuthProperties()
    {
        return [
            $this->getProperty(self::PROPERTY_LOGIN),
            $this->getProperty(self::PROPERTY_PASSWORD),
        ];
    }

    /**
     * @return string
     * @throws \core_kernel_persistence_Exception
     */
    public function getTemplate()
    {
        $instance = $this->getInstance();
        if ($instance && $instance->exists()) {
            $data = [
                self::PROPERTY_LOGIN => (string)$instance->getOnePropertyValue($this->getProperty(self::PROPERTY_LOGIN)),
                self::PROPERTY_PASSWORD => (string)$instance->getOnePropertyValue($this->getProperty(self::PROPERTY_PASSWORD)),
            ];
        } else {
            $data = [
                self::PROPERTY_LOGIN => '',
                self::PROPERTY_PASSWORD => '',
            ];
        }

        return Template::inc('auth/basicAuthForm.tpl', 'tao', $data);
    }

    /**
     * @return array
     * @throws \core_kernel_persistence_Exception
     */
    public function getCredentials()
    {
        return [
            (string)$this->getInstance()->getOnePropertyValue($this->getProperty(self::PROPERTY_LOGIN)),
            (string)$this->getInstance()->getOnePropertyValue($this->getProperty(self::PROPERTY_PASSWORD)),
        ];
    }
}
