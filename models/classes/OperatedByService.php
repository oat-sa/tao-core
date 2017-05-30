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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
 
namespace oat\tao\model;

use oat\oatbox\service\ConfigurableService;

/**
 * TAO OperatedBy Service
 * 
 * This service aims at retrieving/persisting information about
 * the organization operating the TAO Platform.
 */
class OperatedByService extends ConfigurableService
{
    const SERVICE_ID = 'tao/operatedby';
    
    /**
     * TAO OperatedBy Service Constructor
     * 
     * Creates a new OperatedByService object.
     * 
     * @param array $options An associative array where keys are option names and values are option values.
     */
    public function __construct($options = array()) {
        parent::__construct($options);
    }
    
    /**
     * Get Organization Name
     * 
     * Get the name of the organization operating the TAO Platform.
     * 
     * @return string
     */
    public function getName()
    {
        $name = $this->getOption('operatedByName');
        return (empty($name) ? '' : $name);
    }
    
    /**
     * Set Organization Name
     * 
     * Set the name of the organization operating the TAO Platform.
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->setOption('operatedByName', $name);
    }
    
    /**
     * Get Organization Email
     * 
     * Get the email address of the organization operating the TAO
     * Platform.
     * 
     * @return string
     */
    public function getEmail()
    {
        $email = $this->getOption('operatedByEmail');
        return (empty($email) ? '' : $email);
    }
    
    /**
     * Set the Organization Email
     * 
     * Set the email address of the organization operating the TAO
     * Platform.
     * 
     * @param string $email
     */
    public function setEmail($email) {
        $this->setOption('operatedByEmail', $email);
    }
}
