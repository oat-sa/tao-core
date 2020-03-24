<?php

namespace oat\tao\scripts\tools;

use Doctrine\DBAL\Connection;
use oat\tao\scripts\tools\migrations\Configuration;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\extension\script\ScriptException;
use common_ext_Extension;
use oat\tao\scripts\tools\migrations\TaoFinder;
use common_report_Report as Report;
use Doctrine\Migrations\Exception\MigrationException;
use Doctrine\Migrations\Tools\Console\Exception\DirectoryDoesNotExist;

/**
 * Class Migrations
 * Usage examples:
 * ```
 * //generate new migration class
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c generate -e taoAct
 * //show migrations status
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c status
 * //apply all migrations
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c migrate
 * //migrate to version
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c migrate -v 202003120846502234_tao
 * ```
 * @package oat\tao\scripts\tools
 */
class Migrations extends ScriptAction
{

    protected const MIGRATIONS_DIR = 'migrations';
    protected const COMMAND_GENERATE = 'generate';
    protected const COMMAND_STATUS = 'status';
    protected const COMMAND_MIGRATE = 'migrate';
    protected const COMMAND_EXECUTE = 'execute';
    protected const COMMAND_ROLLBACK = 'rollback';

    private $commands = [
        self::COMMAND_GENERATE => 'migrations:generate',
        self::COMMAND_STATUS => 'migrations:status',
        self::COMMAND_MIGRATE => 'migrations:migrate',
        self::COMMAND_EXECUTE => 'migrations:execute',
        self::COMMAND_ROLLBACK => 'migrations:execute',
    ];

