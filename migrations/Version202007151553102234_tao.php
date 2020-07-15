<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Lists\Business\Service\RemoteSource;
use oat\tao\model\Lists\Business\Service\RemoteSourcedListService;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\DataAccess\Repository\RdfValueCollectionRepository;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;
use oat\tao\scripts\install\RegisterValueCollectionServices;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202007151553102234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Re-register Value Collection services';
    }

    public function up(Schema $schema): void
    {
        (new RegisterValueCollectionServices())
            ->setServiceLocator($this->getServiceManager())
        ();
    }

    public function down(Schema $schema): void
    {
        /** @var RdfValueCollectionRepository $repository */
        $repository = $this->getServiceLocator()->get(RdfValueCollectionRepository::SERVICE_ID);

        $this->getServiceManager()->register(
            ValueCollectionService::SERVICE_ID,
            new ValueCollectionService($repository)
        );

        foreach (
            [
                RdsValueCollectionRepository::SERVICE_ID,
                RemoteSource::SERVICE_ID,
                RemoteSourcedListService::SERVICE_ID,
            ] as $serviceId
        ) {
            $this->getServiceManager()->unregister($serviceId);
        }
    }
}
