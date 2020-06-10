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
 *               2013-2017 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\tao\helpers\InstallHelper;
use oat\oatbox\install\Installer;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\OperatedByService;
use oat\generis\persistence\sql\DbCreator;
use oat\generis\persistence\sql\SetupDb;
use oat\generis\persistence\PersistenceManager;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\generis\model\GenerisRdf;
use oat\tao\model\user\TaoRoles;
use oat\oatbox\service\ServiceNotFoundException;

/**
 *
 *
 * Installation main class
 *
 * @access public
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @package tao
 */

class tao_install_Installator
{
    // Adding container and logger.
    use \oat\oatbox\log\ContainerLoggerTrait;

    /**
     * Installator related dependencies will be reached under this offset.
     */
    const CONTAINER_INDEX = 'taoInstallInstallator';

    protected $options = [];

    private $log = [];

    private $escapedChecks = [];

    private $oatBoxInstall = null;

    public function __construct($options)
    {
        // Using the container if it's necessary with automatic dependency returning.
        $options = $this->initContainer($options, static::CONTAINER_INDEX);

        if (!isset($options['root_path'])) {
            throw new tao_install_utils_Exception("root_path option must be defined to perform installation.");
        }
        if (!isset($options['install_path'])) {
            throw new tao_install_utils_Exception("install_path option must be defined to perform installation.");
        }

        $this->options = $options;

        $this->options['root_path'] = rtrim($this->options['root_path'], '/\\') . DIRECTORY_SEPARATOR;
        $this->options['install_path'] = rtrim($this->options['install_path'], '/\\') . DIRECTORY_SEPARATOR;

        $this->oatBoxInstall = new Installer();
    }

