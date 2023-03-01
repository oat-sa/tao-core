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

namespace oat\tao\model\import;

use core_kernel_classes_Class as RdfClass;
use EasyRdf\Format;
use EasyRdf\Graph;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\oatbox\reporting\Report;
use tao_models_classes_import_RdfImporter;
use tao_models_classes_Parser;

/**
 * Purpose of this class extension is to avoid creating new identifiers for imported resources as it is done in parent
 */
class CustomizedRdfImporter extends tao_models_classes_import_RdfImporter
{
    use OntologyAwareTrait;

    private Graph $easyRdfGraph;

    public function __construct(Graph $easyRdfGraph)
    {
        $this->easyRdfGraph = $easyRdfGraph;
    }

    public function importFromFile(RdfClass $targetClass, string $filePath): Report
    {
        $parser = new tao_models_classes_Parser($filePath, ['extension' => 'rdf']);
        $parser->validate();
        if (!$parser->isValid()) {
            return Report::createError('Import failed')
                ->add($parser->getReport());
        }

        return $this->importFileContent($parser->getContent(), $targetClass);
    }

    private function importFileContent($content, $targetClass): Report
    {
        $report = Report::createInfo('Imported data:');
        $this->easyRdfGraph->parse($content);

        $map = [
            OntologyRdf::RDF_PROPERTY => OntologyRdf::RDF_PROPERTY
        ];

        foreach ($this->easyRdfGraph->resources() as $resource) {
            $map[$resource->getUri()] = $resource->getUri();
        }

        $data = $this->easyRdfGraph->serialise(Format::getFormat('php'));

        foreach ($data as $subjectUri => $propertiesValues) {
            $report->add(
                $this->importProperties($this->getResource($subjectUri), $propertiesValues, $map, $targetClass)
            );
        }

        return $report;
    }
}
