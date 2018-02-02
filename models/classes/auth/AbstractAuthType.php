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


use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\PhpSerializable;

abstract class AbstractAuthType implements PhpSerializable
{
    use OntologyAwareTrait;

    /**
     * the resource which has authorizations
     * @var \core_kernel_classes_Resource | null
     */
    private $instance = null;

    /**
     * RDF class of the AuthType
     * @return \core_kernel_classes_Class
     */
    abstract public function getAuthClass();

    /**
     * All fields to configure current authenticator
     * @return array
     */
    abstract public function getAuthProperties();

    /**
     * Returns template for the current instance (or empty template for the default authorization)
     * with credentials
     * @return string
     */
    abstract public function getTemplate();

    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\PhpSerializable::__toPhpCode()
     */
    public function __toPhpCode() {
        return 'new '.get_class($this).'()';
    }

    public function setInstance(\core_kernel_classes_Resource $instance = null) {
        $this->instance = $instance;
    }

    public function getInstance() {
        return $this->instance;
    }

    /**
     * @return array
     */
    abstract public function getCredentials();
}
