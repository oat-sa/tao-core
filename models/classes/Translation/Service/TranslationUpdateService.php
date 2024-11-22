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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Service;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Command\UpdateTranslationCommand;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use Psr\Log\LoggerInterface;
use Throwable;

class TranslationUpdateService
{
    private Ontology $ontology;
    private LoggerInterface $logger;
    private TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer;

    public function __construct(
        Ontology $ontology,
        LoggerInterface $logger,
        TranslatedIntoLanguagesSynchronizer $translatedIntoLanguagesSynchronizer
    ) {
        $this->ontology = $ontology;
        $this->logger = $logger;
        $this->translatedIntoLanguagesSynchronizer = $translatedIntoLanguagesSynchronizer;
    }

    public function update(UpdateTranslationCommand $command): core_kernel_classes_Resource
    {
        try {
            $instance = $this->ontology->getResource($command->getResourceUri());

            if (!$instance->exists()) {
                throw new ResourceTranslationException(
                    sprintf(
                        'Resource %s does not exist',
                        $command->getResourceUri()
                    )
                );
            }

            $translationType = $instance->getOnePropertyValue(
                $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_TYPE)
            );

            if ($translationType->getUri() !== TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION) {
                throw new ResourceTranslationException(
                    sprintf(
                        'Resource %s is not a translation',
                        $command->getResourceUri()
                    )
                );
            }

            $instance->editPropertyValues(
                $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATION_PROGRESS),
                $command->getProgressUri()
            );

            $this->translatedIntoLanguagesSynchronizer->sync($instance);

            return $instance;
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Could not update translation status of [resourceUri=%s] (%s): %s',
                    $command->getResourceUri(),
                    get_class($exception),
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }
}
