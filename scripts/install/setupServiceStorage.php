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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/*
 * This post-installation script creates a new local file source for file uploaded
 * by end-users through the TAO GUI.
 */

$persistences = core_persistence_Manager::singleton()->getPersistences();
if (isset($persistences['serviceState'])) {
    // use key value implementation
    $kvImpl = new tao_models_classes_service_state_KeyValueStorePersistence('serviceState');
    tao_models_classes_service_state_Service::setImplementation($kvImpl);
} else {
    // use fs implementation
    $extension = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    $dataPath = $extension ->getConstant('BASE_PATH'). 'data' .DIRECTORY_SEPARATOR. 'serviceStorage' .DIRECTORY_SEPARATOR;
    
    $source = tao_models_classes_FileSourceService::singleton()->addLocalSource('ServiceState Directory', $dataPath);
    
    $fsImpl = new tao_models_classes_service_state_FileSystemPersistence($source);
    tao_models_classes_service_state_Service::setImplementation($fsImpl);
}
