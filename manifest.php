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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-     (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\routing\ApiRoute;
use oat\tao\model\routing\LegacyRoute;
use oat\tao\scripts\install\AddLogFs;
use oat\tao\scripts\install\AddTmpFsHandlers;
use oat\tao\scripts\install\RegisterTaskQueueServices;
use oat\tao\scripts\install\RegisterUserLockoutsEventListeners;
use oat\tao\scripts\install\SetClientLoggerConfig;
use oat\tao\scripts\install\SetContainerService;
use oat\tao\scripts\install\SetServiceFileStorage;
use oat\tao\scripts\install\RegisterValidationRules;
use oat\tao\scripts\install\InstallNotificationTable;
use oat\tao\scripts\install\SetServiceState;
use oat\tao\scripts\install\SetupMaintenanceService;
use oat\tao\scripts\install\AddArchiveService;
use oat\tao\scripts\install\RegisterResourceWatcherService;
use oat\tao\scripts\install\RegisterResourceEvents;
use oat\tao\scripts\install\RegisterActionService;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\install\SetUpQueueTasks;

$extpath = dirname(__FILE__) . DIRECTORY_SEPARATOR;

return array(
    'name' => 'tao',
    'label' => 'TAO Base',
    'description' => 'TAO meta-extension',
    'license' => 'GPL-2.0',
    'version' => '27.1.4',
    'author' => 'Open Assessment Technologies, CRP Henri Tudor',
    'requires' => array(
        'generis' => '>=8.2.2',
    ),
    'models' => array(
        'http://www.tao.lu/Ontologies/TAO.rdf',
        'http://www.tao.lu/middleware/wfEngine.rdf'
    ),
    'install' => array(
        'rdf' => array(
            dirname(__FILE__). '/models/ontology/tao.rdf',
            dirname(__FILE__). '/models/ontology/taoaclrole.rdf',
            dirname(__FILE__). '/models/ontology/oauth.rdf',
            dirname(__FILE__). '/models/ontology/webservice.rdf',
            dirname(__FILE__). '/models/ontology/services.rdf',
            dirname(__FILE__). '/models/ontology/indexation.rdf',
            dirname(__FILE__). '/models/ontology/model.rdf',
            dirname(__FILE__). '/models/ontology/widegetdefinitions.rdf',
            dirname(__FILE__). '/models/ontology/requiredaction.rdf',
            dirname(__FILE__). '/models/ontology/auth/basicauth.rdf',
            dirname(__FILE__). '/models/ontology/userlocks.rdf'
        ),
        'checks' => array(
                array('type' => 'CheckPHPRuntime', 'value' => array('id' => 'tao_php_runtime', 'min' => '5.4')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_pdo', 'name' => 'PDO')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_curl', 'name' => 'curl')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_zip', 'name' => 'zip')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_json', 'name' => 'json')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_spl', 'name' => 'spl')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_dom', 'name' => 'dom')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_mbstring', 'name' => 'mbstring')),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_suhosin', 'name' => 'suhosin', 'silent' => true)),
                array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_php_finfo', 'name' => 'fileinfo')),
                array('type' => 'CheckCustom',      'value' => array('id' => 'tao_extension_opcache', 'name' => 'opcache', 'optional' => true, 'extension' => 'tao')),
                array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_opcache_save_comments', 'name' => 'opcache.save_comments', 'value' => '1', 'dependsOn' => array('tao_extension_opcache'))),
                array('type' => 'CheckCustom',      'value' => array('id' => 'tao_ini_opcache_load_comments', 'name' => 'opcache_load_comments', 'extension' => 'tao', 'dependsOn' => array('tao_extension_opcache'))),
                array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_suhosin_post_max_name_length', 'name' => 'suhosin.post.max_name_length', 'value' => '128', 'dependsOn' => array('tao_extension_suhosin'))),
                array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_suhosin_request_max_varname_length', 'name' => 'suhosin.request.max_varname_length', 'value' => '128', 'dependsOn' => array('tao_extension_suhosin'))),
                array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_common_conf', 'location' => 'config', 'rights' => 'rw', 'recursive' => true)),
                array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_tao_client_locales', 'location' => 'tao/views/locales', 'rights' => 'rw')),
                array('type' => 'CheckCustom', 'value' => array('id' => 'tao_custom_not_nginx', 'name' => 'not_nginx', 'extension' => 'tao', "optional" => true, 'dependsOn' => array('tao_extension_curl'))),
                array('type' => 'CheckCustom', 'value' => array('id' => 'tao_custom_allowoverride', 'name' => 'allow_override', 'extension' => 'tao', "optional" => true, 'dependsOn' => array('tao_custom_not_nginx'))),
                array('type' => 'CheckCustom', 'value' => array('id' => 'tao_custom_mod_rewrite', 'name' => 'mod_rewrite', 'extension' => 'tao', 'dependsOn' => array('tao_custom_allowoverride'))),
                array('type' => 'CheckCustom', 'value' => array('id' => 'tao_custom_database_drivers', 'name' => 'database_drivers', 'extension' => 'tao')),
        ),
        'php' => array(
            dirname(__FILE__).'/scripts/install/addFileUploadSource.php',
            dirname(__FILE__).'/scripts/install/setSimpleAccess.php',
            SetServiceFileStorage::class,
            SetServiceState::class,
            dirname(__FILE__).'/scripts/install/setJsConfig.php',
            dirname(__FILE__).'/scripts/install/registerEntryPoint.php',
            dirname(__FILE__).'/scripts/install/setLocaleNumbersConfig.php',
            AddLogFs::class,
            AddTmpFsHandlers::class,
            RegisterValidationRules::class,
            SetClientLoggerConfig::class,
            InstallNotificationTable::class,
            SetupMaintenanceService::class,
            AddArchiveService::class,
            SetContainerService::class,
            RegisterResourceWatcherService::class,
            RegisterResourceEvents::class,
            RegisterActionService::class,
            RegisterUserLockoutsEventListeners::class,
            RegisterTaskQueueServices::class,
            SetUpQueueTasks::class
        )
    ),
    'update' => 'oat\\tao\\scripts\\update\\Updater',
    'optimizableClasses' => array(
        'http://www.tao.lu/Ontologies/TAO.rdf#Languages',
        'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages'
    ),
    'managementRole' => TaoRoles::TAO_MANAGER,
    'acl' => array(
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'Main', 'act' => 'entry')),
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'Main', 'act' => 'login')),
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'Main', 'act' => 'logout')),
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'PasswordRecovery', 'act' => 'index')),
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'PasswordRecovery', 'act' => 'resetPassword')),
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'ClientConfig')),
        array('grant', TaoRoles::ANONYMOUS,            array('ext'=>'tao','mod' => 'Health')),
        array('grant', TaoRoles::BASE_USER,            array('ext'=>'tao','mod' => 'ServiceModule')),
        array('grant', TaoRoles::BASE_USER,            array('ext'=>'tao','mod' => 'Notification')),
        array('grant', TaoRoles::BASE_USER,            array('ext'=>'tao','mod' => 'File', 'act' => 'accessFile')),
        array('grant', TaoRoles::BASE_USER,            array('ext'=>'tao','mod' => 'Log', 'act' => 'log')),
        array('grant', TaoRoles::BASE_USER,            array('ext'=>'tao','mod' => 'TaskQueueWebApi')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'File', 'act' => 'upload')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'Main', 'act' => 'index')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'Main', 'act' => 'getSectionActions')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'Main', 'act' => 'getSectionTrees')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'Users', 'act' => 'checkLogin')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'UserSettings')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'GenerisTree')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'Search')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'Main', 'act' => 'index')),
        array('grant', TaoRoles::BACK_OFFICE,          array('act' => 'tao_actions_Lock@locked')),
        array('grant', TaoRoles::BACK_OFFICE,          array('act' => 'tao_actions_Lock@release')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'TaskQueueData')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'RestResource')),
        array('grant', TaoRoles::BACK_OFFICE,          array('ext'=>'tao','mod' => 'RestClass')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Breadcrumbs')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Export')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'File')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Import')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Lock')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Main')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'PasswordRecovery')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Permission')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'PropertiesAuthoring')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'QueueAction')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'RestUser')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Roles')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'TaskQueue')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'Users')),
        array('grant', TaoRoles::TAO_MANAGER,          array('ext'=>'tao','mod' => 'WebService')),
        array('grant', TaoRoles::REST_PUBLISHER,       array('ext'=>'tao', 'mod' => 'TaskQueue', 'act' => 'get')),
        array('grant', TaoRoles::SYSTEM_ADMINISTRATOR, array('ext'=>'tao','mod' => 'ExtensionsManager')),
        array('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#LockManagerRole',     'tao_actions_Lock@forceRelease'),
        array('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyManagerRole', 'tao_actions_PropertiesAuthoring'),
        array(AccessRule::GRANT, TaoRoles::SYSTEM_ADMINISTRATOR, oat\tao\controller\api\Users::class),
        array(AccessRule::GRANT, TaoRoles::GLOBAL_MANAGER, oat\tao\controller\api\Users::class),
    ),
    'routes' => [
        '/tao/api'  => ['class' => ApiRoute::class],
        '/tao'      => ['class' => LegacyRoute::class],
    ],
    'constants' => array(
        #TAO version number
        'TAO_VERSION' => '3.3.0-RC02',
        #TAO version label
        'TAO_VERSION_NAME' => '3.3.0-RC02',
        #the name to display
        'PRODUCT_NAME' => 'TAO',
        #TAO release status, use to add specific footer to TAO, available alpha, beta, demo, stable
        'TAO_RELEASE_STATUS' => 'stable',
        #TAO default character encoding (mainly used with multi-byte string functions).
        'TAO_DEFAULT_ENCODING' => 'UTF-8',
        # actions directory
        "DIR_ACTIONS" => $extpath."actions".DIRECTORY_SEPARATOR,

        # views directory
        "DIR_VIEWS" => $extpath."views".DIRECTORY_SEPARATOR,

        # default module name
        'DEFAULT_MODULE_NAME' => 'Main',

        #default action name
        'DEFAULT_ACTION_NAME' => 'index',

        #BASE PATH: the root path in the file system (usually the document root)
        'BASE_PATH' => $extpath,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL.'tao/',

         #TPL PATH the path to the templates
         'TPL_PATH' => $extpath."views".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR,
    ),
    'extra' => array(
        'structures' => $extpath.'actions'.DIRECTORY_SEPARATOR.'structures.xml',
    )
);
