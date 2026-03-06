<?php

namespace oat\tao\scripts\tools\migrations\commands;

use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @inheritDoc
 * @package oat\tao\scripts\tools\migrations\commands
 */
class GenerateCommand extends DoctrineCommand
{
    /** @var string */
    protected static $defaultName = 'migrations:generate';

    protected function configure(): void
    {
        $this
            ->setName('migrations:generate')
            ->setAliases(['generate'])
            ->setDescription('Generate a blank migration class.')
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace to use for the migration (must be in the list of configured namespaces)'
            )
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command generates a blank migration class:

    <info>%command.full_name%</info>

EOT
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->getDependencyFactory()->getConfiguration();

        $migrationGenerator = $this->getDependencyFactory()->getMigrationGenerator();

        $namespace = $input->getOption('namespace');
        if ($namespace === '') {
            $namespace = null;
        }

        $dirs = $configuration->getMigrationDirectories();
        if ($namespace === null) {
            $namespace = key($dirs);
        } elseif (! isset($dirs[$namespace])) {
            throw new Exception(sprintf('Path not defined for the namespace %s', $namespace));
        }

        assert(is_string($namespace));

        $fqcn = $this->getDependencyFactory()->getClassNameGenerator()->generateClassName($namespace);
        $path = $migrationGenerator->generateMigration($fqcn);
        $output->writeln($this->getOutput($path, $fqcn));

        return 0;
    }

    /**
     * @param string $path
     * @param string $fqcn
     * @return array
     */
    private function getOutput(string $path, string $fqcn): array
    {
        return [
            sprintf('Generated new migration class to "<info>%s</info>"', realpath($path)),
            '',
            sprintf(
                'To run just this migration for testing purposes, you can use <info>sudo -u www-data php '
                . 'index.php \'\oat\tao\scripts\tools\Migrations\' -c execute -v \'%s\'</info>',
                $fqcn
            ),
            '',
            sprintf(
                'To revert the migration you can use <info>sudo -u www-data php index.php '
                . '\'\oat\tao\scripts\tools\Migrations\' -c rollback -v \'%s\'</info>',
                $fqcn
            ),
        ];
    }
}
