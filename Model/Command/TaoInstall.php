<?php

namespace oat\tao\Model\Command;

use common_Config;
use common_configuration_Report;
use common_exception_Error;
use common_ext_ExtensionsManager;
use common_ext_GenerisInstaller;
use common_persistence_KeyValuePersistence;
use common_persistence_Manager;
use core_kernel_users_Service;
use Exception;
use FileNotFoundException;
use helpers_File;
use helpers_Random;
use oat\generis\Model\ConsoleCommand;
use oat\oatbox\install\Installer;
use oat\oatbox\PimpleContainerTrait;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\InstallHelper;
use oat\tao\model\OperatedByService;
use Pimple\Container;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use tao_install_utils_ChecksHelper;
use tao_install_utils_ConfigWriter;
use tao_install_utils_DbalDbCreator;
use tao_install_utils_Exception;
use tao_install_utils_ModelCreator;
use tao_install_utils_Shield;
use tao_models_classes_LanguageService;

/**
 * Command to install the tao instance
 */
class TaoInstall extends ConsoleCommand
{

    use PimpleContainerTrait;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var Installer
     */
    private $oatBoxInstall;

    /**
     * @var tao_install_utils_DbalDbCreator
     */
    private $dbCreator;

    /**
     * @var array
     */
    private $dbConfiguration;

