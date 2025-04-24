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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use Psr\Log\LoggerInterface;
use Throwable;

class TranslationMoveService
{
    private Ontology $ontology;
    private ResourceTranslationRepository $resourceTranslationRepository;
    private LoggerInterface $logger;


    public function __construct(
        Ontology $ontology,
        ResourceTranslationRepository $resourceTranslationRepository,
        LoggerInterface $logger,
    ) {
        $this->ontology = $ontology;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
        $this->logger = $logger;
    }

    public function moveTranslations(ResourceTransferCommand $command): void
    {
        try {
            $destinationClass = $this->ontology->getClass($command->getTo());
            $translations = $this->resourceTranslationRepository
                ->find(new ResourceTranslationQuery([$command->getFrom()]));

            /** @var AbstractResource $translation */
            foreach ($translations as $translation) {
                $instance = $this->ontology->getResource($translation->getResourceUri());
                $this->changeInstanceClassReference($instance, $destinationClass);
            }
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Error moving translations for originResourceUri [%s] (%s): %s',
                    $command->getTo(),
                    get_class($exception),
                    $exception->getMessage()
                )
            );
        }
    }

    private function changeInstanceClassReference(
        core_kernel_classes_Resource $instance,
        core_kernel_classes_Class $destinationClass
    ): void {
        $fromClasses = $instance->getTypes();

        foreach ($fromClasses as $fromClass) {
            $instance->removeType($fromClass);
        }

        $instance->setType($destinationClass);
    }
}
