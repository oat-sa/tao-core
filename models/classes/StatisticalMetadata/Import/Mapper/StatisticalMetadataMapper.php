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

namespace oat\tao\model\StatisticalMetadata\Import\Mapper;

use oat\generis\model\data\Ontology;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Model\MetadataProperty;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataAliasesExtractor;
use oat\tao\model\StatisticalMetadata\Contract\StatisticalMetadataRepositoryInterface;

class StatisticalMetadataMapper
{
    public const KEY_URI = 'uri';
    public const KEY_DOMAIN = 'domain';
    public const KEY_WIDGET = 'widget';

    /** @var MetadataAliasesExtractor */
    private $metadataAliasesExtractor;

    /** @var StatisticalMetadataRepositoryInterface */
    private $statisticalMetadataRepository;

    /** @var Ontology */
    private $ontology;

    public function __construct(
        MetadataAliasesExtractor $metadataAliasesExtractor,
        StatisticalMetadataRepositoryInterface $statisticalMetadataRepository,
        Ontology $ontology
    ) {
        $this->metadataAliasesExtractor = $metadataAliasesExtractor;
        $this->statisticalMetadataRepository = $statisticalMetadataRepository;
        $this->ontology = $ontology;
    }

    public function getMap(array $header): array
    {
        $metadataAliases = $this->metadataAliasesExtractor->extract($header);
        $metadataProperties = $this->statisticalMetadataRepository->findProperties(
            [
                StatisticalMetadataRepositoryInterface::FILTER_ALIASES => $metadataAliases,
            ]
        );

        return $this->map($metadataProperties);
    }

    /**
     * @param MetadataProperty[] $metadataProperties
     */
    private function map(array $metadataProperties): array
    {
        $map = [];

        foreach ($metadataProperties as $metadataProperty) {
            $map[Header::METADATA_PREFIX . $metadataProperty->alias][] = [
                self::KEY_URI => $metadataProperty->uri,
                self::KEY_DOMAIN => $this->ontology->getClass($metadataProperty->domain),
                self::KEY_WIDGET => $metadataProperty->widget,
            ];
        }

        return $map;
    }
}
