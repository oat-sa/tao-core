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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\scripts\update;

use common_Exception;
use common_report_Report as Report;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\data\ModelManager;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\file\FileIterator;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\user\UserRdf;
use oat\oatbox\event\EventManager;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceNotFoundException;
use oat\oatbox\task\TaskService;
use oat\oatbox\user\UserService;
use oat\tao\controller\api\Users;
use oat\tao\helpers\dateFormatter\EuropeanFormatter;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\actionQueue\implementation\InstantActionQueue;
use oat\tao\model\cliArgument\argument\implementation\Group;
use oat\tao\model\cliArgument\argument\implementation\verbose\Debug;
use oat\tao\model\cliArgument\argument\implementation\verbose\Error;
use oat\tao\model\cliArgument\argument\implementation\verbose\Info;
use oat\tao\model\cliArgument\argument\implementation\verbose\Notice;
use oat\tao\model\cliArgument\ArgumentService;
use oat\tao\model\clientConfig\ClientConfigService;
use oat\tao\model\clientConfig\sources\ThemeConfig;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\event\FileUploadedEvent;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\event\RoleChangedEvent;
use oat\tao\model\event\RoleCreatedEvent;
use oat\tao\model\event\RoleRemovedEvent;
use oat\tao\model\event\UploadLocalCopyCreatedEvent;
use oat\tao\model\event\UserCreatedEvent;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;
use oat\tao\model\extension\UpdateLogger;
use oat\tao\model\i18n\ExtraPoService;
use oat\tao\model\maintenance\Maintenance;
use oat\tao\model\media\MediaService;
use oat\tao\model\metadata\compiler\ResourceJsonMetadataCompiler;
use oat\tao\model\metrics\MetricsService;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\tao\model\mvc\error\ExceptionInterpretor;
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\implementation\RdsNotification;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\oauth\DataStore;
use oat\tao\model\oauth\lockout\NoLockout;
use oat\tao\model\oauth\nonce\NoNonce;
use oat\tao\model\oauth\OauthService;
use oat\tao\model\OperatedByService;
use oat\tao\model\resources\ListResourceLookup;
use oat\tao\model\resources\ResourceService;
use oat\tao\model\resources\ResourceWatcher;
use oat\tao\model\resources\SecureResourceService;
use oat\tao\model\resources\SecureResourceServiceInterface;
use oat\tao\model\resources\TreeResourceLookup;
use oat\tao\model\routing\AnnotationReaderService;
use oat\tao\model\routing\ControllerService;
use oat\tao\model\routing\RouteAnnotationService;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\security\ActionProtector;
use oat\tao\model\security\Business\Contract\SecuritySettingsRepositoryInterface;
use oat\tao\model\security\DataAccess\Repository\SecuritySettingsRepository;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\security\xsrf\TokenStore;
use oat\tao\model\security\xsrf\TokenStoreSession;
use oat\tao\model\service\ApplicationService;
use oat\tao\model\service\ContainerService;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\session\restSessionFactory\builder\HttpBasicAuthBuilder;
use oat\tao\model\session\restSessionFactory\RestSessionFactory;
use oat\tao\model\settings\CspHeaderSettingsInterface;
use oat\tao\model\settings\SettingsStorageInterface;
use oat\tao\model\task\ExportByHandler;
use oat\tao\model\task\ImportByHandler;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\Queue\Broker\InMemoryQueueBroker;
use oat\tao\model\taskQueue\Queue\TaskSelector\WeightStrategy;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\TaskSerializerService;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\Broker\RdsTaskLogBroker;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\Tree\GetTreeService;
use oat\tao\model\upload\UploadService;
use oat\tao\model\user\GenerisUserService;
use oat\tao\model\user\implementation\NoUserLocksService;
use oat\tao\model\user\import\OntologyUserMapper;
use oat\tao\model\user\import\UserCsvImporterFactory;
use oat\tao\model\user\TaoRoles;
use oat\tao\model\user\UserLocks;
use oat\tao\model\webhooks\log\WebhookEventLogInterface;
use oat\tao\model\webhooks\log\WebhookLogRepository;
use oat\tao\model\webhooks\log\WebhookLogRepositoryInterface;
use oat\tao\model\webhooks\log\WebhookRdsEventLogService;
use oat\tao\model\webhooks\task\JsonWebhookPayloadFactory;
use oat\tao\model\webhooks\task\JsonWebhookResponseFactory;
use oat\tao\model\webhooks\task\WebhookPayloadFactoryInterface;
use oat\tao\model\webhooks\task\WebhookResponseFactoryInterface;
use oat\tao\model\webhooks\WebhookEventsService;
use oat\tao\model\webhooks\WebhookEventsServiceInterface;
use oat\tao\model\webhooks\WebhookFileRegistry;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\model\webhooks\WebhookTaskService;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use oat\tao\scripts\install\AddArchiveService;
use oat\tao\scripts\install\AddTmpFsHandlers;
use oat\tao\scripts\install\CreateWebhookEventLogTable;
use oat\tao\scripts\install\InstallNotificationTable;
use oat\tao\scripts\install\RegisterActionService;
use oat\tao\scripts\install\RegisterSignatureGenerator;
use oat\tao\scripts\install\SetClientLoggerConfig;
use oat\tao\scripts\install\UpdateRequiredActionUrl;
use oat\tao\scripts\tools\MigrateSecuritySettings;
use tao_models_classes_UserService;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater
{

    /**
     *
     * @param $initialVersion
     * @return string $initialVersion
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ExtensionException
     * @throws common_Exception
     */
    public function update($initialVersion)
    {

        if ($this->isBetween('0.0.0', '2.21.0')) {
            throw new \common_exception_NotImplemented('Updates from versions prior to Tao 3.1 are not longer supported, please update to Tao 3.1 first');
        }
        $this->skip('2.22.0', '5.5.0');

        if ($this->isVersion('5.5.0')) {
            $clientConfig = new ClientConfigService();
            $clientConfig->setClientConfig('themesAvailable', new ThemeConfig());
            $this->getServiceManager()->register(ClientConfigService::SERVICE_ID, $clientConfig);
            $this->setVersion('5.6.0');
        }

        $this->skip('5.6.0', '5.6.2');

        if ($this->isVersion('5.6.2')) {
            if (!$this->getServiceManager()->has(UpdateLogger::SERVICE_ID)) {
                // setup log fs
                $fsm = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
                $fsm->createFileSystem('log', 'tao/log');
                $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $fsm);

                $this->getServiceManager()->register(UpdateLogger::SERVICE_ID, new UpdateLogger([UpdateLogger::OPTION_FILESYSTEM => 'log']));
            }
            $this->setVersion('5.6.3');
        }

        $this->skip('5.6.3', '5.9.1');

        if ($this->isVersion('5.9.1')) {
            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::CONFIG_ID);

            $eventManager->detach(RoleRemovedEvent::class, ['oat\\tao\\scripts\\update\\LoggerService', 'logEvent']);
            $eventManager->detach(RoleCreatedEvent::class, ['oat\\tao\\scripts\\update\\LoggerService', 'logEvent']);
            $eventManager->detach(RoleChangedEvent::class, ['oat\\tao\\scripts\\update\\LoggerService', 'logEvent']);
            $eventManager->detach(UserCreatedEvent::class, ['oat\\tao\\scripts\\update\\LoggerService', 'logEvent']);
            $eventManager->detach(UserUpdatedEvent::class, ['oat\\tao\\scripts\\update\\LoggerService', 'logEvent']);
            $eventManager->detach(UserRemovedEvent::class, ['oat\\tao\\scripts\\update\\LoggerService', 'logEvent']);

            $this->getServiceManager()->register(EventManager::CONFIG_ID, $eventManager);

            $this->setVersion('5.9.2');
        }

        // Hotfix to register ApplicationService for instances with old tao-core version
        // ApplicationService was introduced in tao-core version 20.1.0
        if ($this->isBetween('6.0.1', '20.0.4')) {
            $options = [];
            if (defined('ROOT_PATH') && is_readable(ROOT_PATH . 'build')) {
                $content = file_get_contents(ROOT_PATH . 'build');
                $options[ApplicationService::OPTION_BUILD_NUMBER] = $content;
            }

            $applicationService = new ApplicationService($options);
            $this->getServiceManager()->register(ApplicationService::SERVICE_ID, $applicationService);
        }

        $this->skip('5.9.2', '6.0.1');

        if ($this->isVersion('6.0.1')) {
            OntologyUpdater::syncModels();
            $this->setVersion('6.1.0');
        }

        $this->skip('6.1.0', '7.16.2');

        if ($this->isVersion('7.16.2')) {
            OntologyUpdater::syncModels();
            ValidationRuleRegistry::getRegistry()->set('notEmpty', new \tao_helpers_form_validators_NotEmpty());
            $this->setVersion('7.17.0');
        }

        $this->skip('7.17.0', '7.23.0');

        if ($this->isVersion('7.23.0')) {
            $service = new \oat\tao\model\mvc\DefaultUrlService();
            $service->setRoute('default', [
                'ext'        => 'tao',
                'controller' => 'Main',
                'action'     => 'index',
                ]);
            $service->setRoute('login', [
                'ext'        => 'tao',
                'controller' => 'Main',
                'action'     => 'login',
            ]);
            $this->getServiceManager()->register(\oat\tao\model\mvc\DefaultUrlService::SERVICE_ID, $service);
            $this->setVersion('7.24.0');
        }

        $this->skip('7.24.0', '7.27.0');

        if ($this->isVersion('7.27.0')) {
            OntologyUpdater::syncModels();
            $this->setVersion('7.28.0');
        }
        $this->skip('7.28.0', '7.30.1');

        if ($this->isVersion('7.30.1')) {
            /*@var $routeService \oat\tao\model\mvc\DefaultUrlService */
            $routeService = $this->getServiceManager()->get(\oat\tao\model\mvc\DefaultUrlService::SERVICE_ID);
            $routeService->setRoute(
                'logout',
                [
                            'ext'        => 'tao',
                            'controller' => 'Main',
                            'action'     => 'logout',
                            'redirect'   => _url('entry', 'Main', 'tao'),
                        ]
            );
            $this->getServiceManager()->register(\oat\tao\model\mvc\DefaultUrlService::SERVICE_ID, $routeService);

            $this->setVersion('7.31.0');
        }

        $this->skip('7.31.0', '7.31.1');
        // add validation widget
        if ($this->isVersion('7.31.1')) {
            OntologyUpdater::syncModels();
            $this->setVersion('7.32.0');
        }

        $this->skip('7.32.0', '7.34.0');

        if ($this->isVersion('7.34.0')) {
            OntologyUpdater::syncModels();
            AclProxy::applyRule(new AccessRule(
                AccessRule::GRANT,
                TaskService::TASK_QUEUE_MANAGER_ROLE,
                ['ext' => 'tao', 'mod' => 'TaskQueue']
            ));
            $this->setVersion('7.35.0');
        }

        $this->skip('7.35.0', '7.46.0');

        if ($this->isVersion('7.46.0')) {
            $this->getServiceManager()->register(ExtraPoService::SERVICE_ID, new ExtraPoService());

            $this->setVersion('7.47.0');
        }

        $this->skip('7.47.0', '7.54.0');

        if ($this->isVersion('7.54.0')) {
            $persistence = \common_persistence_Manager::getPersistence('default');
            /** @var \common_persistence_sql_pdo_SchemaManager $schemaManager */
            $schemaManager = $persistence->getDriver()->getSchemaManager();
            $schema = $schemaManager->createSchema();
            $fromSchema = clone $schema;
            // test if already executed
            $statementsTableData = $schema->getTable('statements');
            $statementsTableData->dropIndex('idx_statements_modelid');
            $modelsTableData = $schema->getTable('models');
            $modelsTableData->dropIndex('idx_models_modeluri');
            $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
            foreach ($queries as $query) {
                $persistence->exec($query);
            }
            $this->setVersion('7.54.1');
        }

        $this->skip('7.54.1', '7.61.0');

        if ($this->isVersion('7.61.0')) {
            $setClientLoggerConfig = new SetClientLoggerConfig();
            $setClientLoggerConfig([]);
            $this->setVersion('7.62.0');
        }

        $this->skip('7.62.0', '7.68.0');

        if ($this->isVersion('7.68.0')) {
            $notifInstaller = new InstallNotificationTable();
            $notifInstaller->setServiceLocator($this->getServiceManager());
            $notifInstaller->__invoke([]);
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole', ['ext' => 'tao','mod' => 'Notification']));
            $this->setVersion('7.69.0');
        }

        $this->skip('7.69.0', '7.69.6');

        if ($this->isVersion('7.69.6')) {
            $queue = new NotificationServiceAggregator([
                'rds' =>
                    [
                        'class'   => RdsNotification::class,
                        'options' => [
                            RdsNotification::OPTION_PERSISTENCE => RdsNotification::DEFAULT_PERSISTENCE,
                            'visibility'  => false,
                        ],
                    ]
                ]);

            $this->getServiceManager()->register(NotificationServiceInterface::SERVICE_ID, $queue);

            $this->setVersion('7.70.0');
        }

        $this->skip('7.70.0', '7.73.0');

        if ($this->isVersion('7.73.0')) {
            $action = new AddTmpFsHandlers();
            $action->setServiceLocator($this->getServiceManager());
            $action->__invoke([]);
            $this->setVersion('7.74.0');
        }

        $this->skip('7.74.0', '7.82.1');

        if ($this->isVersion('7.82.1')) {
            $service = new \oat\tao\model\import\ImportersService([]);
            $this->getServiceManager()->register(\oat\tao\model\import\ImportersService::SERVICE_ID, $service);
            $this->setVersion('7.83.0');
        }

        $this->skip('7.83.0', '7.88.0');


        if ($this->isVersion('7.88.0')) {
            $service = new ExceptionInterpreterService([
                ExceptionInterpreterService::OPTION_INTERPRETERS => [
                    \Exception::class => ExceptionInterpretor::class
                ]
            ]);
            $this->getServiceManager()->register(ExceptionInterpreterService::SERVICE_ID, $service);
            $this->setVersion('7.89.0');
        }

        $this->skip('7.89.0', '7.91.2');

        if ($this->isVersion('7.91.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('7.91.3');
        }
        $this->skip('7.91.3', '8.1.0');

        if ($this->isVersion('8.1.0')) {
            if (! $this->getServiceManager()->has(Maintenance::SERVICE_ID)) {
                $maintenancePersistence = 'maintenance';

                try {
                    \common_persistence_Manager::getPersistence($maintenancePersistence);
                } catch (\common_Exception $e) {
                    \common_persistence_Manager::addPersistence($maintenancePersistence, ['driver' => 'phpfile']);
                }

                $service = new Maintenance();
                $service->setOption(Maintenance::OPTION_PERSISTENCE, $maintenancePersistence);
                $this->getServiceManager()->register(Maintenance::SERVICE_ID, $service);

                $this->getServiceManager()->get(Maintenance::SERVICE_ID)->enablePlatform();
            }
            $this->setVersion('8.2.0');
        }

        $this->skip('8.2.0', '9.1.1');

        if ($this->isVersion('9.1.1')) {
            $this->getServiceManager()->register(TokenService::SERVICE_ID, new TokenService([
                'store' => new TokenStoreSession(),
                'poolSize' => 10,
                'timeLimit' => 0
            ]));
            $this->setVersion('9.2.0');
        }

        $this->skip('9.2.0', '10.10.0');

        if ($this->isVersion('10.10.0')) {
            $this->getServiceManager()->register(ArgumentService::SERVICE_ID, new ArgumentService([
                'arguments' => [
                    new Group([new Debug(), new Info(), new Notice(), new Error(),])
                ]
            ]));
            $this->setVersion('10.11.0');
        }

        $this->skip('10.11.0', '10.12.0');

        if ($this->isVersion('10.12.0')) {
            $this->getServiceManager()->register(
                OperatedByService::SERVICE_ID,
                new OperatedByService([
                    'operatedByName' => 'Open Assessment Technologies S.A.',
                    'operatedByEmail' => 'contact@taotesting.com'
                ])
            );

            $this->setVersion('10.13.0');
        }

        $this->skip('10.13.0', '10.15.0');

        if ($this->isVersion('10.15.0')) {
            ClientLibConfigRegistry::getRegistry()->register(
                'util/locale',
                ['dateTimeFormat' => 'DD/MM/YYYY HH:mm:ss']
            );
            $this->setVersion('10.16.0');
        }

        $this->skip('10.16.0', '10.19.3');

        if ($this->isVersion('10.19.3')) {
            $operatedByService = $this->getServiceManager()->get(OperatedByService::SERVICE_ID);

            $operatedByService->setName('');
            $operatedByService->setEmail('');

            $this->getServiceManager()->register(OperatedByService::SERVICE_ID, $operatedByService);
            $this->setVersion('10.19.4');
        }

        $this->skip('10.19.4', '10.19.6');

        if ($this->isVersion('10.19.6')) {
            /**
             * @var $urlService DefaultUrlService
             */
            $urlService = $this->getServiceManager()->get(DefaultUrlService::SERVICE_ID);

            $route = $urlService->getRoute('logout');

            $route['redirect'] =  [
                'class'   => \oat\tao\model\mvc\DefaultUrlModule\TaoActionResolver::class,
                'options' => [
                    'action' => 'entry',
                    'controller' => 'Main',
                    'ext' => 'tao'
                ]
            ];

            $urlService->setRoute('logout', $route);
            $this->getServiceManager()->register(DefaultUrlService::SERVICE_ID, $urlService);
            $this->setVersion('10.20.0');
        }

        if ($this->isVersion('10.20.0')) {
            $this->runExtensionScript(UpdateRequiredActionUrl::class);
            $this->setVersion('10.21.0');
        }

        $this->skip('10.21.0', '10.24.1');

        if ($this->isVersion('10.24.1')) {
            $this->runExtensionScript(AddArchiveService::class);

            $this->setVersion('10.25.0');
        }

        $this->skip('10.25.0', '10.27.0');

        if ($this->isVersion('10.27.0')) {
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', ['ext' => 'tao','mod' => 'TaskQueueData']));
            $this->setVersion('10.28.0');
        }

        $this->skip('10.28.0', '10.28.1');

        if ($this->isVersion('10.28.1')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $config = $extension->getConfig('login');

            if (!array_key_exists('block_iframe_usage', $config)) {
                $config['block_iframe_usage'] = false;
            }
            $extension->setConfig('login', $config);

            $this->setVersion('10.29.0');
        }

        $this->skip('10.29.0', '12.2.1');

        if ($this->isVersion('12.2.1')) {
            try {
                $session = $this->getServiceManager()->get('tao/session');
            } catch (ServiceNotFoundException $e) {
                \common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->setConfig('session', false);
            }
            $this->setVersion('12.2.2');
        }

        $this->skip('12.2.2', '12.21.5');

        if ($this->isVersion('12.21.5')) {
                $service = new GetTreeService();
                $this->getServiceManager()->register(GetTreeService::SERVICE_ID, $service);
                $this->setVersion('12.21.6');
        }

        $this->skip('12.21.6', '13.1.5');

        if ($this->isVersion('13.1.5')) {
            $service = new InstantActionQueue([
                InstantActionQueue::OPTION_PERSISTENCE => 'cache',
                InstantActionQueue::OPTION_ACTIONS => [],
            ]);
            $this->getServiceManager()->register(InstantActionQueue::SERVICE_ID, $service);
            $this->setVersion('13.2.0');
        }

        $this->skip('13.2.0', '14.4.0');

        if ($this->isVersion('14.4.0')) {
            $this->getServiceManager()->register(
                ContainerService::SERVICE_ID,
                new ContainerService()
            );

            $this->setVersion('14.4.1');
        }

        $this->skip('14.4.1', '14.8.0');

        if ($this->isVersion('14.8.0')) {
            AclProxy::revokeRule(new AccessRule(
                AccessRule::GRANT,
                'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',
                ['ext' => 'tao']
            ));

            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole', ['ext' => 'tao','mod' => 'ExtensionsManager']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Api']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Breadcrumbs']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Export']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'File']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Import']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Lock']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Main']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'PasswordRecovery']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Permission']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'PropertiesAuthoring']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'QueueAction']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'RestResource']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'RestUser']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Roles']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'TaskQueue']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'Users']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'WebService']));

            $this->setVersion('14.8.1');
        }

        $this->skip('14.8.1', '14.11.2');

        if ($this->isVersion('14.11.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('14.11.3');
        }

        if ($this->isVersion('14.11.3')) {
            $resourceWatcher = new ResourceWatcher([ResourceWatcher::OPTION_THRESHOLD => 1]);
            $this->getServiceManager()->register(ResourceWatcher::SERVICE_ID, $resourceWatcher);

            OntologyUpdater::syncModels();

            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(ResourceCreated::class, [ResourceWatcher::SERVICE_ID, 'catchCreatedResourceEvent']);
            $eventManager->attach(ResourceUpdated::class, [ResourceWatcher::SERVICE_ID, 'catchUpdatedResourceEvent']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
            $this->setVersion('14.12.0');
        }

        $this->skip('14.12.0', '14.15.0');

        if ($this->isVersion('14.15.0')) {
            OntologyUpdater::syncModels();
            $this->setVersion('14.16.0');
        }

        $this->skip('14.16.0', '14.19.0');

        if ($this->isVersion('14.19.0')) {
            $action = new RegisterActionService();
            $action->setServiceLocator($this->getServiceManager());
            $action->__invoke([]);

            $this->setVersion('14.20.0');
        }

        // register OAuthService
        if ($this->isVersion('14.20.0')) {
            if (!$this->getServiceManager()->has(OauthService::SERVICE_ID)) {
                $this->getServiceManager()->register(OauthService::SERVICE_ID, new OauthService([
                    OauthService::OPTION_DATA_STORE => new DataStore([
                        DataStore::OPTION_NONCE_STORE => new NoNonce()
                    ])
                ]));
            }
            $this->setVersion('14.21.0');
        }
        $this->skip('14.21.0', '14.23.3');

        if ($this->isVersion('14.23.3')) {
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext' => 'tao','mod' => 'RestClass']));

            $this->getServiceManager()->register(ResourceService::SERVICE_ID, new ResourceService());
            $this->getServiceManager()->register(ListResourceLookup::SERVICE_ID, new ListResourceLookup());
            $this->getServiceManager()->register(TreeResourceLookup::SERVICE_ID, new TreeResourceLookup());

            $this->setVersion('15.0.0');
        }

        $this->skip('15.0.0', '15.4.0');

        if ($this->isVersion('15.4.0')) {
            $setClientLoggerConfig = new SetClientLoggerConfig();
            $setClientLoggerConfig([]);
            AclProxy::applyRule(new AccessRule('grant', TaoRoles::BASE_USER, ['ext' => 'tao', 'mod' => 'Log', 'act' => 'log']));
            $this->setVersion('15.5.0');
        }

        $this->skip('15.5.0', '15.12.0');

        if ($this->isVersion('15.12.0')) {
            OntologyUpdater::syncModels();
            $this->setVersion('15.13.0');
        }

        if ($this->isVersion('15.13.0')) {
            $this->getServiceManager()->register(IndexService::SERVICE_ID, new IndexService());

            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(ResourceDeleted::class, [ResourceWatcher::SERVICE_ID, 'catchDeletedResourceEvent']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('16.0.0');
        }

        $this->skip('16.0.0', '16.4.0');

        if ($this->isVersion('16.4.0')) {
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', ['ext' => 'tao','mod' => 'RestResource']));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', ['ext' => 'tao','mod' => 'RestClass']));

            $this->setVersion('17.0.0');
        }

        $this->skip('17.0.0', '17.8.4');

        if ($this->isVersion('17.8.4')) {
            OntologyUpdater::syncModels();
            $this->setVersion('17.9.0');
        }

        if ($this->isVersion('17.9.0')) {
            $this->getServiceManager()->register(
                RestSessionFactory::SERVICE_ID,
                new RestSessionFactory([
                    RestSessionFactory::OPTION_BUILDERS => [
                        HttpBasicAuthBuilder::class
                    ]
                ])
            );
            $this->setVersion('17.10.0');
        }

        $this->skip('17.10.0', '17.10.2');

        if ($this->isVersion('17.10.2')) {
            OntologyUpdater::syncModels();

            $this->getServiceManager()->register(UserLocks::SERVICE_ID, new NoUserLocksService());

            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);

            $eventManager->attach(LoginFailedEvent::class, [UserLocks::SERVICE_ID, 'catchFailedLogin']);
            $eventManager->attach(LoginSucceedEvent::class, [UserLocks::SERVICE_ID, 'catchSucceedLogin']);

            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('17.11.0');
        }

        $this->skip('17.11.0', '17.12.2');

        if ($this->isVersion('17.12.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('17.13.0');
        }

        $this->skip('17.13.0', '17.13.1');

        if ($this->isVersion('17.13.1')) {
            $rdfLang = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, '../../locales/fr-CA/lang.rdf');
            $iterator = new FileIterator($rdfLang);
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                //make sure that the ontology is clear to avoid errors if triple is in multiple time
                $rdf->remove($triple);
                $rdf->add($triple);
            }
            $this->setVersion('17.13.2');
        }
        $this->skip('17.13.2', '17.13.3');

        if ($this->isVersion('17.13.3')) {
            $service = new UserCsvImporterFactory([
                UserCsvImporterFactory::OPTION_DEFAULT_SCHEMA => [
                    OntologyUserMapper::OPTION_SCHEMA_MANDATORY => [
                        'label' => OntologyRdfs::RDFS_LABEL,
                        'interface language' => UserRdf::PROPERTY_UILG,
                        'login' => UserRdf::PROPERTY_LOGIN,
                        'password' => UserRdf::PROPERTY_PASSWORD,
                    ],
                    OntologyUserMapper::OPTION_SCHEMA_OPTIONAL => [
                        'default language' => UserRdf::PROPERTY_DEFLG,
                        'first name' => UserRdf::PROPERTY_FIRSTNAME,
                        'last name' => UserRdf::PROPERTY_LASTNAME,
                        'mail' => UserRdf::PROPERTY_MAIL,
                    ]
                ]
            ]);

            $this->getServiceManager()->register(UserCsvImporterFactory::SERVICE_ID, $service);
            $this->setVersion('17.14.0');
        }

        $this->skip('17.14.0', '17.15.1');

        if ($this->isVersion('17.15.1')) {
            $this->getServiceManager()->register(
                \tao_models_classes_UserService::SERVICE_ID,
                new \tao_models_classes_UserService([])
            );
            $this->setVersion('17.16.0');
        }
        $this->skip('17.16.0', '17.16.1');

        if ($this->isVersion('17.16.1')) {
            AclProxy::applyRule(new AccessRule('grant', TaoRoles::REST_PUBLISHER, ['ext' => 'tao', 'mod' => 'TaskQueue', 'act' => 'get']));
            $this->setVersion('17.17.0');
        }

        $this->skip('17.17.0', '18.4.0');

        if ($this->isVersion('18.4.0')) {
            AclProxy::applyRule(new AccessRule('grant', TaoRoles::BASE_USER, ['ext' => 'tao', 'mod' => 'Log', 'act' => 'log']));
            $this->setVersion('18.4.1');
        }

        if ($this->isVersion('18.4.1')) {
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', ['ext' => 'tao', 'mod' => 'Health']));
            $this->setVersion('18.5.0');
        }

        $this->skip('18.5.0', '18.6.0');

        if ($this->isVersion('18.6.0')) {
            ClientLibConfigRegistry::getRegistry()->register(
                'util/shortcut/registry',
                ['debounceDelay' => 250]
            );
            $this->setVersion('18.7.0');
        }

        $this->skip('18.7.0', '18.7.2');

        if ($this->isVersion('18.7.2')) {
            ClientLibConfigRegistry::getRegistry()->remove(
                'util/shortcut/registry'
            );
            $this->setVersion('18.8.0');
        }

        $this->skip('18.8.0', '19.7.1');

        if ($this->isVersion('19.7.1')) {
            // register new queue dispatcher service with default values
            $taskLogService = new TaskLog([
                TaskLogInterface::OPTION_TASK_LOG_BROKER => new RdsTaskLogBroker('default')
            ]);
            $this->getServiceManager()->register(TaskLogInterface::SERVICE_ID, $taskLogService);

            try {
                $taskLogService->createContainer();
            } catch (\Exception $e) {
                return \common_report_Report::createFailure('Creating task log container failed');
            }

            $queueService = new QueueDispatcher([
                QueueDispatcherInterface::OPTION_QUEUES       => [
                    new Queue('queue', new InMemoryQueueBroker())
                ],
                QueueDispatcherInterface::OPTION_TASK_LOG     => TaskLogInterface::SERVICE_ID,
                QueueDispatcherInterface::OPTION_TASK_TO_QUEUE_ASSOCIATIONS => [],
                QueueDispatcherInterface::OPTION_TASK_SELECTOR_STRATEGY => new WeightStrategy()
            ]);

            $this->getServiceManager()->register(QueueDispatcherInterface::SERVICE_ID, $queueService);

            AclProxy::applyRule(new AccessRule('grant', TaoRoles::BASE_USER, ['ext' => 'tao','mod' => 'TaskQueueWebApi']));

            $this->setVersion('19.8.0');
        }

        $this->skip('19.8.0', '19.9.0');

        if ($this->isVersion('19.9.0')) {
            $service = new MetricsService();
            $service->setOption(MetricsService::OPTION_METRICS, []);
            $this->getServiceManager()->register(MetricsService::SERVICE_ID, $service);
            $this->setVersion('19.10.0');
        }

        $this->skip('19.10.0', '19.19.0');

        if ($this->isVersion('19.19.0')) {
            /** @var TaskLogInterface|ConfigurableService $taskLogService */
            $taskLogService = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

            $taskLogService->linkTaskToCategory(ImportByHandler::class, TaskLogInterface::CATEGORY_IMPORT);
            $taskLogService->linkTaskToCategory(ExportByHandler::class, TaskLogInterface::CATEGORY_EXPORT);

            $this->getServiceManager()->register(TaskLogInterface::SERVICE_ID, $taskLogService);
            $this->setVersion('19.20.0');
        }

        $this->skip('19.20.0', '21.2.0');

        if ($this->isVersion('21.2.0')) {
            $resourceJsonMetadataCompiler = new ResourceJsonMetadataCompiler();
            $this->getServiceManager()->register(ResourceJsonMetadataCompiler::SERVICE_ID, $resourceJsonMetadataCompiler);

            $this->setVersion('21.3.0');
        }

        $this->skip('21.3.0', '21.4.0');

        if ($this->isVersion('21.4.0')) {
            $taskSerializer = new TaskSerializerService();
            $this->getServiceManager()->register(TaskSerializerService::SERVICE_ID, $taskSerializer);

            $this->setVersion('21.5.0');
        }

        $this->skip('21.5.0', '22.9.1');

        if ($this->isVersion('22.9.1')) {
            OntologyUpdater::syncModels();

            $iterator = new FileIterator(__DIR__ . '/../../locales/nl-BE/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->add($triple);
            }

            $this->setVersion('22.10.2');
        }

        $this->skip('22.10.0', '26.1.7');

        if ($this->isVersion('26.1.7')) {
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, TaoRoles::SYSTEM_ADMINISTRATOR, Users::class));
            AclProxy::applyRule(new AccessRule(AccessRule::GRANT, TaoRoles::GLOBAL_MANAGER, Users::class));

            $userService = $this->getServiceManager()->get(tao_models_classes_UserService::SERVICE_ID);
            $userService->setOption(tao_models_classes_UserService::OPTION_ALLOW_API, false);
            $this->getServiceManager()->register(tao_models_classes_UserService::SERVICE_ID, $userService);

            $this->setVersion('27.0.0');
        }

        $this->skip('27.0.0', '27.1.2');

        if ($this->isVersion('27.1.2')) {
            if (!$this->getServiceManager()->has(RouteAnnotationService::SERVICE_ID)) {
                $annotationService = new RouteAnnotationService();
                $this->getServiceManager()->register(RouteAnnotationService::SERVICE_ID, $annotationService);
            }

            if (!$this->getServiceManager()->has(AnnotationReaderService::SERVICE_ID)) {
                $readerService = new AnnotationReaderService();
                $this->getServiceManager()->register(AnnotationReaderService::SERVICE_ID, $readerService);
            }

            if (!$this->getServiceManager()->has(ControllerService::SERVICE_ID)) {
                $controllerService = new ControllerService();
                $this->getServiceManager()->register(ControllerService::SERVICE_ID, $controllerService);
            }

            $this->setVersion('27.2.0');
        }

        $this->skip('27.2.0', '27.3.0');

        if ($this->isVersion('27.3.0')) {
            AclProxy::applyRule(new AccessRule('grant', TaoRoles::ANONYMOUS, ['ext' => 'tao', 'mod' => 'RestVersion', 'act' => 'index']));

            $this->setVersion('27.4.0');
        }

        $this->skip('27.4.0', '30.0.1');

        if ($this->isVersion('30.0.1')) {
            $register = new RegisterSignatureGenerator();
            $register->setServiceLocator($this->getServiceManager());
            $register->__invoke('');

            $this->setVersion('30.0.2');
        }

        $this->skip('30.0.2', '30.0.5');

        if ($this->isVersion('30.0.5')) {
            AclProxy::applyRule(new AccessRule(
                AccessRule::GRANT,
                TaoRoles::TAO_MANAGER,
                ['ext' => 'tao', 'mod' => 'Security']
            ));

            \common_persistence_Manager::addPersistence('settings', ['driver' => 'phpfile']);

            $this->getServiceManager()->register(
                SettingsStorage::SERVICE_ID,
                new SettingsStorage(['persistence' => 'settings'])
            );

            $this->setVersion('30.1.0');
        }

        if ($this->isVersion('30.1.0')) {
            /** @var SettingsStorage $settingsStorage */
            $settingsStorage = $this->getServiceManager()->get(SettingsStorage::SERVICE_ID);

            if ($settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_SETTING) === false) {
                $settingsStorage->set(CspHeaderSettingsInterface::CSP_HEADER_SETTING, '*');
            }


            $this->setVersion('30.1.1');
        }

        $this->skip('30.1.1', '31.0.0');

        if ($this->isVersion('31.0.0')) {
            // Removes previously set util/local[dateTimeFormat] key prior to registering it with the correct values.
            $registry = ClientLibConfigRegistry::getRegistry();
            $localeValues = $registry->get('util/locale');
            // If 'util/locale' is not set in the registry, $localeValues is '' and retrieving a string index on a string would fail.
            if (is_array($localeValues) && isset($localeValues['dateTimeFormat'])) {
                unset($localeValues['dateTimeFormat']);
            }
            $registry->register('util/locale', $localeValues);

            $ext = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
            $ext->setConfig(\tao_helpers_Date::CONFIG_KEY, new EuropeanFormatter());

            $this->setVersion('31.1.0');
        }

        $this->skip('31.1.0', '33.6.0');

        if ($this->isVersion('33.6.0')) {
            /** @var TokenService $tokenService */
            $tokenService = $this->getServiceManager()->get(TokenService::SERVICE_ID);

            /** @var TokenStore $tokenStore */
            $tokenStore = $tokenService->getOption(TokenService::OPTION_STORE);
            $tokenStore->removeTokens();
            $this->setVersion('34.0.0');
        }

        $this->skip('34.0.0', '35.3.0');

        if ($this->isVersion('35.3.0')) {
            OntologyUpdater::syncModels();

            $iterator = new FileIterator(__DIR__ . '/../../locales/en-GB/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->add($triple);
            }
            $this->setVersion('35.4.0');
        }

        $this->skip('35.4.0', '35.8.1');

        if ($this->isVersion('35.8.1')) {
            OntologyUpdater::syncModels();

            $iterator = new FileIterator(__DIR__ . '/../../locales/en-GB/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->remove($triple);
                $rdf->add($triple);
            }
            $this->setVersion('35.8.2');
        }

        $this->skip('35.8.2', '38.0.0');

        if ($this->isVersion('38.0.0')) {
            OntologyUpdater::syncModels();

            $iterator = new FileIterator(__DIR__ . '/../../locales/ru-RU/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->remove($triple);
                $rdf->add($triple);
            }
            $this->setVersion('38.0.1');
        }

        $this->skip('38.0.1', '38.1.2');

        if ($this->isVersion('38.1.2')) {
            $iterator = new FileIterator(__DIR__ . '/../../locales/ru-RU/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->remove($triple);
                $rdf->add($triple);
            }

            $iterator = new FileIterator(__DIR__ . '/../../locales/es-MX/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();

            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->remove($triple);
                $rdf->add($triple);
            }

            OntologyUpdater::syncModels();

            $this->setVersion('38.1.3');
        }

        $this->skip('38.1.3', '38.3.0');
        if ($this->isVersion('38.3.0')) {
            $this->getServiceManager()->register(
                WebhookEventsServiceInterface::SERVICE_ID,
                new WebhookEventsService([WebhookEventsService::OPTION_SUPPORTED_EVENTS => []])
            );
            $this->getServiceManager()->register(
                WebhookRegistryInterface::SERVICE_ID,
                new WebhookFileRegistry([
                    WebhookFileRegistry::OPTION_WEBHOOKS => [],
                    WebhookFileRegistry::OPTION_EVENTS => [],
                ])
            );
            $this->setVersion('38.3.2');
        }

        $this->skip('38.3.2', '38.3.3');

        if ($this->isVersion('38.3.3')) {
            $options = [
                SettingsStorage::OPTION_PERSISTENCE => 'default_kv',
                SettingsStorage::OPTION_KEY_NAMESPACE => 'tao:settings:'
            ];
            $settingsStorage = new SettingsStorage($options);
            $this->getServiceManager()->register(SettingsStorageInterface::SERVICE_ID, $settingsStorage);

            $defaultHeaderSetting = 'self';
            $defaultHeaderList = '';
            $settingsStorage->set(CspHeaderSettingsInterface::CSP_HEADER_SETTING, $defaultHeaderSetting);
            $settingsStorage->set(CspHeaderSettingsInterface::CSP_HEADER_LIST, $defaultHeaderList);

            $this->runExtensionScript(MigrateSecuritySettings::class, ['settings', '--wet']);

            $msg = 'If you have more than one server execute %s script on all servers to migrate existing security settings from file to new persistence' . PHP_EOL;
            $msg .= 'The script may be executed with dry/wet run options to see which settings will be migrated.';
            $msg = sprintf($msg, MigrateSecuritySettings::class);
            $this->addReport(new Report(Report::TYPE_WARNING, $msg));

            $this->setVersion('38.4.0');
        }

        $this->skip('38.4.0', '38.8.0');

        if ($this->isVersion('38.8.0')) {
            $this->getServiceManager()->register(
                WebhookTaskServiceInterface::SERVICE_ID,
                new WebhookTaskService()
            );
            $this->getServiceManager()->register(
                WebhookPayloadFactoryInterface::SERVICE_ID,
                new JsonWebhookPayloadFactory()
            );
            $this->getServiceManager()->register(
                WebhookResponseFactoryInterface::SERVICE_ID,
                new JsonWebhookResponseFactory()
            );
            $this->setVersion('38.9.0');
        }

        $this->skip('38.9.0', '38.9.5');

        if ($this->isVersion('38.9.5')) {
            $webhookEventLogTableCreator = new CreateWebhookEventLogTable();
            $webhookEventLogTableCreator->setServiceLocator($this->getServiceManager());
            $webhookEventLogTableCreator->__invoke([]);

            $this->getServiceManager()->register(
                WebhookLogRepositoryInterface::SERVICE_ID,
                new WebhookLogRepository([WebhookLogRepository::OPTION_PERSISTENCE => 'default'])
            );
            $this->getServiceManager()->register(
                WebhookEventLogInterface::SERVICE_ID,
                new WebhookRdsEventLogService()
            );
            $this->setVersion('38.10.0');
        }

        $this->skip('38.10.0', '38.11.0');

        if ($this->isVersion('38.11.0')) {
            /** @var \common_persistence_Persistence $persistence */
            $persistence = \common_persistence_Manager::getPersistence('default');
            /** @var \common_persistence_sql_dbal_SchemaManager $schemaManager */
            $schemaManager = $persistence->getDriver()->getSchemaManager();
            $schema = $schemaManager->createSchema();
            $fromSchema = clone $schema;

            $logTable = $schema->getTable(WebhookLogRepository::TABLE_NAME);

            if ($logTable->getPrimaryKey() === null) {
                $logTable->setPrimaryKey([WebhookLogRepository::COLUMN_ID]);

                $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
                foreach ($queries as $query) {
                    $persistence->exec($query);
                }
            }

            $this->setVersion('38.11.1');
        }

        $this->skip('38.11.1', '39.0.6');

        if ($this->isVersion('39.0.6')) {
            OntologyUpdater::syncModels();
            $iterator = new FileIterator(__DIR__ . '/../../locales/lt-LT/lang.rdf');
            $rdf = ModelManager::getModel()->getRdfInterface();
            /* @var \core_kernel_classes_Triple $triple */
            foreach ($iterator as $triple) {
                $rdf->add($triple);
            }
            $this->setVersion('39.1.0');
        }

        //Removed update from 39.3.2 -> 39.3.3 due to broken operation cause by removal of `tao_install_utils_ModelCreator` class
        //Related PR https://github.com/oat-sa/tao-core/pull/2404. Update is re-played on 40.9.5 to 40.9.6
        $this->skip('39.1.0', '40.3.3');

        if ($this->isVersion('40.3.3')) {
            $extManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);
            $sources = $extManager->getExtensionById('tao')->getConfig('mediaSources');
            $sources = is_array($sources) ? $sources : [];
            foreach ($sources as $key => $source) {
                if (is_string($source) && class_exists($source)) {
                    $sources[$key] = new $source([]);
                }
            }
            $service = new MediaService([MediaService::OPTION_SOURCE => $sources]);
            $this->getServiceManager()->register(MediaService::SERVICE_ID, $service);
            $extManager->getExtensionById('tao')->unsetConfig('mediaSources');

            $this->setVersion('40.3.4');
        }

        $this->skip('40.3.4', '40.6.1');

        if ($this->isVersion('40.6.1')) {
            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->detach(FileUploadedEvent::class, [UploadService::class, 'listenUploadEvent']);
            $eventManager->detach(UploadLocalCopyCreatedEvent::class, [UploadService::class, 'listenLocalCopyEvent']);
            $eventManager->attach(FileUploadedEvent::class, [UploadService::SERVICE_ID, 'listenUploadEvent']);
            $eventManager->attach(UploadLocalCopyCreatedEvent::class, [UploadService::SERVICE_ID, 'listenLocalCopyEvent']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('40.7.0');
        }

        $this->skip('40.7.0', '40.8.1');

        if ($this->isVersion('40.8.1')) {
            if (!$this->getServiceManager()->has(UserService::SERVICE_ID)) {
                $this->getServiceManager()->register(UserService::SERVICE_ID, new GenerisUserService());
            }
            $this->setVersion('40.9.0');
        }

        $this->skip('40.9.0', '40.9.5');

        if ($this->isVersion('40.9.5')) {
            $langModel = \tao_models_classes_LanguageService::singleton()->getLanguageDefinition();
            $modelRdf = $this->getServiceManager()->get(Ontology::SERVICE_ID)->getRdfInterface();
            foreach ($langModel as $triple) {
                $triple->modelid = core_kernel_persistence_smoothsql_SmoothModel::DEFAULT_WRITABLE_MODEL;
                $modelRdf->remove($triple);
            }
            OntologyUpdater::syncModels();
            $this->setVersion('40.9.6');
        }

        $this->skip('40.9.6', '41.0.1');

        if ($this->isVersion('41.0.1')) {
            AclProxy::applyRule(new AccessRule('grant', TaoRoles::REST_PUBLISHER, ['ext' => 'tao', 'mod' => 'TaskQueue', 'act' => 'getStatus']));
            $this->setVersion('41.0.2');
        }


        if ($this->isVersion('41.0.2')) {
            $this->getServiceManager()->register(SecureResourceServiceInterface::SERVICE_ID, new SecureResourceService());

            $this->setVersion('41.1.0');
        }

        $this->skip('41.1.0', '41.1.2');

        if ($this->isVersion('41.1.2')) {
            $serviceManager = $this->getServiceManager();

            /** @var SettingsStorageInterface $storage */
            $storage = $serviceManager->get(SettingsStorageInterface::SERVICE_ID);
            $securitySettingsRepository = new SecuritySettingsRepository($storage);

            $serviceManager->register(
                SecuritySettingsRepositoryInterface::SERVICE_ID,
                $securitySettingsRepository
            );
            $serviceManager->register(
                ActionProtector::SERVICE_ID,
                new ActionProtector(
                    $securitySettingsRepository,
                    [
                        'X-Content-Type-Options: nosniff',
                    ]
                )
            );

            $this->setVersion('41.2.0');
        }
        $this->skip('41.2.0', '41.5.1');

        if ($this->isVersion('41.5.1')) {
            $this->addReport(
                Report::createInfo('To make SecureResourceService use cache see example from default config at: tao/config/default/SecureResourceService.conf.php ')
            );
            $this->setVersion('41.6.0');
        }

        $this->skip('41.6.0', '41.7.0');
        if ($this->isVersion('41.7.0')) {
            $oauthService = $this->getServiceManager()->get(OauthService::SERVICE_ID);
            $oauthService->setOption(OauthService::OPTION_LOCKOUT_SERVICE, new NoLockout());
            $this->getServiceManager()->register(OauthService::SERVICE_ID, $oauthService);
            $this->setVersion('41.8.0');
        }

        $this->skip('41.8.0', '42.0.3');
        if ($this->isVersion('42.0.3')) {
            $this->getServiceManager()->unregister('tao/UnionSearchService');
            $this->setVersion('42.0.4');
        }

        $this->skip('42.0.4', '42.10.3');
    }
}
