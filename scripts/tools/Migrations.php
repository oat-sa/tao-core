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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Generator\ClassNameGenerator;
use Doctrine\Migrations\MigrationRepository;
use Doctrine\Migrations\Exception\MigrationException;
use Doctrine\Migrations\Exception\NoMigrationsToExecute;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Version\Comparator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\extension\script\ScriptException;
use oat\tao\scripts\tools\migrations\TaoClassNameGenerator;
use oat\tao\scripts\tools\migrations\commands\GenerateCommand;
use oat\tao\scripts\tools\migrations\TaoComparator;
use oat\tao\scripts\tools\migrations\TaoMigrationRepository;
use common_ext_Extension;
use common_report_Report as Report;
use common_ext_ExtensionsManager as ExtensionsManager;
use common_ext_Extension as Extension;

/**
 * Class Migrations
 * Usage examples:
 * ```
 * //generate new migration class
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c generate -e taoItems
 * //show migrations status
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c status
 * //apply all migrations
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c migrate
 * //migrate to version
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c migrate -v 'oat\generis\migrations\Version202004220924112348_generis'
 * //Add migrations to the migrations table without execution (skip extension migrations)
 * sudo -u www-data php index.php '\oat\tao\scripts\tools\Migrations' -c add -e tao
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
    protected const COMMAND_ADD = 'add';
    protected const COMMAND_INIT = 'init';

    private $commands = [
        self::COMMAND_GENERATE => 'migrations:generate',
        self::COMMAND_STATUS => 'migrations:status',
        self::COMMAND_MIGRATE => 'migrations:migrate',
        self::COMMAND_EXECUTE => 'migrations:execute',
        self::COMMAND_ROLLBACK => 'migrations:execute',
        self::COMMAND_ADD => 'migrations:version',
        self::COMMAND_INIT => 'migrations:sync-metadata-storage',
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

        $output = $this->{$command}();
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
        $extension = $this->getExtension();
        if (!is_dir($extension->getDir().self::MIGRATIONS_DIR)) {
            mkdir($extension->getDir().self::MIGRATIONS_DIR);
        }
        $input = [
            'command' => $this->commands[self::COMMAND_GENERATE],
            '--namespace' => $this->getExtensionNamespace($extension)
        ];
        $configuration = $this->getConfiguration();
        $configuration->setCustomTemplate(
            __DIR__.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'Template.tpl'
        );
        $dependencyFactory = $this->getDependencyFactory($configuration);
        $this->executeMigration($dependencyFactory, new ArrayInput($input), $output = new BufferedOutput());
        return $output;
    }

    /**
     * @return BufferedOutput
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function migrate()
    {
        $input = ['command' => $this->commands[self::COMMAND_MIGRATE]];
        if ($this->hasOption('version')) {
            $input['version'] = $this->getOption('version');
        }
        $dependencyFactory = $this->getDependencyFactory($this->getConfiguration());
        $input[] = '--no-interaction';
        $this->executeMigration($dependencyFactory, new ArrayInput($input), $output = new BufferedOutput());
        return $output;
    }

    /**
     * Add versions directly to the migrations table without executing them (skip migration)
     * @return BufferedOutput
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function add()
    {
        $input = ['command' => $this->commands[self::COMMAND_ADD], '--add' => true, '--all' => true];
        $output = new BufferedOutput();
        $input[] = '--no-interaction';
        $dependencyFactory = $this->getDependencyFactory($this->getConfiguration());
        $this->executeMigration($dependencyFactory, new ArrayInput($input), $output);
        return $output;
    }

    /**
     * @return BufferedOutput
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function init()
    {
        $input = ['command' => $this->commands[self::COMMAND_INIT]];
        $output = new BufferedOutput();
        $input[] = '--no-interaction';
        $dependencyFactory = $this->getDependencyFactory($this->getConfiguration());
        $this->executeMigration($dependencyFactory, new ArrayInput($input), $output);
        return $output;
    }

    /**
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function status()
    {
        $output = new BufferedOutput();
        $dependencyFactory = $this->getDependencyFactory($this->getConfiguration());
        $this->executeMigration($dependencyFactory, new ArrayInput(['command' => $this->commands[self::COMMAND_STATUS]]), $output);
        return $output;
    }

    /**
     * @param bool $rollback
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function execute(bool $rollback = false)
    {
        $input = [
            'command' => $this->commands[self::COMMAND_EXECUTE],
            'versions' => [$this->getOption('version')],
        ];
        if ($rollback) {
            $input['--down'] = true;
        } else {
            $input['--up'] = true;
        }
        $input[] = '--no-interaction';

        $dependencyFactory = $this->getDependencyFactory($this->getConfiguration());
        $this->executeMigration($dependencyFactory, new ArrayInput($input), $output = new BufferedOutput());
        return $output;
    }

    /**
     * @return BufferedOutput
     * @throws MigrationException
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function rollback()
    {
        return $this->execute(true);
    }

    /**
     * @param DependencyFactory $dependencyFactory
     * @param InputInterface $input
     * @param OutputInterface|null $output
     * @throws ScriptException
     */
    private function executeMigration(DependencyFactory $dependencyFactory, InputInterface $input, OutputInterface $output = null)
    {
        $cli = new Application('Doctrine Migrations');
        $cli->setCatchExceptions(true);
        $cli->setAutoExit(false);
        $cli->addCommands(array(
            new GenerateCommand($dependencyFactory),
            new MigrateCommand($dependencyFactory),
            new StatusCommand($dependencyFactory),
            new ExecuteCommand($dependencyFactory),
            new VersionCommand($dependencyFactory),
            new SyncMetadataCommand($dependencyFactory),
        ));

        try {
            $cli->run($input, $output);
        } catch (NoMigrationsToExecute $e) {
            $output->write($e->getMessage());
        } catch (\Exception $e) {
            $this->logWarning('Migration error: ' . $e->getMessage());
            throw new ScriptException('Migration error: ' . $e->getMessage());
        }
    }

    private function getDependencyFactory($configuration)
    {
        $connection = $this->getConnection();
        $dependencyFactory = DependencyFactory::fromConnection(
            new ExistingConfiguration($configuration),
            new ExistingConnection($connection)
        );
        $extManager = $this->getServiceManager()->get(ExtensionsManager::SERVICE_ID);
        if ($this->hasOption('extension')) {
            $dependencyFactory->setService(ClassNameGenerator::class, new TaoClassNameGenerator($this->getExtension()));
        }
        $dependencyFactory->setService(Comparator::class, new TaoComparator($extManager));
        $dependencyFactory->setService(MigrationRepository::class, new TaoMigrationRepository(
            $configuration->getMigrationClasses(),
            $configuration->getMigrationDirectories(),
            $dependencyFactory->getMigrationsFinder(),
            $dependencyFactory->getMigrationFactory(),
            $dependencyFactory->getVersionComparator()
        ));
        return $dependencyFactory;
    }

    /**
     * @return Configuration
     * @throws ScriptException
     * @throws \common_ext_ExtensionException
     */
    private function getConfiguration()
    {
        $configuration = new Configuration();
        /** @var ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
        /** @var Extension $extension */
        if ($this->hasOption('extension')) {
            $extensions = [$this->getExtension()];
        } else {
            $extensions = $extensionManager->getInstalledExtensions();
        }
        foreach ($extensions as $extension) {
            $path = $extension->getDir().self::MIGRATIONS_DIR;
            if (is_dir($path)) {
                $configuration->addMigrationsDirectory(
                    $this->getExtensionNamespace($extension),
                    $extension->getDir().self::MIGRATIONS_DIR
                );
            }
        }
        return $configuration;
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
        $persistence = $persistenceManager->getPersistenceById('default');
        return $persistence->getDriver()->getDbalConnection();
    }

    /**
     * @return common_ext_Extension
     * @throws ScriptException
     */
    private function getExtension()
    {
        /** @var ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);

        if (!$this->hasOption('extension')) {
            throw new ScriptException('Extension parameter missed');
        }

        $extensionId = $this->getOption('extension');
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
