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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\import\RDF;

use core_kernel_classes_Class as RdfClass;
use core_kernel_classes_Resource as RdfResource;
use EasyRdf\Format;
use EasyRdf\Graph;
use oat\generis\model\OntologyRdf;
use oat\oatbox\reporting\Report;
use tao_models_classes_import_RdfImporter;
use tao_models_classes_Parser;

class CustomizedRdfImporter extends tao_models_classes_import_RdfImporter
{
    public function importFromFile(RdfClass $targetClass, string $filePath): Report
    {
        $parser = new tao_models_classes_Parser($filePath, ['extension' => 'rdf']);
        $parser->validate();
        if (!$parser->isValid()) {
            return Report::createError('Import failed')
                ->add($parser->getReport());
        }

        return $this->fetchContent($parser->getContent(), $targetClass);
    }

    private function fetchContent($content, $targetClass): Report
    {
        $report = Report::createInfo('Imported data:');
        $graph = new Graph();
        $graph->parse($content);

        $map = [
            OntologyRdf::RDF_PROPERTY => OntologyRdf::RDF_PROPERTY
        ];

        foreach ($graph->resources() as $resource) {
            $map[$resource->getUri()] = $resource->getUri();
        }

        $format = Format::getFormat('php');
        $data = $graph->serialise($format);

        foreach ($data as $subjectUri => $propertiesValues) {
            $report->add(
                $this->importProperties(new RdfResource($subjectUri), $propertiesValues, $map, $targetClass)
            );
        }

        return $report;
    }
}
