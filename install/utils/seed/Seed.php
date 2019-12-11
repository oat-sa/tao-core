<?php
/*  
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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\tao\install\utils\seed;

use oat\generis\model\GenerisRdf;

class Seed
{
    private $extensionsToInstall;
    
    private $services;
    
    private $options;
    
    private $userData;
    
    private $postInstallScripts = [];
    
    public function __construct(SeedOptions $options, $extensions, $services, $user, $postInstallScripts = []) {
        $this->options = $options;
        $this->extensionsToInstall = $extensions;
        $this->services = $services;
        $this->userData = $user;
        $this->postInstallScripts = $postInstallScripts;
    }
    
    public function getRootUrl() {
        return $this->options->getRootUrl();
    }
    
    public function getLocalNamespace() {
        return $this->options->getLocalNamespace();
    }
    
    public function getDefaultLanguage() {
        return $this->options->getDefaultLanguage();
    }
    
    public function getDefaultTimezone() {
        return $this->options->getDefaultTimezone();
    }
    
    public function getInstanceName() {
        return $this->options->getInstanceName();
    }
    
    public function useDebugMode() {
        return $this->options->useDebugMode();
    }
    
    public function installSamples() {
        return $this->options->installSamples();
    }
    
    public function getExtensionsToInstall() {
        return $this->extensionsToInstall;
    }
    
    public function getUserProperties() {
        return [
            GenerisRdf::PROPERTY_USER_FIRSTNAME,
            GenerisRdf::PROPERTY_USER_LASTNAME,
            GenerisRdf::PROPERTY_USER_LOGIN,
            GenerisRdf::PROPERTY_USER_PASSWORD
        ];
    }

    public function getServices() {
        return $this->services;
    }

    public function getPostInstallScripts() {
        return $this->postInstallScripts;
    }
}