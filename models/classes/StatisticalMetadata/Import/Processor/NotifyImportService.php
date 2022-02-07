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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata\Import\Processor;

use Google\Cloud\PubSub\PubSubClient;
use oat\generis\model\data\Ontology;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Result\ImportResult;
use oat\tao\model\TaoOntology;
use oat\taoDeliveryRdf\model\DataStore\Metadata\JsonMetadataCompiler;
use Psr\Log\LoggerInterface;
use Throwable;

class NotifyImportService
{
    /** @var Ontology */
    private $ontology;

    /** @var LoggerInterface */
    private $logger;

    /** @var JsonMetadataCompiler */
    private $jsonMetadataCompiler;

    public function __construct(
        Ontology $ontology,
        LoggerInterface $logger,
        JsonMetadataCompiler $jsonMetadataCompiler
    ) {
        $this->ontology = $ontology;
        $this->logger = $logger;
        $this->jsonMetadataCompiler = $jsonMetadataCompiler;
    }

    public function notify(ImportResult $result): void
    {
        // @TODO Migrate pub/sub to proper abstraction
        // @TODO Retry multiple times in case of fail
        // @TODO Migrate this class from taoDeliveryRdf extension
        // @TODO Add proper pub/sub credentials via config

        $data = [];

        foreach ($result->getImportedRecords() as $record) {
            $resourceId = $record[Header::ITEM_ID] ?? $record[Header::TEST_ID];

            $resource = $this->ontology->getResource($resourceId);
            $compiled = $this->jsonMetadataCompiler->compile($resource);
            $compiled['@type'] = isset($record[Header::ITEM_ID])
                ? TaoOntology::CLASS_URI_ITEM
                : TaoOntology::CLASS_URI_TEST;

            $data[] = $compiled;
        }

        $topicId = 'oat-demo-delivery-processing-topic';

        $tries = 0;
        $maxTries = 10;

        do {
            try {
                $tries++;

                $topic = $this->getPubSub()->topic($topicId);

                $messageIds = $topic->publish(
                    [
                        'data' => json_encode($data),
                    ]
                );

                $this->logger->info(
                    sprintf(
                        'Pub/Sub messages %s send for Statistical data',
                        var_export($messageIds, true)
                    )
                );

                return;
            } catch (Throwable $exception) {
                $this->logger->error(
                    sprintf(
                        'Pub/Sub messages not send for Statistical data: %s',
                        $exception->getMessage()
                    )
                );
            }
        } while ($tries < $maxTries);

        throw new \Exception('Sync error', 0, $exception); //@TODO Provide proper exception
    }

    private function getPubSub(): PubSubClient
    {
        $keyFilePath = __DIR__ . '/../../../../../../oat-dev-eu-key.json';

        return new PubSubClient(
            [
                'keyFilePath' => $keyFilePath,
            ]
        );
    }
}