    /**
     * Run the TAO install from the given data
     * @throws tao_install_utils_Exception
     * @param $installData array data coming from the install form
     */
    public function install(array $installData)
    {
        try {
            /**
             * It's a quick hack for solving reinstall issue.
             * Should be a better option.
             */
            @unlink($this->options['root_path'] . 'config/generis.conf.php');

            /*
             * 0 - Check input parameters.
             */
            $this->log('i', "Checking install data");
            self::checkInstallData($installData);
            
            $this->log('i', "Starting TAO install");
            
            // Sanitize $installData if needed.
            if (!preg_match("/\/$/", $installData['module_url'])) {
                $installData['module_url'] .= '/';
            }

            if (isset($installData['extensions'])) {
                $extensionIDs = is_array($installData['extensions'])
                 ? $installData['extensions']
                 : explode(',', $installData['extensions']);
            } else {
                $extensionIDs = ['taoCe'];
            }

            $this->log('d', 'Extensions to be installed: ' . var_export($extensionIDs, true));

            $installData['file_path'] = rtrim($installData['file_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    
            /*
             *  1 - Check configuration with checks described in the manifest.
             */
            $configChecker = tao_install_utils_ChecksHelper::getConfigChecker($extensionIDs);
            
            // Silence checks to have to be escaped.
            foreach ($configChecker->getComponents() as $c) {
                if (method_exists($c, 'getName') && in_array($c->getName(), $this->getEscapedChecks())) {
                    $configChecker->silent($c);
                }
            }
            
            $reports = $configChecker->check();
            foreach ($reports as $r) {
                $msg = $r->getMessage();
                $component = $r->getComponent();
                $this->log('i', $msg);

                if ($r->getStatus() !== common_configuration_Report::VALID && !$component->isOptional()) {
                    throw new tao_install_utils_Exception($msg);
                }
            }
            
            /*
             *  X - Setup Oatbox
             */
            
            $this->log('d', 'Removing old config');
            $consistentOptions = array_merge($installData, $this->options);
            $consistentOptions['config_path'] = $this->getConfigPath();
            $this->oatBoxInstall->setOptions($consistentOptions);
            $this->oatBoxInstall->install();
            $this->log('d', 'Oatbox was installed!');

            ServiceManager::setServiceManager($this->getServiceManager());

            /*
             *  2 - Setup RDS persistence
             */
            if ($this->getServiceManager()->has(PersistenceManager::SERVICE_ID)) {
                $persistenceManager = $this->getServiceManager()->get(PersistenceManager::SERVICE_ID);
            } else {
                $this->log('i', "Spawning new PersistenceManager");
                $persistenceManager = new PersistenceManager();
            }
            if (!$persistenceManager->hasPersistence('default')) {
                $this->log('i', "Register default Persistence");
                $dbalConfigCreator = new tao_install_utils_DbalConfigCreator();
                $persistenceManager->registerPersistence('default', $dbalConfigCreator->createDbalConfig($installData));
                $this->getServiceManager()->register(PersistenceManager::SERVICE_ID, $persistenceManager);
            }

            $dbCreator = new SetupDb();
            $dbCreator->setLogger($this->logger);
            $dbCreator->setupDatabase($persistenceManager->getPersistenceById('default'));
            
            /*
             *  4 - Create the generis config files
             */
            
            $this->log('d', 'Writing generis config');
            $generisConfigWriter = new tao_install_utils_ConfigWriter(
                $this->options['root_path'] . 'generis/config/sample/generis.conf.php',
                $this->getGenerisConfig()
            );

            $session_name = (isset($installData['session_name'])) ? $installData['session_name'] : self::generateSessionName();
            $generisConfigWriter->createConfig();
            $constants = [
                'LOCAL_NAMESPACE'           => $installData['module_namespace'],
                'GENERIS_INSTANCE_NAME'     => $installData['instance_name'],
                'GENERIS_SESSION_NAME'      => $session_name,
                'ROOT_PATH'                 => $this->options['root_path'],
                'FILES_PATH'                => $installData['file_path'],
                'ROOT_URL'                  => $installData['module_url'],
                'DEFAULT_LANG'              => $installData['module_lang'],
                'DEBUG_MODE'                => ($installData['module_mode'] == 'debug') ? true : false,
                'TIME_ZONE'                 => $installData['timezone']
            ];

            $constants['DEFAULT_ANONYMOUS_INTERFACE_LANG'] = (isset($installData['anonymous_lang'])) ? $installData['anonymous_lang'] : $installData['module_lang'];


            $generisConfigWriter->writeConstants($constants);
            $this->log('d', 'The following constants were written in generis config:' . PHP_EOL . var_export($constants, true));

            /*
             * 4b - Prepare the file/cache folder (FILES_PATH) not yet defined)
             * @todo solve this more elegantly
             */
            $file_path = $installData['file_path'];
            if (is_dir($file_path)) {
                $this->log('i', 'Data from previous install found and will be removed');
                if (!helpers_File::emptyDirectory($file_path, true)) {
                    throw new common_exception_Error('Unable to empty ' . $file_path . ' folder.');
                }
            } else {
                if (mkdir($file_path, 0700, true)) {
                    $this->log('d', $file_path . ' directory was created!');
                } else {
                    throw new Exception($file_path . ' directory creation was failed!');
                }
            }
            $cachePath = $file_path . 'generis' . DIRECTORY_SEPARATOR . 'cache';
            if (mkdir($cachePath, 0700, true)) {
                $this->log('d', $cachePath . ' directory was created!');
            } else {
                throw new Exception($cachePath . ' directory creation was failed!');
            }

            foreach ((array)$installData['extra_persistences'] as $k => $persistence) {
                $persistenceManager->registerPersistence($k, $persistence);
            }

            /*
             * 5 - Run the extensions bootstrap
             */
            $this->log('d', 'Running the extensions bootstrap');
            common_Config::load($this->getGenerisConfig());
            
            /*
             * 5b - Create cache persistence
            */
            $this->log('d', 'Creating cache persistence..');
            $persistenceManager->registerPersistence('cache', [
                'driver' => 'phpfile'
            ]);
            $persistenceManager->getPersistenceById('cache')->purge();
            $this->getServiceManager()->register(PersistenceManager::SERVICE_ID, $persistenceManager);

            /*
             * 6 - Finish Generis Install
             */

            $this->log('d', 'Finishing generis install..');
            $generis = common_ext_ExtensionsManager::singleton()->getExtensionById('generis');

            $generisInstaller = new common_ext_GenerisInstaller($generis, true);
            $generisInstaller->initContainer($this->getContainer());
            $generisInstaller->install();

            /*
             * 7 - Add languages
             */
            $this->log('d', 'Adding languages..');
            $ontology = $this->getServiceManager()->get(Ontology::SERVICE_ID);
            $langModel = \tao_models_classes_LanguageService::singleton()->getLanguageDefinition();
            $rdfModel = $ontology->getRdfInterface();
            foreach ($langModel as $triple) {
                $rdfModel->add($triple);
            }

            /*
             * 8 - Install the extensions
             */
            InstallHelper::initContainer($this->container);
            $installed = InstallHelper::installRecursively($extensionIDs, $installData);
            $this->log('ext', $installed);

            /*
             *  8b - Generates client side translation bundles (depends on extension install)
             */
            $this->log('i', 'Generates client side translation bundles');
            
            tao_models_classes_LanguageService::singleton()->generateAll();

            /*
             *  9 - Insert Super User
             */
            $this->log('i', 'Spawning SuperUser ' . $installData['user_login']);

            $userClass = $ontology->getClass(TaoOntology::CLASS_URI_TAO_USER);
            $userid = $installData['module_namespace'] . TaoOntology::DEFAULT_USER_URI_SUFFIX;
            $userpwd = core_kernel_users_Service::getPasswordHash()->encrypt($installData['user_pass1']);
            $userLang = 'http://www.tao.lu/Ontologies/TAO.rdf#Lang' . $installData['module_lang'];

            $superUser = $userClass->createInstance('Super User', 'super user created during the TAO installation', $userid);
            $superUser->setPropertiesValues([
                GenerisRdf::PROPERTY_USER_ROLES => [
                    TaoRoles::GLOBAL_MANAGER,
                    TaoRoles::SYSTEM_ADMINISTRATOR
                ],
                TaoOntology::PROPERTY_USER_FIRST_TIME => GenerisRdf::GENERIS_TRUE,
                GenerisRdf::PROPERTY_USER_LOGIN => $installData['user_login'],
                GenerisRdf::PROPERTY_USER_PASSWORD => $userpwd,
                GenerisRdf::PROPERTY_USER_LASTNAME => $installData['user_lastname'],
                GenerisRdf::PROPERTY_USER_FIRSTNAME => $installData['user_firstname'],
                GenerisRdf::PROPERTY_USER_MAIL => $installData['user_email'],
                GenerisRdf::PROPERTY_USER_DEFLG => $userLang,
                GenerisRdf::PROPERTY_USER_UILG => $userLang,
                GenerisRdf::PROPERTY_USER_TIMEZONE => TIME_ZONE
            ]);

            /*
             *  10 - Secure the install for production mode
             */
            if ($installData['module_mode'] == 'production') {
                $extensions = common_ext_ExtensionsManager::singleton()->getInstalledExtensions();
                $this->log('i', 'Securing tao for production');
                
                // 11.0 Protect TAO dist
                $shield = new tao_install_utils_Shield(array_keys($extensions));
                $shield->disableRewritePattern(["!/test/", "!/doc/"]);
                                $shield->denyAccessTo([
                                    'views/sass',
                                    'views/js/test',
                                    'views/build'
                                ]);
                $shield->protectInstall();
            }

            /*
             *  11 - Create the version file
             */
            $this->log('d', 'Creating TAO version file');
            file_put_contents($installData['file_path'] . 'version', TAO_VERSION);
            
            /*
             * 12 - Register Information about organization operating the system
             */
            $this->log('t', 'Registering information about the organization operating the system');
            $operatedByService = $this->getServiceManager()->get(OperatedByService::SERVICE_ID);
            
            if (!empty($installData['operated_by_name'])) {
                $operatedByService->setName($installData['operated_by_name']);
            }
            
            if (!empty($installData['operated_by_email'])) {
                $operatedByService->setEmail($installData['operated_by_email']);
            }
            
            $this->getServiceManager()->register(OperatedByService::SERVICE_ID, $operatedByService);
        } catch (Exception $e) {
            if ($this->retryInstallation($e)) {
                return;
            }

            // In any case, we transmit a single exception type (at the moment)
            // for a clearer API for client code.
            $this->log('e', 'Error Occurs : ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            throw new tao_install_utils_Exception($e->getMessage(), 0, $e);
        }
    }

    public function getServiceManager()
    {
        return $this->oatBoxInstall->setupServiceManager($this->getConfigPath());
    }

    private function retryInstallation($exception)
    {
        $returnValue = false;
        $err = $exception->getMessage();

        if (strpos($err, 'cannot construct the resource because the uri cannot be empty') === 0 && $this->isWindows()) {
            /*
             * a known issue
             * @see http://forge.taotesting.com/issues/3014
             * this issue can only be fixed by an administrator
             * changing the thread_stack system variable in my.ini as following:
             * '256K' on 64bit windows
             * '192K' on 32bit windows
             */

            $this->log('e', 'Error Occurs : ' . $err . PHP_EOL . $exception->getTraceAsString());
            throw new tao_install_utils_Exception("Error in mysql system variable 'thread_stack':<br>It is required to change its value in my.ini as following<br>'192K' on 32bit windows<br>'256K' on 64bit windows.<br><br>Note that such configuration changes will only take effect after server restart.<br><br>", 0, $exception);
        }

        if (!$returnValue) {
            return false;
        }

        // it is a known issue, go ahead to retry with the issue fixer
        $this->install($this->config);
        return true;
    }

    private function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }

    /**
     * Generate an alphanum token to be used as a PHP session name.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function generateSessionName()
    {
        return 'tao_' . helpers_Random::generateString(8);
    }

    /**
     * Check the install data information such as
     * - instance name
     * - database driver
     * - ...
     *
     * If a parameter of the $installData is not valid regarding the install
     * business rules, an MalformedInstall
     *
     * @param array $installData
     */
    public static function checkInstallData(array $installData)
    {
        // instance name
        if (empty($installData['instance_name'])) {
            $msg = "Missing install parameter 'instance_name'.";
            throw new tao_install_utils_MalformedParameterException($msg);
        } elseif (!is_string($installData['instance_name'])) {
            $msg = "Malformed install parameter 'instance_name'. It must be a string.";
            throw new tao_install_utils_MalformedParameterException($msg);
        } elseif (1 === preg_match('/\s/u', $installData['instance_name'])) {
            $msg = "Malformed install parameter 'instance_name'. It cannot contain spacing characters (tab, backspace).";
            throw new tao_install_utils_MalformedParameterException($msg);
        }
    }
    
    /**
     * Tell the Installator instance to not take into account
     * a Configuration Check with ID = $id.
     *
     * @param string $id The identifier of the check to escape.
     */
    public function escapeCheck($id)
    {
        $checks = $this->getEscapedChecks();
        array_push($checks, $id);
        $checks = array_unique($checks);
        $this->setEscapedChecks($checks);
    }
    
    /**
     * Obtain an array of Configuration Check IDs to be escaped by
     * the Installator.
     *
     * @return array
     */
    public function getEscapedChecks()
    {
        return $this->escapedChecks;
    }
    
    /**
     * Set the array of Configuration Check IDs to be escaped by
     * the Installator.
     *
     * @param array $escapedChecks An array of strings.
     * @return void
     */
    public function setEscapedChecks(array $escapedChecks)
    {
        $this->escapedChecks = $escapedChecks;
    }
    
    /**
     * Informs you if a given Configuration Check ID corresponds
     * to a Check that has to be escaped.
     */
    public function isEscapedCheck($id)
    {
        return in_array($id, $this->getEscapedChecks());
    }
    
    /**
     * Log message and add it to $this->log array;
     * @see common_Logger class
     * @param string $logLevel
     * <ul>
     *   <li>'w' - warning</li>
     *   <li>'t' - trace</li>
     *   <li>'d' - debug</li>
     *   <li>'i' - info</li>
     *   <li>'e' - error</li>
     *   <li>'f' - fatal</li>
     *   <li>'ext' - installed extensions</li>
     * </ul>
     * @param string $message
     * @param array $tags
     */
    public function log($logLevel, $message, $tags = [])
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }
        if ($this->getLogger() instanceof \Psr\Log\LoggerInterface) {
            if ($logLevel === 'ext') {
                $this->logNotice('Installed extensions: ' . implode(', ', $message));
            } else {
                $this->getLogger()->log(
                    common_log_Logger2Psr::getPsrLevelFromCommon($logLevel),
                    $message
                );
            }
        }
        if (method_exists('common_Logger', $logLevel)) {
            call_user_func('common_Logger::' . $logLevel, $message, $tags);
        }
        if (is_array($message)) {
            $this->log[$logLevel] = (isset($this->log[$logLevel])) ? array_merge($this->log[$logLevel], $message) : $message;
        } else {
            $this->log[$logLevel][] = $message;
        }
    }

    /**
     * Get array of log messages
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Get the config file platform e.q. generis.conf.php
     *
     * @return string
     */
    protected function getGenerisConfig()
    {
         return $this->getConfigPath() . 'generis.conf.php';
    }

    /**
     * Get the config path for installation
     * If options have installation_config_path, it's taken otherwise it's root_path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        if (isset($this->options['installation_config_path'])) {
            return $this->options['installation_config_path'];
        } else {
            return $this->options['root_path'] . 'config' . DIRECTORY_SEPARATOR;
        }
    }
}
