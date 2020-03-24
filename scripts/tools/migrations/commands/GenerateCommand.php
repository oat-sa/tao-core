<?php

namespace oat\tao\scripts\tools\migrations\commands;

use Doctrine\Migrations\Tools\Console\Command\GenerateCommand as DoctrineGenerateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCommand
 * @package oat\tao\scripts\tools\migrations\commands
 */
class GenerateCommand extends DoctrineGenerateCommand
{
    /** @var string */
    protected static $defaultName = 'migrations:generate';

    protected function configure() : void
    {
        $this
            ->setAliases(['generate'])
            ->setDescription('Generate a blank migration class.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command generates a blank migration class:

    <info>%command.full_name%</info>
EOT
        );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output) : ?int
    {
        $versionNumber = $this->configuration->generateVersionNumber();

        $migrationGenerator = $this->dependencyFactory->getMigrationGenerator();

        $path = $migrationGenerator->generateMigration($versionNumber);

        $editorCommand = $input->getOption('editor-cmd');

        if ($editorCommand !== null) {
            $this->procOpen($editorCommand, $path);
        }

        $output->writeln([
            sprintf('Generated new migration class to "<info>%s</info>"', $path),
            '',
            sprintf(
                'To run just this migration for testing purposes, you can use <info>sudo -u www-data php index.php \'\oat\tao\scripts\tools\Migrations\' -c execute -v %s</info>',
                $versionNumber
            ),
            '',
            sprintf(
                'To revert the migration you can use <info>sudo -u www-data php index.php \'\oat\tao\scripts\tools\Migrations\' -c rollback -v %s</info>',
                $versionNumber
            ),
        ]);

        return 0;
    }
}
