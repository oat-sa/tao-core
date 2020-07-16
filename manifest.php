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

use oat\tao\controller\api\Users;
use oat\tao\install\services\SetupSettingsStorage;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\routing\ApiRoute;
use oat\tao\model\routing\LegacyRoute;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\install\AddArchiveService;
use oat\tao\scripts\install\AddLogFs;
use oat\tao\scripts\install\AddTmpFsHandlers;
use oat\tao\scripts\install\CreateWebhookEventLogTable;
use oat\tao\scripts\install\InstallNotificationTable;
use oat\tao\scripts\install\RegisterActionService;
use oat\tao\scripts\install\RegisterResourceEvents;
use oat\tao\scripts\install\RegisterEvents;
use oat\tao\scripts\install\RegisterResourceWatcherService;
use oat\tao\scripts\install\RegisterSignatureGenerator;
use oat\tao\scripts\install\RegisterTaskQueueServices;
use oat\tao\scripts\install\RegisterUserLockoutsEventListeners;
use oat\tao\scripts\install\RegisterUserService;
use oat\tao\scripts\install\RegisterValidationRules;
use oat\tao\scripts\install\RegisterValueCollectionServices;
use oat\tao\scripts\install\SetClientLoggerConfig;
use oat\tao\scripts\install\SetContainerService;
use oat\tao\scripts\install\SetDefaultCSPHeader;
use oat\tao\scripts\install\SetLocaleNumbersConfig;
use oat\tao\scripts\install\SetServiceFileStorage;
use oat\tao\scripts\install\SetServiceState;
use oat\tao\scripts\install\SetupMaintenanceService;
use oat\tao\scripts\install\SetUpQueueTasks;

$extpath = __DIR__ . DIRECTORY_SEPARATOR;