    protected function provideOptions()
    {
        return [
            'command' => [
                'prefix' => 'c',
                'longPrefix' => 'command',
                'required' => true,
                'description' => 'Command to be run'
            ],
            'extension' => [
                'prefix' => 'e',
                'longPrefix' => 'extension',
                'required' => false,
                'description' => 'Extension for which migration needs to be generated'
            ],
            'version' => [
                'prefix' => 'v',
                'longPrefix' => 'version',
                'required' => false,
                'description' => 'Version number to migrate'
            ],
        ];
    }

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Tao migrations tool';
    }

    /**
     * @return Report
     * @throws MigrationException
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    public function run()
    {
        $command = $this->getOption('command');

        if (!isset($this->commands[$command])) {
            throw new ScriptException(sprintf('Command "%s" is not supported', $command));
        }

        switch ($command) {
            case self::COMMAND_GENERATE:
                $output = $this->generate();
                break;
            case self::COMMAND_STATUS:
                $output = $this->status();
                break;
            case self::COMMAND_MIGRATE:
                $output = $this->migrate();
                break;
            case self::COMMAND_EXECUTE:
                $output = $this->executeMigration(false);
                break;
            case self::COMMAND_ROLLBACK:
                $output = $this->executeMigration(true);
                break;
        }

        return new Report(Report::TYPE_INFO, $output->fetch());
    }

    /**
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function generate()
    {
        if (!$this->hasOption('extension')) {
            throw new ScriptException('extension option missed');
        }

        $extension = $this->getExtension();
        $input = ['command' => $this->commands[self::COMMAND_GENERATE]];
        $configuration = $this->getConfiguration();
        $configuration->setExtension($extension);
        $configuration->setMigrationsDirectory($extension->getDir().self::MIGRATIONS_DIR);
        $configuration->setMigrationsNamespace($this->getExtensionNamespace($extension));
        $configuration->setCustomTemplate(
            __DIR__.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'Template.tpl'
        );
        $connection = $this->getConnection();
        $helperSet = new HelperSet();
        $helperSet->set(new QuestionHelper(), 'question');
        $helperSet->set(new ConfigurationHelper($connection, $configuration));
        $this->execute($helperSet, new ArrayInput($input), $output = new BufferedOutput());

        return $output;
    }

    /**
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     */
    private function status()
    {
        $input = new ArrayInput(['command' => $this->commands[self::COMMAND_STATUS]]);
        $this->execute(new HelperSet(), $input, $output = new BufferedOutput());
        return $output;
    }

    /**
     * @param bool $rollback
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     */
    private function executeMigration($rollback = false)
    {
        $input = [
            'command' => $this->commands[self::COMMAND_EXECUTE],
        ];
        if ($this->hasOption('version')) {
            $input['version'] = $this->getOption('version');
        }
        if ($rollback) {
            $input['--down'] = true;
        } else {
            $input['--up'] = true;
        }
        $input[] = '--no-interaction';
        $this->execute(new HelperSet(), new ArrayInput($input), $output = new BufferedOutput());
        return $output;
    }

    /**
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     */
    private function migrate()
    {
        $input = [
            'command' => $this->commands[self::COMMAND_MIGRATE],
        ];

        if ($this->hasOption('version')) {
            $input['version'] = $this->getOption('version');
        }

        $input[] = '--no-interaction';
        $this->execute(new HelperSet(), new ArrayInput($input), $output = new BufferedOutput());
        return $output;
    }

    /**
     * @param HelperSet $helperSet
     * @param InputInterface $input
     * @param OutputInterface|null $output
     * @throws MigrationException
     * @throws ScriptException
     */
    private function execute(HelperSet $helperSet, InputInterface $input, OutputInterface $output = null)
    {
        $helperSet->set(new QuestionHelper(), 'question');

        if (!$helperSet->has('configuration')) {
            $connection = $this->getConnection();
            $configuration = $this->getConfiguration();
            $helperSet->set(new ConfigurationHelper($connection, $configuration));
        }

        $cli = new Application('Doctrine Migrations');
        $cli->setAutoExit(false);
        $cli->setCatchExceptions(true);
        $cli->setHelperSet($helperSet);
        $cli->setCatchExceptions(false);
        $cli->addCommands(array(
            new migrations\commands\GenerateCommand(),
            new Command\MigrateCommand(),
            new Command\StatusCommand(),
            //new Command\DumpSchemaCommand(),
            new Command\ExecuteCommand(),
            //new Command\LatestCommand(),
            //new Command\RollupCommand(),
            //new Command\VersionCommand()
        ));
        try {
            $cli->run($input, $output);
        } catch (\Exception $e) {
            $this->logWarning('Migration error: ' . $e->getMessage());
            throw new ScriptException('Migration error: ' . $e->getMessage());
        }
    }

    /**
     * @return Configuration
     * @throws MigrationException
     */
    private function getConfiguration()
    {
        $connection = $this->getConnection();
        $configuration = new Configuration($connection);
        $configuration->setServiceLocator($this->getServiceLocator());
        $configuration->setName('Tao Migrations');
        $configuration->setMigrationsTableName('doctrine_migration_versions');
        $configuration->setMigrationsColumnName('version');
        $configuration->setMigrationsColumnLength(255);
        $configuration->setMigrationsExecutedAtColumnName('executed_at');
        $configuration->setAllOrNothing(true);
        $configuration->setCheckDatabasePlatform(false);
        $configuration->setMigrationsDirectory(ROOT_PATH);
        $configuration->setMigrationsFinder(new TaoFinder(ROOT_PATH));
        $configuration->setMigrationsNamespace('oat');

        return $configuration;
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
        //todo: use migrations service to store persistence id in it's option
        $persistence = $persistenceManager->getPersistenceById('default');
        return $persistence->getDriver()->getDbalConnection();
    }

    /**
     * @return common_ext_Extension
     * @throws ScriptException
     */
    private function getExtension()
    {
        $extensionId = $this->getOption('extension');
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);

        if (!$extensionManager->isInstalled($extensionId)) {
            throw new ScriptException(sprintf('Extension "%s" is not installed', $extensionId));
        }

        try {
            return $extensionManager->getExtensionById($extensionId);
        } catch (\common_ext_ExtensionException $e) {
            $this->logWarning('Error during extension retrieval: '.$e->getMessage());
            throw new ScriptException(sprintf('Cannot retrieve extension "%s"', $extensionId));
        }
    }

    /**
     * This is an assumption
     * @param common_ext_Extension $extension
     * @return string
     */
    private function getExtensionNamespace(common_ext_Extension $extension)
    {
        return 'oat\\'.$extension->getId().'\\migrations';
    }
}

