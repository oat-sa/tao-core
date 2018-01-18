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
use common_ext_ExtensionsManager;
use League\Flysystem\Adapter\Local;
use oat\funcAcl\models\ModuleAccessService;
use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\fileReference\ResourceFileSerializer;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventManager;
use oat\oatbox\filesystem\Directory;
use oat\tao\helpers\Template;
use oat\tao\model\accessControl\func\implementation\SimpleAccess;
use oat\tao\model\asset\AssetService;
use oat\tao\model\cliArgument\argument\implementation\Group;
use oat\tao\model\cliArgument\argument\implementation\verbose\Debug;
use oat\tao\model\cliArgument\argument\implementation\verbose\Error;
use oat\tao\model\cliArgument\argument\implementation\verbose\Info;
use oat\tao\model\cliArgument\argument\implementation\verbose\Notice;
use oat\tao\model\cliArgument\ArgumentService;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\event\RoleChangedEvent;
use oat\tao\model\event\RoleCreatedEvent;
use oat\tao\model\event\RoleRemovedEvent;
use oat\tao\model\event\UserCreatedEvent;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\event\UserUpdatedEvent;
use oat\tao\model\maintenance\Maintenance;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\implementation\RdsNotification;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\resources\ResourceWatcher;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\security\xsrf\TokenStoreSession;
use oat\tao\model\service\ContainerService;
use oat\tao\model\Tree\GetTreeService;
use oat\tao\scripts\install\AddArchiveService;
use oat\tao\scripts\install\InstallNotificationTable;
use oat\tao\scripts\install\AddTmpFsHandlers;
use oat\tao\scripts\install\UpdateRequiredActionUrl;
use tao_helpers_data_GenerisAdapterRdf;
use common_Logger;
use oat\tao\model\search\SearchService;
use oat\tao\model\search\zend\ZendSearch;
use oat\tao\model\ClientLibRegistry;
use oat\generis\model\kernel\persistence\file\FileModel;
use oat\generis\model\data\ModelManager;
use oat\tao\model\lock\implementation\OntoLock;
use oat\tao\model\lock\implementation\NoLock;
use oat\tao\model\lock\LockManager;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\websource\TokenWebSource;
use oat\tao\model\websource\WebsourceManager;
use oat\tao\model\websource\ActionWebSource;
use oat\tao\model\websource\DirectWebSource;
use oat\tao\model\search\strategy\GenerisSearch;
use oat\tao\model\entryPoint\BackOfficeEntrypoint;
use oat\tao\model\entryPoint\EntryPointService;
use oat\tao\model\ThemeRegistry;
use oat\tao\model\entryPoint\PasswordReset;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\theme\ThemeService;
use oat\tao\model\theme\DefaultTheme;
use oat\tao\model\theme\CompatibilityTheme;
use oat\tao\model\theme\Theme;
use oat\tao\model\requiredAction\implementation\RequiredActionService;
use oat\tao\model\extension\UpdateLogger;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\clientConfig\ClientConfigService;
use oat\tao\model\clientConfig\sources\ThemeConfig;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\oatbox\task\TaskService;
use oat\tao\model\i18n\ExtraPoService;
use oat\tao\scripts\install\SetClientLoggerConfig;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\tao\model\mvc\error\ExceptionInterpretor;
use oat\tao\model\OperatedByService;
use oat\tao\model\actionQueue\implementation\InstantActionQueue;
use oat\tao\model\oauth\OauthService;
use oat\tao\model\oauth\DataStore;
use oat\tao\model\oauth\nonce\NoNonce;
use oat\tao\scripts\install\RegisterActionService;
use oat\tao\model\resources\ResourceService;
use oat\tao\model\resources\ListResourceLookup;
use oat\tao\model\resources\TreeResourceLookup;
use oat\tao\model\user\TaoRoles;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater {

    /**
     *
     * @param $initialVersion
     * @return string $initialVersion
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ExtensionException
     * @throws common_Exception
     */
    public function update($initialVersion) {

        $extensionManager = common_ext_ExtensionsManager::singleton();

        //migrate from 2.6 to 2.7.0
        if ($this->isVersion('2.6')) {

            //create Js config
            $ext = $extensionManager->getExtensionById('tao');
            $config = array(
                'timeout' => 30
            );
            $ext->setConfig('js', $config);

            $this->setVersion('2.7.0');
        }

        //migrate from 2.7.0 to 2.7.1
        if ($this->isVersion('2.7.0')) {

            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'indexation_2_7_1.rdf';

            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $this->setVersion('2.7.1');
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }

        if ($this->isVersion('2.7.1')) {
            SearchService::setSearchImplementation(ZendSearch::createSearch());
            $this->setVersion('2.7.2');
        }

        // upgrade is requied for asset service to continue working
        if ($this->isBetween('2.7.2','2.13.2')) {
            if (!$this->getServiceManager()->has(AssetService::SERVICE_ID))
            {
                $this->getServiceManager()->register(AssetService::SERVICE_ID, new AssetService());
            }
        }

        if ($this->isVersion('2.7.2')) {
            foreach ($extensionManager->getInstalledExtensions() as $extension) {
                $jsPath = trim(Template::js('', $extension->getId()), '/');
                ClientLibRegistry::getRegistry()->register($extension->getId(), $jsPath);

                $cssPath = trim(Template::css('', $extension->getId()), '/');
                ClientLibRegistry::getRegistry()->register($extension->getId().'Css', $cssPath);
            }
             $this->setVersion('2.7.3');
        }

        if ($this->isVersion('2.7.3')) {

            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'indexation_2_7_4.rdf';

            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $this->setVersion('2.7.4');
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }

        if ($this->isVersion('2.7.4')) {
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'model_2_7_5.rdf';

            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $this->setVersion('2.7.5');
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }

        if ($this->isVersion('2.7.5')) {
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'index_type_2_7_6.rdf';

            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $this->setVersion('2.7.6');
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }

        if ($this->isVersion('2.7.6')) {

            $dir = FILES_PATH.'updates'.DIRECTORY_SEPARATOR.'pre_2.7.6';
            if (!mkdir($dir, 0700, true)) {
                throw new \common_exception_Error('Unable to log update to '.$dir);
            }
            FileModel::toFile($dir.DIRECTORY_SEPARATOR.'backup.rdf', ModelManager::getModel()->getRdfInterface());

            OntologyUpdater::correctModelId(dirname(__FILE__).DIRECTORY_SEPARATOR.'indexation_2_7_1.rdf');
            OntologyUpdater::correctModelId(dirname(__FILE__).DIRECTORY_SEPARATOR.'indexation_2_7_4.rdf');
            OntologyUpdater::correctModelId(dirname(__FILE__).DIRECTORY_SEPARATOR.'model_2_7_5.rdf');
            OntologyUpdater::correctModelId(dirname(__FILE__).DIRECTORY_SEPARATOR.'index_type_2_7_6.rdf');

            // syncronise also adds translations to correct modelid
            OntologyUpdater::syncModels();

            // remove translations from model 1
            $persistence = \common_persistence_SqlPersistence::getPersistence('default');

            $result = $persistence->query("SELECT DISTINCT subject FROM statements WHERE NOT modelId = 1");
            $toCleanup = array();
            while ($row = $result->fetch()) {
                $toCleanup[] = $row['subject'];
            }

            $query = "DELETE from statements WHERE modelId = 1 AND subject = ? "
                    ."AND predicate IN ('".OntologyRdfs::RDFS_LABEL."','".OntologyRdfs::RDFS_COMMENT."') ";
            foreach ($toCleanup as $subject) {
                $persistence->exec($query,array($subject));
            }

            $this->setVersion('2.7.7');
        }

        // update FuncAccessControl early to support access changes
        if ($this->isBetween('2.7.7', '2.17.4')) {
            $implClass = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConfig('FuncAccessControl');
            if (is_string($implClass)) {
                $impl = new $implClass;
                $this->getServiceManager()->register(AclProxy::SERVICE_ID, $impl);
            }
        }

        if ($this->isVersion('2.7.7')) {
            $lockImpl = (defined('ENABLE_LOCK') && ENABLE_LOCK)
                ? new OntoLock()
                : new NoLock();
            LockManager::setImplementation($lockImpl);
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', array('ext'=>'tao','mod' => 'Lock')));

            $this->setVersion('2.7.8');
        }

        if ($this->isVersion('2.7.8')) {
            if ($this->migrateFsAccess()) {
                $this->setVersion('2.7.9');
            }
        }

        if ($this->isVersion('2.7.9')) {
            // update role classes
            OntologyUpdater::syncModels();
            $this->setVersion('2.7.10');
        }

        if ($this->isVersion('2.7.10')) {
            // correct access roles
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', array('act'=>'tao_actions_Lists@getListElements')));
            AclProxy::revokeRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', array('ext'=>'tao','mod' => 'Lock')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', array('act'=>'tao_actions_Lock@release')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', array('act'=>'tao_actions_Lock@locked')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#LockManagerRole', array('act'=>'tao_actions_Lock@forceRelease')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', array('ext'=>'tao','mod' => 'Search')));
            $this->setVersion('2.7.11');
        }

        if ($this->isVersion('2.7.11')) {
            // move session abstraction
            if (defined("PHP_SESSION_HANDLER") && class_exists(PHP_SESSION_HANDLER)) {
                if (PHP_SESSION_HANDLER == 'common_session_php_KeyValueSessionHandler') {
                    $sessionHandler = new \common_session_php_KeyValueSessionHandler(array(
                        \common_session_php_KeyValueSessionHandler::OPTION_PERSISTENCE => 'session'
                    ));
                } else {
                    $sessionHandler = new PHP_SESSION_HANDLER();
                }
                $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
                $ext->setConfig(\Bootstrap::CONFIG_SESSION_HANDLER, $sessionHandler);
            }
            $this->setVersion('2.7.12');
        }

        if ($this->isVersion('2.7.12')) {
            // add the property manager
            OntologyUpdater::syncModels();

            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyManagerRole', array('controller' => 'tao_actions_Lists')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyManagerRole', array('controller' => 'tao_actions_PropertiesAuthoring')));
            $this->setVersion('2.7.13');
        }

        if ($this->isVersion('2.7.13')) {
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', array('ext'=>'tao', 'mod' => 'PasswordRecovery', 'act' => 'index')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', array('ext'=>'tao', 'mod' => 'PasswordRecovery', 'act' => 'resetPassword')));

            $this->setVersion('2.7.14');
        }

        if ($this->isVersion('2.7.14')) {
            // index user logins
            OntologyUpdater::syncModels();
            $this->setVersion('2.7.15');
        }

        // reset the search impl for machines that missed 2.7.1 update due to merge
        if ($this->isVersion('2.7.15') || $this->isVersion('2.7.16')) {
            try {
                SearchService::getSearchImplementation();
                // all good
            } catch (\common_exception_Error $error) {
                SearchService::setSearchImplementation(new GenerisSearch());
            }
            $this->setVersion('2.7.16');
        }

        if ($this->isVersion('2.7.16')) {
            $registry = ClientLibRegistry::getRegistry();
            $map = $registry->getLibAliasMap();
            foreach ($map as $id => $fqp) {
                $registry->remove($id);
                $registry->register($id, $fqp);
            }
            $this->setVersion('2.7.17');
        }

        // semantic versioning
        $this->skip('2.7.17','2.8.0');

        if ($this->isBetween('2.8.0','2.13.0')) {

            $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $entryPoints = $tao->getConfig('entrypoint');

            if (is_array($entryPoints) || $entryPoints == false) {

                $service = new EntryPointService();
                if (is_array($entryPoints)) {
                    foreach ($entryPoints as $id => $entryPoint) {
                        $service->overrideEntryPoint($id, $entryPoint);
                        $service->activateEntryPoint($id, EntryPointService::OPTION_POSTLOGIN);
                    }
                }
                // register, don't activate
                $passwordResetEntry = new PasswordReset();
                $service->overrideEntryPoint($passwordResetEntry->getId(), $passwordResetEntry);

                $this->getServiceManager()->register(EntryPointService::SERVICE_ID, $service);

            }
        }

        if ($this->isVersion('2.8.0')) {
            $service = $this->getServiceManager()->get(EntryPointService::SERVICE_ID);
            $service->registerEntryPoint(new BackOfficeEntrypoint());
            $this->getServiceManager()->register(EntryPointService::SERVICE_ID, $service);
            $this->setVersion('2.8.1');
        }

        // semantic versioning
        $this->skip('2.8.1','2.9');

        // remove id properties
        if ($this->isVersion('2.9')) {
            $rdf = ModelManager::getModel()->getRdfInterface();
            foreach ($rdf as $triple) {
                if ($triple->predicate == 'id') {
                    $rdf->remove($triple);
                }
            }

            $this->setVersion('2.9.1');
        }

        // tao object split
        if ($this->isVersion('2.9.1')) {
            OntologyUpdater::syncModels();
            $this->setVersion('2.10.0');
        }

        // widget definitions
        if ($this->isVersion('2.10.0')) {
            OntologyUpdater::syncModels();
            $this->setVersion('2.10.1');
        }

        // add login form config
        if ($this->isVersion('2.10.1')) {
            $loginFormSettings = array(
                'elements' => array()
            );

            $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $ext->setConfig('loginForm', $loginFormSettings);

            $this->setVersion('2.10.2');
        }

        if ($this->isVersion('2.10.2')) {

            $s = DIRECTORY_SEPARATOR;
            ThemeRegistry::getRegistry()->createTarget('frontOffice', array(
                'css' => 'tao'.$s.'views'.$s.'css'.$s.'tao-3.css',
                'templates' => array(
                    'header-logo' => 'taoDelivery'.$s.'views'.$s.'templates'.$s.'DeliveryServer'.$s.'blocks'.$s.'header-logo.tpl',
                    'footer' => 'taoDelivery'.$s.'views'.$s.'templates'.$s.'DeliveryServer'.$s.'blocks'.$s.'footer.tpl'
                )
            ));
            ThemeRegistry::getRegistry()->createTarget('backOffice', array(
                'css' => 'tao'.$s.'views'.$s.'css'.$s.'tao-3.css',
                'templates' => array(
                    'header-logo' => 'tao'.$s.'views'.$s.'templates'.$s.'blocks'.$s.'header-logo.tpl',
                    'footer' => 'tao'.$s.'views'.$s.'templates'.$s.'blocks'.$s.'footer.tpl'
                )
            ));

            $this->setVersion('2.11.0');
        }

        if ($this->isVersion('2.11.0')) {
            $service = new \tao_models_classes_service_StateStorage(array('persistence' => 'serviceState'));
            $this->getServiceManager()->register('tao/stateStorage', $service);
            $this->setVersion('2.12.0');
        }

        $this->skip('2.12.0','2.13.0');

        // moved to 2.8.0
        $this->skip('2.13.0','2.13.1');

        // moved to 2.7.2
        $this->skip('2.13.1','2.13.2');

        if ($this->isVersion('2.13.2')) {

            //add the new customizable template "login-message" to backOffice target
            $themeService = new ThemeService();

            //test for overrides
            $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $oldConfig = $ext->getConfig('themes');
            $compatibilityConfig = array();
            foreach ($oldConfig['frontOffice']['available'] as $arr) {
                if ($arr['id'] == $oldConfig['frontOffice']['default']) {
                    $compatibilityConfig[Theme::CONTEXT_FRONTOFFICE] = $arr;
                }
            }
            foreach ($oldConfig['backOffice']['available'] as $arr) {
                if ($arr['id'] == $oldConfig['backOffice']['default']) {
                    $compatibilityConfig[Theme::CONTEXT_BACKOFFICE] = $arr;
                }
            }

            if (empty($compatibilityConfig)) {
                $themeService->setTheme(new DefaultTheme());
            } else {
                $themeService->setTheme(new CompatibilityTheme($compatibilityConfig));
            }

            unset($oldConfig['backOffice']);
            unset($oldConfig['frontOffice']);
            $ext->setConfig('themes', $oldConfig );

            $this->getServiceManager()->register(ThemeService::SERVICE_ID, $themeService);

            $this->setVersion('2.14.0');
        }

        $this->skip('2.14.0', '2.15.0');

        if ($this->isVersion('2.15.0')) {
            (new SimpleAccess())->revokeRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole',
                ['ext' => 'tao', 'mod' => 'AuthApi']));
            $this->setVersion('2.15.1');
        }

        $this->skip('2.15.1', '2.15.2');

        if ($this->isVersion('2.15.2')) {
            ClientLibConfigRegistry::getRegistry()->register(
                'util/locale', ['decimalSeparator' => '.', 'thousandsSeparator' => '']
            );

            $this->setVersion('2.15.3');
        }

        $this->skip('2.15.3','2.16.0');

        if ($this->isVersion('2.16.0')) {
            try {
                $this->getServiceManager()->get(RequiredActionService::CONFIG_ID);
                // all good, already configured
            } catch (ServiceNotFoundException $error) {
                $requiredActionService = new RequiredActionService();
                $this->getServiceManager()->register(RequiredActionService::CONFIG_ID, $requiredActionService);
            }

            OntologyUpdater::syncModels();

            $this->setVersion('2.17.0');
        }

        if ($this->isBetween('2.17.0','2.17.4')) {
            ClientLibConfigRegistry::getRegistry()->register(
                'util/locale', ['decimalSeparator' => '.', 'thousandsSeparator' => '']
            );
            $this->setVersion('2.17.4');
        }

        // skiped registering of func ACL proxy as done before 2.7.7
        $this->skip('2.17.4', '2.18.2');

        if ($this->isVersion('2.18.2')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $config = $extension->getConfig('login');
            if (!is_array($config)) {
                $config = [];
            }
            if (!array_key_exists('disableAutocomplete', $config)) {
                $config['disableAutocomplete'] = false;
            }
            $extension->setConfig('login', $config);

            $this->setVersion('2.19.0');
        }

        $this->skip('2.19.0', '2.21.0');

        if ($this->isVersion('2.21.0')) {
            $config = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConfig('ServiceFileStorage');
            $service = new \tao_models_classes_service_FileStorage($config);
            $this->getServiceManager()->register(\tao_models_classes_service_FileStorage::SERVICE_ID, $service);
            $this->setVersion('2.22.0');
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

                $this->getServiceManager()->register(UpdateLogger::SERVICE_ID, new UpdateLogger(array(UpdateLogger::OPTION_FILESYSTEM => 'log')));
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
            $service->setRoute('default', array(
                'ext'        => 'tao',
                'controller' => 'Main',
                'action'     => 'index',
                ));
            $service->setRoute('login', array(
                'ext'        => 'tao',
                'controller' => 'Main',
                'action'     => 'login',
            ));
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
            $routeService->setRoute('logout',
                        [
                            'ext'        => 'tao',
                            'controller' => 'Main',
                            'action'     => 'logout',
                            'redirect'   => _url('entry', 'Main', 'tao'),
                        ]
                    );
            $this->getServiceManager()->register(\oat\tao\model\mvc\DefaultUrlService::SERVICE_ID , $routeService);

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

        if($this->isVersion('7.68.0')) {
            $notifInstaller = new InstallNotificationTable();
            $notifInstaller->setServiceLocator($this->getServiceManager());
            $notifInstaller->__invoke([]);
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole', ['ext'=>'tao','mod' => 'Notification']));
            $this->setVersion('7.69.0');
        }

        $this->skip('7.69.0', '7.69.6');

        if($this->isVersion('7.69.6')) {

            $queue = new NotificationServiceAggregator([
                'rds' =>
                    array(
                        'class'   => RdsNotification::class,
                        'options' => [
                            RdsNotification::OPTION_PERSISTENCE => RdsNotification::DEFAULT_PERSISTENCE,
                            'visibility'  => false,
                        ],
                    )
                ]
            );

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
                    \common_persistence_Manager::addPersistence($maintenancePersistence,  array('driver' => 'phpfile'));
                }

                $service = new Maintenance();
                $service->setOption(Maintenance::OPTION_PERSISTENCE, $maintenancePersistence);
                $this->getServiceManager()->register(Maintenance::SERVICE_ID, $service);

                $this->getServiceManager()->get(Maintenance::SERVICE_ID)->enablePlatform();
            }
            $this->setVersion('8.2.0');
        }

        $this->skip('8.2.0', '9.1.1');

        if($this->isVersion('9.1.1')){
            $this->getServiceManager()->register(TokenService::SERVICE_ID, new TokenService([
                'store' => new TokenStoreSession(),
                'poolSize' => 10,
                'timeLimit' => 0
            ]));
            $this->setVersion('9.2.0');
        }

        $this->skip('9.2.0', '10.10.0');

        if ($this->isVersion('10.10.0')) {
            $this->getServiceManager()->register(ArgumentService::SERVICE_ID, new ArgumentService(array(
                'arguments' => array(
                    new Group(array(new Debug(), new Info(), new Notice(), new Error(),))
                )
            )));
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
                'util/locale', ['dateTimeFormat' => 'DD/MM/YYYY HH:mm:ss']
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

            $urlService->setRoute('logout' , $route);
            $this->getServiceManager()->register(DefaultUrlService::SERVICE_ID , $urlService);
            $this->setVersion('10.20.0');
        }

        if ($this->isVersion('10.20.0')) {
            $this->runExtensionScript(UpdateRequiredActionUrl::class);
            $this->setVersion('10.21.0');
        }

        $this->skip('10.21.0', '10.24.1');

        if($this->isVersion('10.24.1')){
            $this->runExtensionScript(AddArchiveService::class);

            $this->setVersion('10.25.0');
        }

        $this->skip('10.25.0', '10.27.0');

        if($this->isVersion('10.27.0')) {
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole', ['ext'=>'tao','mod' => 'TaskQueueData']));
            $this->setVersion('10.28.0');
        }

        $this->skip('10.28.0', '10.28.1');

        if($this->isVersion('10.28.1')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $config = $extension->getConfig('login');

            if (!array_key_exists('block_iframe_usage', $config)) {
                $config['block_iframe_usage'] = false;
            }
            $extension->setConfig('login', $config);

            $this->setVersion('10.29.0');
        }

        $this->skip('10.29.0', '12.2.1');

        if($this->isVersion('12.2.1')) {
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
            $moduleService = ModuleAccessService::singleton();
            $moduleService->remove('http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',
                'http://www.tao.lu/Ontologies/taoFuncACL.rdf#m_tao_ExtensionsManager');

            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',      array('ext'=>'tao','mod' => 'ExtensionsManager')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Api')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Breadcrumbs')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Export')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'File')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Import')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Lock')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Main')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'PasswordRecovery')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Permission')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'PropertiesAuthoring')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'QueueAction')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'RestResource')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'RestUser')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Roles')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'TaskQueue')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'Users')));
            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',    array('ext'=>'tao','mod' => 'WebService')));

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
                    OauthService::OPTION_DATASTORE => new DataStore([
                        DataStore::OPTION_NONCE_STORE => new NoNonce()
                    ])
                ]));
            }
            $this->setVersion('14.21.0');
        }
        $this->skip('14.21.0', '14.23.3');

        if($this->isVersion('14.23.3')){

            AclProxy::applyRule(new AccessRule('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole', ['ext'=>'tao','mod' => 'RestClass']));

            $this->getServiceManager()->register(ResourceService::SERVICE_ID, new ResourceService());
            $this->getServiceManager()->register(ListResourceLookup::SERVICE_ID, new ListResourceLookup());
            $this->getServiceManager()->register(TreeResourceLookup::SERVICE_ID, new TreeResourceLookup());

            $this->setVersion('15.0.0');
        }

        $this->skip('15.0.0', '15.4.0');

        if ($this->isVersion('15.4.0')) {
            $setClientLoggerConfig = new SetClientLoggerConfig();
            $setClientLoggerConfig([]);
            AclProxy::applyRule(new AccessRule('grant', TaoRoles::BASE_USER, ['ext'=>'tao', 'mod' => 'Log', 'act' => 'log']));
            $this->setVersion('15.5.0');
        }

        $this->skip('15.5.0', '15.6.1');
    }

    private function migrateFsAccess() {
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $config = $tao->getConfig('filesystemAccess');
        if (is_array($config)) {
            foreach ($config as $id => $string) {
                list($class, $id, $fsUri, $jsconfig) = explode(' ', $string, 4);
                $config = json_decode($jsconfig, true);
                $options = array(
                    TokenWebSource::OPTION_ID => $id,
                    TokenWebSource::OPTION_FILESYSTEM_ID => $fsUri,
                );
                switch ($class) {
                	case 'tao_models_classes_fsAccess_TokenAccessProvider' :
                	    /** @var Directory $dir */
                        $dir = $this->getServiceManager()->get(ResourceFileSerializer::SERVICE_ID)->unserializeDirectory($fsUri);
                        // maybe it's a dirty way but it's quicker. too much modification would have been required in ItemUpdater
                        $adapter = $dir->getFileSystem()->getAdapter();
                        if (!$adapter instanceof Local) {
                            throw new \Exception(__CLASS__.' can only handle local files');
                        }

                        $options[TokenWebSource::OPTION_PATH] = $adapter->getPathPrefix();
                	    $options[TokenWebSource::OPTION_SECRET] = $config['secret'];
                	    $options[TokenWebSource::OPTION_TTL] = (int) ini_get('session.gc_maxlifetime');
                	    $websource = new TokenWebSource($options);
                	    break;
                	case 'tao_models_classes_fsAccess_ActionAccessProvider' :
                	    $websource = new ActionWebSource($options);
                	    break;
                	case 'tao_models_classes_fsAccess_DirectAccessProvider' :
                	    $options[DirectWebSource::OPTION_URL] = $config['accessUrl'];
                	    $websource = new DirectWebSource($options);
                	    break;
                	default:
                	    throw new common_Exception('unknown implementation '.$class);
                }
                WebsourceManager::singleton()->addWebsource($websource);
            }
        } else {
            throw new common_Exception('Error reading former filesystem access configuration');
        }
        return true;
    }
}