return [
    'name' => 'tao',
    'label' => 'TAO Base',
    'description' => 'TAO meta-extension',
    'license' => 'GPL-2.0',
    'version' => '44.13.0',
    'author' => 'Open Assessment Technologies, CRP Henri Tudor',
    'requires' => [
        'generis' => '>=12.31.0',
    ],
    'models' => [
        'http://www.tao.lu/Ontologies/TAO.rdf',
        'http://www.tao.lu/middleware/wfEngine.rdf'
    ],
    'install' => [
        'rdf' => [
            __DIR__ . '/models/ontology/tao.rdf',
            __DIR__ . '/models/ontology/taoaclrole.rdf',
            __DIR__ . '/models/ontology/oauth.rdf',
            __DIR__ . '/models/ontology/webservice.rdf',
            __DIR__ . '/models/ontology/services.rdf',
            __DIR__ . '/models/ontology/indexation.rdf',
            __DIR__ . '/models/ontology/model.rdf',
            __DIR__ . '/models/ontology/widegetdefinitions.rdf',
            __DIR__ . '/models/ontology/requiredaction.rdf',
            __DIR__ . '/models/ontology/auth/basicauth.rdf',
            __DIR__ . '/models/ontology/userlocks.rdf'
        ],
        'checks' => [
                ['type' => 'CheckPHPRuntime', 'value' => ['id' => 'tao_php_runtime', 'min' => '5.4']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_pdo', 'name' => 'PDO']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_curl', 'name' => 'curl']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_zip', 'name' => 'zip']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_json', 'name' => 'json']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_spl', 'name' => 'spl']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_dom', 'name' => 'dom']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_mbstring', 'name' => 'mbstring']],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_suhosin', 'name' => 'suhosin', 'silent' => true]],
                ['type' => 'CheckPHPExtension', 'value' => ['id' => 'tao_extension_php_finfo', 'name' => 'fileinfo']],
                ['type' => 'CheckCustom',      'value' => ['id' => 'tao_extension_opcache', 'name' => 'opcache', 'optional' => true, 'extension' => 'tao']],
                ['type' => 'CheckPHPINIValue', 'value' => ['id' => 'tao_ini_opcache_save_comments', 'name' => 'opcache.save_comments', 'value' => '1', 'dependsOn' => ['tao_extension_opcache']]],
                ['type' => 'CheckCustom',      'value' => ['id' => 'tao_ini_opcache_load_comments', 'name' => 'opcache_load_comments', 'extension' => 'tao', 'dependsOn' => ['tao_extension_opcache']]],
                ['type' => 'CheckPHPINIValue', 'value' => ['id' => 'tao_ini_suhosin_post_max_name_length', 'name' => 'suhosin.post.max_name_length', 'value' => '128', 'dependsOn' => ['tao_extension_suhosin']]],
                ['type' => 'CheckPHPINIValue', 'value' => ['id' => 'tao_ini_suhosin_request_max_varname_length', 'name' => 'suhosin.request.max_varname_length', 'value' => '128', 'dependsOn' => ['tao_extension_suhosin']]],
                ['type' => 'CheckFileSystemComponent', 'value' => ['id' => 'fs_generis_common_conf', 'location' => 'config', 'rights' => 'rw', 'recursive' => true]],
                ['type' => 'CheckFileSystemComponent', 'value' => ['id' => 'fs_tao_client_locales', 'location' => 'tao/views/locales', 'rights' => 'rw']],
                ['type' => 'CheckCustom', 'value' => ['id' => 'tao_custom_not_nginx', 'name' => 'not_nginx', 'extension' => 'tao', "optional" => true, 'dependsOn' => ['tao_extension_curl']]],
                ['type' => 'CheckCustom', 'value' => ['id' => 'tao_custom_allowoverride', 'name' => 'allow_override', 'extension' => 'tao', "optional" => true, 'dependsOn' => ['tao_custom_not_nginx']]],
                ['type' => 'CheckCustom', 'value' => ['id' => 'tao_custom_mod_rewrite', 'name' => 'mod_rewrite', 'extension' => 'tao', 'dependsOn' => ['tao_custom_allowoverride']]],
                ['type' => 'CheckCustom', 'value' => ['id' => 'tao_custom_database_drivers', 'name' => 'database_drivers', 'extension' => 'tao']],
        ],
        'php' => [
            __DIR__ . '/scripts/install/addFileUploadSource.php',
            __DIR__ . '/scripts/install/setSimpleAccess.php',
            SetServiceFileStorage::class,
            SetServiceState::class,
            __DIR__ . '/scripts/install/setJsConfig.php',
            __DIR__ . '/scripts/install/registerEntryPoint.php',
            SetLocaleNumbersConfig::class,
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
            RegisterEvents::class,
            RegisterActionService::class,
            RegisterUserLockoutsEventListeners::class,
            RegisterTaskQueueServices::class,
            SetUpQueueTasks::class,
            RegisterSignatureGenerator::class,
            SetDefaultCSPHeader::class,
            CreateWebhookEventLogTable::class,
            SetupSettingsStorage::class,
            RegisterUserService::class,
            RegisterValueCollectionServices::class,
        ]
    ],
    'update' => 'oat\\tao\\scripts\\update\\Updater',
    'optimizableClasses' => [
        'http://www.tao.lu/Ontologies/TAO.rdf#Languages',
        'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages'
    ],
    'managementRole' => TaoRoles::TAO_MANAGER,
    'acl' => [
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'Main', 'act' => 'entry']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'Main', 'act' => 'login']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'Main', 'act' => 'logout']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'PasswordRecovery', 'act' => 'index']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'PasswordRecovery', 'act' => 'resetPassword']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'ClientConfig']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'Health']],
        [AccessRule::GRANT, TaoRoles::ANONYMOUS,            ['ext' => 'tao','mod' => 'RestVersion', 'act' => 'index']],
        [AccessRule::GRANT, TaoRoles::BASE_USER,            ['ext' => 'tao','mod' => 'ServiceModule']],
        [AccessRule::GRANT, TaoRoles::BASE_USER,            ['ext' => 'tao','mod' => 'Notification']],
        [AccessRule::GRANT, TaoRoles::BASE_USER,            ['ext' => 'tao','mod' => 'File', 'act' => 'accessFile']],
        [AccessRule::GRANT, TaoRoles::BASE_USER,            ['ext' => 'tao','mod' => 'Log', 'act' => 'log']],
        [AccessRule::GRANT, TaoRoles::BASE_USER,            ['ext' => 'tao','mod' => 'TaskQueueWebApi']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Languages', 'act' => 'index']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'File', 'act' => 'upload']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Main', 'act' => 'index']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Main', 'act' => 'getSectionActions']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Main', 'act' => 'getSectionTrees']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Users', 'act' => 'checkLogin']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'UserSettings']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'GenerisTree']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Search']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'Main', 'act' => 'index']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['act' => 'tao_actions_Lock@locked']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['act' => 'tao_actions_Lock@release']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'TaskQueueData']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'RestResource']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'RestClass']],
        [AccessRule::GRANT, TaoRoles::BACK_OFFICE,          ['ext' => 'tao','mod' => 'PropertyValues']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Breadcrumbs']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Export']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'File']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Import']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Lock']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Main']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'PasswordRecovery']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Permission']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'PropertiesAuthoring']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'QueueAction']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'RestUser']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Roles']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'TaskQueue']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Users']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'WebService']],
        [AccessRule::GRANT, TaoRoles::TAO_MANAGER,          ['ext' => 'tao','mod' => 'Security']],
        [AccessRule::GRANT, TaoRoles::REST_PUBLISHER,       ['ext' => 'tao','mod' => 'TaskQueue', 'act' => 'get']],
        [AccessRule::GRANT, TaoRoles::REST_PUBLISHER,       ['ext' => 'tao','mod' => 'TaskQueue', 'act' => 'getStatus']],
        [AccessRule::GRANT, TaoRoles::SYSTEM_ADMINISTRATOR, ['ext' => 'tao','mod' => 'ExtensionsManager']],
        [AccessRule::GRANT, TaoRoles::LOCK_MANAGER,     'tao_actions_Lock@forceRelease'],
        [AccessRule::GRANT, TaoRoles::PROPERTY_MANAGER, 'tao_actions_PropertiesAuthoring'],
        [AccessRule::GRANT, TaoRoles::SYSTEM_ADMINISTRATOR, Users::class],
        [AccessRule::GRANT, TaoRoles::GLOBAL_MANAGER, Users::class],
    ],
    'routes' => [
        '/tao/api'  => ['class' => ApiRoute::class],
        '/tao'      => ['class' => LegacyRoute::class],
    ],
    'constants' => [
        #TAO version number
        'TAO_VERSION' => '3.4.0-sprint132',
        #TAO version label
        'TAO_VERSION_NAME' => '3.4.0-sprint132',
        #the name to display
        'PRODUCT_NAME' => 'TAO',
        #TAO release status, use to add specific footer to TAO, available alpha, beta, demo, stable
        'TAO_RELEASE_STATUS' => 'stable',
        #TAO default character encoding (mainly used with multi-byte string functions).
        'TAO_DEFAULT_ENCODING' => 'UTF-8',
        # actions directory
        'DIR_ACTIONS' => $extpath . 'actions' . DIRECTORY_SEPARATOR,

        # views directory
        'DIR_VIEWS' => $extpath . 'views' . DIRECTORY_SEPARATOR,

        # default module name
        'DEFAULT_MODULE_NAME' => 'Main',

        #default action name
        'DEFAULT_ACTION_NAME' => 'index',

        #BASE PATH: the root path in the file system (usually the document root)
        'BASE_PATH' => $extpath,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL . 'tao/',

        #TPL PATH the path to the templates
        'TPL_PATH' => $extpath . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
    ],
    'extra' => [
        'structures' => $extpath . 'actions' . DIRECTORY_SEPARATOR . 'structures.xml',
    ]
];