    /**
     * @var bool
     */
    protected $loadConfig = false;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tao:install')
             ->setDescription('Updates the TAO instance')
             ->addArguments()
             ->addOptions();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setContainer(new Container());
        parent::initialize($input, $output);
    }


    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->title('Starting TAO Installation');

        try{
            $rootDir = dir(__DIR__ . '/../../../');
            $this->rootPath = realpath($rootDir->path) . DIRECTORY_SEPARATOR;

            if ($this->install() === false) {
                $this->error('An error occurred during the installation process.');
                return;
            }
        } catch (Exception $e){
            $this->error('A fatal error has occurred during installation: ' . $e->getMessage());
        }

        $this->success('TAO has been successfully installed!');

    }

    private function install()
    {
        $progressBar = $this->createProgressBar(19);
        $progressBar->start();

        /**
         * Quick hack for solving reinstall issue (taken from original install script).
         * Should be a better option.
         */
        @unlink($this->rootPath . 'config/generis.conf.php');
        $progressBar->advance();
        $this->newLine(2);

        if ($this->verifyArguments() === false) {
            return false;
        }
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Checking configuration');
        $this->sanitizeArguments();
        $this->checkConfig();
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Install OAT Box');
        $this->installOatBox();
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Database');
        $this->initDatabaseCreator();
        $this->writeln('DbCreator retrieved');
        $this->createDatabase();

        // reset db name for mysql
        $dbDriver = $this->input->getArgument('db-driver');
        if ($dbDriver === 'pdo_mysql'){
            $this->dbConfiguration['dbname'] = $this->input->getArgument('db-name');
        }

        $this->dbCreator->initTaoDataBase();
        $this->success('TAO database initialized');
        $progressBar->advance();
        $this->newLine(2);

        $storedProcedureFile = __DIR__ . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'tao_stored_procedures_' . str_replace('pdo_', '', $dbDriver) . '.sql';
        if (file_exists($storedProcedureFile) && is_readable($storedProcedureFile)) {
            $this->writeln('Installing stored procedures for ' . $dbDriver . ' from file: ' . $storedProcedureFile);
            $this->dbCreator->loadProc($storedProcedureFile);
        } else {
            throw new Exception('Could not find storefile: ' . $storedProcedureFile);
        }
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Generis configuration');
        $this->setGenerisConfiguration();
        $progressBar->advance();
        $this->newLine(2);

        $this->section('File structure');
        $this->setFileStructure();
        $progressBar->advance();
        $this->newLine(2);

//        foreach ((array)$installData['extra_persistences'] as $k => $persistence) {
//            common_persistence_Manager::addPersistence($k, $persistence);
//        }

        $this->section('Bootstrapping extensions');
        common_Config::load($this->getGenerisConfigPath());
        $this->writeln('Extensions bootstrapped');
        $progressBar->advance();
        $this->newLine(2);


        $this->section('Create persistence');
        $this->createPersistence();
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Generis user creation');
        $modelCreator = new tao_install_utils_ModelCreator(LOCAL_NAMESPACE);
        $modelCreator->insertGenerisUser(helpers_Random::generateString(8));
        $this->writeln('Created generis user');
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Language setup');
        $models = $modelCreator->getLanguageModels();
        foreach ($models as $ns => $modelFiles){
            foreach ($modelFiles as $file){
                $this->writeln("Inserting language description model '" . $file . "'");
                $modelCreator->insertLocalModel($file);
            }
        }
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Finalize generis install');
        $generis = common_ext_ExtensionsManager::singleton()->getExtensionById('generis');

        $generisInstaller = new common_ext_GenerisInstaller($generis, true);
        $generisInstaller->install();
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Installing extensions');
        $installed = InstallHelper::installRecursively($this->input->getArgument('extensions'), $this->input->getArguments());
        $this->success('The following extensions have been installed:');
        $this->listing($installed);
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Translation bundle generation');
        tao_models_classes_LanguageService::singleton()->generateAll();
        $progressBar->advance();
        $this->newLine(2);

        $this->section('SuperUser creation');
        $modelCreator->insertSuperUser(array(
            'login'			=> $this->input->getArgument('admin-user'),
            'password'		=> core_kernel_users_Service::getPasswordHash()->encrypt($this->input->getArgument('admin-pass')),
            'userLastName'	=> 'user',
            'userFirstName'	=> $this->input->getArgument('operated-by-name') ?: 'Super',
            'userMail'		=> $this->input->getArgument('operated-by-email') ?: '',
            'userDefLg'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang' . $this->input->getArgument('language'),
            'userUILg'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang' . $this->input->getArgument('language'),
            'userTimezone'  => TIME_ZONE
        ));
        $progressBar->advance();
        $this->newLine(2);

        if ($this->input->getArgument('deployment-mode') === 'production') {
            $this->section('Securing installation');
            $this->secureInstallation();
        }
        $progressBar->advance();
        $this->newLine(2);

        $this->section('Finalizing setup');
        file_put_contents($this->input->getArgument('data-dir') . 'version', TAO_VERSION);
        $this->success('TAO version file created');
        $progressBar->advance();
        $this->newLine(2);

        $operatedByService = $this->getServiceManager()->get(OperatedByService::SERVICE_ID);

        if (!empty($installData['operated_by_name'])) {
            $operatedByService->setName($this->input->getArgument('operated-by-name') ?: '');
        }

        if (!empty($installData['operated_by_email'])) {
            $operatedByService->setEmail($this->input->getArgument('operated-by-email') ?: '');
        }

        $this->getServiceManager()->register(OperatedByService::SERVICE_ID, $operatedByService);
        $this->success('Information about the organization operating the system registered');
        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Check if the given arguments are valid
     *
     * @return bool
     */
    private function verifyArguments()
    {
        $instanceName = $this->input->getArgument('instance-name');
        if (preg_match('/\s/u', $instanceName) === 1) {
            $this->error('Instance name cannot contain any whitespace characters.');
            return false;
        }

        $allowedCharacters = ['-', '_'];
        if (ctype_alnum(str_replace($allowedCharacters, '', $instanceName)) === false) {
            $this->error('Instance name can only contain alphanumeric characters, dashes and underscores.');
            return false;
        }

        return true;
    }

    private function sanitizeArguments()
    {
        $moduleUrl = $this->input->getArgument('url');
        if(!preg_match("/\/$/", $moduleUrl)){
            $this->input->setArgument('url', $moduleUrl . '/');
        }

        $namespace = $this->input->getArgument('module-namespace');
        if (null === $namespace) {
            $this->input->setArgument('module-namespace', $this->input->getArgument('url') . 'first.rdf');
        }

        $extensionList = $this->input->getArgument('extensions');
        if ($extensionList !== null) {
            $extensionList = is_array($extensionList) ? $extensionList : explode(',',$extensionList);
        } else {
            $extensionList = ['taoCe'];
        }

        $this->input->setArgument('extensions', $extensionList);

        $this->note('Following extensions will be installed');
        $this->listing($extensionList);

        $dataDir = $this->input->getArgument('data-dir');
        $this->input->setArgument('data-dir', rtrim($dataDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        $configDir = $this->input->getArgument('config-dir');
        $this->input->setArgument('config-dir', $this->rootPath . rtrim($configDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
    }

    /**
     * Add the arguments for the install command.
     *
     * @return self
     */
    private function addArguments()
    {
        $this->addArgument(
            'db-driver',
            InputArgument::REQUIRED,
            'Available options are: pdo_pgsql, pdo_mysql, pdo_sqlsrv, pdo_oci.'
        )->addArgument(
            'db-host',
            InputArgument::REQUIRED,
            'The database host.'
        )->addArgument(
            'db-name',
            InputArgument::REQUIRED,
            'The name of the database.'
        )->addArgument(
            'db-pass',
            InputArgument::REQUIRED,
            'Password used to access the database.'
        )->addArgument(
            'db-user',
            InputArgument::REQUIRED,
            'The user to access the database.'
        )->addArgument(
            'url',
            InputArgument::REQUIRED,
            'The url at which you can access your application.'
        )->addArgument(
            'admin-user',
            InputArgument::REQUIRED,
            'The username for the administrator role.'
        )->addArgument(
            'admin-pass',
            InputArgument::REQUIRED,
            'The password for the administrator role.'
        )->addArgument(
            'instance-name',
            InputArgument::REQUIRED,
            'The name for the instance being installed'
        )->addArgument(
            'extensions',
            InputArgument::OPTIONAL,
            'A comma separated list of extension to install (eg. taoCe, taoDevTools)'
        )->addArgument(
            'data-dir',
            InputArgument::OPTIONAL,
            'The location of the data directory (relative to the root of your project).',
            'data' . DIRECTORY_SEPARATOR
        )->addArgument(
            'config-dir',
            InputArgument::OPTIONAL,
            'The location of the config directory (relative to the root of your project).',
            'config' . DIRECTORY_SEPARATOR
        )->addArgument(
            'timezone',
            InputArgument::OPTIONAL,
            'The timezone that should be used for this installation (eg. Europe/Amsterdam).',
            date_default_timezone_get()
        )->addArgument(
            'language',
            InputArgument::OPTIONAL,
            'The language that should be used for this installation (eg. en-US).',
            'en-US'
        )->addArgument(
            'anonymous-lang',
            InputArgument::OPTIONAL,
            'The language that should be used for guest users (eg. en-US).',
            'en-US'
        )->addArgument(
            'deployment-mode',
            InputArgument::OPTIONAL,
            'Available options are debug and production.',
            'production'
        )->addArgument(
            'module-namespace',
            InputArgument::OPTIONAL,
            'This namespace will be used to identify the data stored by your module.'
        )->addArgument(
            'session-name',
            InputArgument::OPTIONAL,
            'This namespace will be used to identify the data stored by your module.'
        )->addArgument(
            'operated-by-name',
            InputArgument::OPTIONAL,
            'Name of the organization operating the system.'
        )->addArgument(
            'operated-by-email',
            InputArgument::OPTIONAL,
            'Email of the organization operating the system.'
        );

        return $this;
    }

    /**
     * Add the options for the install command.
     *
     * @return self
     */
    private function addOptions()
    {
        $this->addOption(
            'import-local',
            null,
            InputOption::VALUE_NONE,
            'States if the local.rdf files must be imported or not.'
        );

        return $this;
    }

    private function checkConfig()
    {
        $configChecker = tao_install_utils_ChecksHelper::getConfigChecker($this->input->getArgument('extensions'));

        // Silence checks to have to be escaped.
        foreach ($configChecker->getComponents() as $c){
//            if (method_exists($c, 'getName') && in_array($c->getName(), $this->getEscapedChecks())){
//                $configChecker->silent($c);
//            }
        }

        $reports = $configChecker->check();
        foreach ($reports as $r){
            $msg = $r->getMessage();
            $component = $r->getComponent();
            $this->writeln($msg);

            if ($r->getStatus() !== common_configuration_Report::VALID && !$component->isOptional()){
                throw new tao_install_utils_Exception($msg);
            }
        }
    }

    private function installOatBox()
    {
        $this->writeln('Removing old config');
        $this->oatBoxInstall = new Installer();
        $consistentOptions = $this->input->getArguments();
        $consistentOptions['root_path'] = $this->rootPath;
        $consistentOptions['file_path'] = $this->input->getArgument('data-dir');
        $consistentOptions['config_path'] = $this->input->getArgument('config-dir');
        $this->oatBoxInstall->setOptions($consistentOptions);
        $this->oatBoxInstall->install();
        $this->success('Oatbox was installed!');
    }

    private function initDatabaseCreator()
    {
        $this->writeln('Getting DbCreator');
        $dbDriver = $this->input->getArgument('db-driver');
        if ($dbDriver  === 'pdo_oci') {
            $this->input->setArgument('db-name', $this->input->getArgument('db-host'));
            $this->input->setArgument('db-host', '');
        }

        $dbHost = $this->input->getArgument('db-host');

        $this->dbConfiguration = [
            'driver' => $dbDriver,
            'host' => $dbHost,
            'dbname' => $this->input->getArgument('db-name'),
            'user' => $this->input->getArgument('db-user'),
            'password' => $this->input->getArgument('db-pass'),

        ];

        $hostParts = explode(':', $dbHost);
        if (count($hostParts) === 2) {
            $this->dbConfiguration['host'] = $hostParts[0];
            $this->dbConfiguration['port'] = $hostParts[1];
        }

        if ($dbDriver === 'pdo_mysql') {
            $this->dbConfiguration['dbname'] = '';
        }

        if ($dbDriver === 'pdo_oci') {
            $this->dbConfiguration['wrapperClass'] = 'Doctrine\DBAL\Portability\Connection';
            $this->dbConfiguration['portability'] = \Doctrine\DBAL\Portability\Connection::PORTABILITY_ALL;
            $this->dbConfiguration['fetch_case'] = PDO::CASE_LOWER;
        }

        $this->dbCreator = new tao_install_utils_DbalDbCreator($this->dbConfiguration);
    }

    private function createDatabase()
    {
        $dbName = $this->input->getArgument('db-name');
        if ($this->dbCreator->dbExists($dbName)) {
            $this->cleanupDatabase();
        } else {
            try {
                $this->dbCreator->createDatabase($dbName);
                $this->writeln('Created database ' . $dbName);
            } catch (Exception $e){
                $dbUser = $this->input->getArgument('db-user');
                throw new tao_install_utils_Exception(
                    'Unable to create the database, make sure that ' . $dbUser . ' is granted to create databases.
                    Otherwise create the database with your super user and give ' . $dbUser . ' the right to use it.'
                );
            }

            if ($this->input->getArgument('db-driver') === 'pdo_mysql'){
                $this->dbCreator->setDatabase($dbName);
            }
        }
    }

    private function cleanupDatabase()
    {
        $this->writeln('Existing database found. Cleaning the existing database...');
        $dbName = $this->input->getArgument('db-name');
        try {
            //If the target Sgbd is mysql select the database after creating it
            if ($this->input->getArgument('db-driver') === 'pdo_mysql'){
                $this->dbCreator->setDatabase($dbName);
            }
            $this->dbCreator->cleanDb($dbName);

        } catch (Exception $e){
            $this->warning('An error occurred while cleaning the db: ' . $e->getMessage());
            $this->caution('Trying to erase the whole db...');
            try {
                $this->dbCreator->destroyTaoDatabase();
            } catch (Exception $e){
                $this->error('Unable to clean the database');
                throw new tao_install_utils_Exception($e->getMessage());
            }
        }
        $this->writeln('Database cleaned up');
    }

    private function setGenerisConfiguration()
    {
        $this->writeln('Writing generis config');
        $generisConfigWriter = new tao_install_utils_ConfigWriter(
            $this->rootPath.'generis/config/sample/generis.conf.php',
            $this->getGenerisConfigPath()
        );

        $session_name = $this->input->getArgument('session-name') ?: $this->generateSessionName();
        $generisConfigWriter->createConfig();
        $constants = array(
            'LOCAL_NAMESPACE'			=> $this->input->getArgument('module-namespace'),
            'GENERIS_INSTANCE_NAME'		=> $this->input->getArgument('instance-name'),
            'GENERIS_SESSION_NAME'		=> $session_name,
            'ROOT_PATH'					=> $this->rootPath,
            'FILES_PATH'                => $this->input->getArgument('data-dir'),
            'ROOT_URL'					=> $this->input->getArgument('url'),
            'DEFAULT_LANG'				=> $this->input->getArgument('language'),
            'DEBUG_MODE'				=> $this->input->getArgument('deployment-mode') === 'debug',
            'TIME_ZONE'                 => $this->input->getArgument('timezone')
        );

        $constants['DEFAULT_ANONYMOUS_INTERFACE_LANG'] = $this->input->getArgument('anonymous-lang') ?: $this->input->getArgument('language');


        $generisConfigWriter->writeConstants($constants);
        $this->success('The following constants were written in generis config:');
        $this->listing($constants);
    }

    /**
     * Generate an alphanumeric token to be used as a PHP session name.
     *
     * @return string
     */
    private function generateSessionName(){
        return 'tao_' . helpers_Random::generateString(8);
    }


    private function setFileStructure()
    {
        $file_path = $this->input->getArgument('data-dir');
        if (is_dir($file_path)) {
            $this->note('Data from previous install found and will be removed');
            if (!helpers_File::emptyDirectory($file_path, true)) {
                throw new common_exception_Error('Unable to empty ' . $file_path . ' folder.');
            }
        } else {
            if (mkdir($file_path, 0700, true)) {
                $this->success($file_path . ' directory was created.');
            } else {
                throw new Exception($file_path . ' directory creation has failed.');
            }
        }
        $cachePath = $file_path . 'generis' . DIRECTORY_SEPARATOR . 'cache';
        if (mkdir($cachePath, 0700, true)) {
            $this->success($cachePath . ' directory was created!');
        } else {
            throw new Exception($cachePath . ' directory creation was failed!');
        }
    }

    /**
     * Get the config file platform e.q. generis.conf.php
     *
     * @return string
     */
    private function getGenerisConfigPath()
    {
        return $this->input->getArgument('config-dir') . 'generis.conf.php';
    }

    private function createPersistence()
    {
        common_persistence_Manager::addPersistence('cache', [
            'driver' => 'phpfile'
        ]);
        common_persistence_KeyValuePersistence::getPersistence('cache')->purge();
        $this->success('Created cache persistence');

        common_persistence_Manager::addPersistence('default', $this->dbConfiguration);
        $this->success('Created generis persistence');
    }

    private function secureInstallation()
    {
        $extensions = common_ext_ExtensionsManager::singleton()->getInstalledExtensions();
        $this->writeln('Securing tao for production');

        // 11.1 Remove Generis User
        $this->dbCreator->removeGenerisUser();

        // 11.2 Protect TAO dist
        $shield = new tao_install_utils_Shield(array_keys($extensions));
        $shield->disableRewritePattern(array("!/test/", "!/doc/"));
        $shield->denyAccessTo(array(
            'views/sass',
            'views/js/test',
            'views/build'
        ));
        $shield->protectInstall();
    }
}