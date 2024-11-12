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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Listener;

use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Event\TranslationTouchedEvent;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;

class TranslationTouchedEventListener
{
    private Ontology $ontology;
    private ResourceTranslationRepository $resourceTranslationRepository;

    public function __construct(Ontology $ontology, ResourceTranslationRepository $resourceTranslationRepository)
    {
        $this->ontology = $ontology;
        $this->resourceTranslationRepository = $resourceTranslationRepository;
    }

    public function onTranslationTouched(TranslationTouchedEvent $event): void
    {
        $resource = $this->ontology->getResource($event->getResourceUri());
        $property = $this->ontology->getProperty(TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES);

        /** @var AbstractResource[] $translations */
        $translations = $this->resourceTranslationRepository->find(
            new ResourceTranslationQuery(
                [$event->getResourceUri()]
            )
        );

        $resource->removePropertyValues($property);

        foreach ($translations as $translation) {
            $resource->setPropertyValue($property, TaoOntology::LANGUAGE_PREFIX . $translation->getLanguageCode());
        }
    }
}
