<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\webhooks\WebhookFileRegistry;
use oat\tao\model\webhooks\WebhookRdfRegistry;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202303231517262234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register and configure WebhookRdfRegistry';
    }

    public function up(Schema $schema): void
    {
        $serviceManager = $this->getServiceManager();

        /** @var WebhookFileRegistry $fileRegistry */
        $fileRegistry = $serviceManager->get(WebhookRegistryInterface::SERVICE_ID);

        $this->skipIf(($fileRegistry instanceof WebhookRdfRegistry), 'WebhookFileRegistry already registered');

        $rdfRegistry = new WebhookRdfRegistry();
        $this->propagate($rdfRegistry);

        $webHooks = $fileRegistry->getWebhooks();
        $preparedWebHooks = [];
        foreach ($webHooks as $webHook) {
            $preparedWebHooks[$webHook->getId()] = $webHook;
        }

        $events = $fileRegistry->getOption(WebhookFileRegistry::OPTION_EVENTS);

        foreach ($events as $eventClass => $webhookIds) {
            foreach ($webhookIds as $webhookId) {
                $rdfRegistry->addWebhook($preparedWebHooks[$webhookId], [$eventClass]);
            }
        }

        $serviceManager->register(WebhookRegistryInterface::SERVICE_ID, $rdfRegistry);
    }

    public function down(Schema $schema): void
    {
        $serviceManager = $this->getServiceManager();

        /** @var WebhookRdfRegistry $rdfRegistry */
        $rdfRegistry = $serviceManager->get(WebhookRegistryInterface::class);

        $this->skipIf(($rdfRegistry instanceof WebhookFileRegistry), 'WebhookRdfRegistry already registered');

        $webhooks = $rdfRegistry->getWebhooks();

        $fileRegistry = new WebhookFileRegistry();
        $this->propagate($fileRegistry);

        foreach ($webhooks as $webhook) {
            $fileRegistry->addWebhook($webhook);
        }

        $serviceManager->register(WebhookRegistryInterface::SERVICE_ID, $fileRegistry);
    }
}
