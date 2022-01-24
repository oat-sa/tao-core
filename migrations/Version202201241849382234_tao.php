<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\reporting\Report;
use oat\tao\model\config\BackupConfigService;
use oat\tao\scripts\install\AddConfigBackupLocalFileSystem;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202201241849382234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new local folder in filesystem for storing config backups';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(new AddConfigBackupLocalFileSystem());

        $this->addReport(Report::createSuccess("New filesystem 'config' was created"));
    }

    public function down(Schema $schema): void
    {
        /** @var FileSystemService $fileSystemManager */
        $fileSystemManager = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);

        if (!$fileSystemManager->hasDirectory(BackupConfigService::FILE_SYSTEM_ID)) {
            $fileSystemManager->unregisterFileSystem(BackupConfigService::FILE_SYSTEM_ID);
            $this->registerService(FileSystemService::SERVICE_ID, $fileSystemManager);
        }

        $this->addReport(Report::createSuccess('Filesystem config was unregistered. Files still remains.'));
    }
}
