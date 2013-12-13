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

$publicDataPath = GENERIS_FILES_PATH.'servicePublic'.DIRECTORY_SEPARATOR;
$privateDataPath = GENERIS_FILES_PATH.'servicePrivate'.DIRECTORY_SEPARATOR;

helpers_File::emptyDirectory($publicDataPath);
helpers_File::emptyDirectory($privateDataPath);

$publicFs = tao_models_classes_FileSourceService::singleton()->addLocalSource('public service storage', $publicDataPath);
$privateFs = tao_models_classes_FileSourceService::singleton()->addLocalSource('public service storage', $privateDataPath);

$provider = tao_models_classes_fsAccess_TokenAccessProvider::spawnProvider($publicFs);
/*
$provider = new tao_models_classes_fsAccess_TokenAccessProvider($publicFs);
$provider->prepareProvider();
*/
tao_models_classes_service_FileStorage::configure($privateFs, $publicFs, $provider);