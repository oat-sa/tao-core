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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\import\CustomizedRdfImporter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\import\RDF\ImportMirrorRdfStructure' \
 * -f /var/www/html/data/mediaManager.rdf -c 'http://www.tao.lu/Ontologies/TAOMedia.rdf#Media'
 * -f /var/www/html/data/items.rdf -c 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item'
 * -f /var/www/html/data/tests.rdf -c 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test'
 */
class ImportMirrorRdfStructure extends ScriptAction
{
    use OntologyAwareTrait;

    protected function provideOptions(): array
    {
        return [
            'parent-class-uri' => [
                'prefix' => 'c',
                'longPrefix' => 'class-uri',
                'required' => true,
                'description' => 'The parent class for the imported structure',
            ],
            'file-path' => [
                'prefix' => 'f',
                'longPrefix' => 'file-path',
                'required' => true,
                'description' => 'File path location.',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'This script imports RDF data from file under target class keeping original identifiers';
    }

    protected function run(): Report
    {
        $path = $this->getOption('file-path');
        $parentClassUri = $this->getOption('parent-class-uri');
        $targetClass = $this->getClass($parentClassUri);

        return $this->getRdfImporter()
            ->importFromFile($targetClass, $path);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidServiceManagerException
     */
    private function getRdfImporter(): CustomizedRdfImporter
    {
        return $this->getServiceManager()
            ->getContainer()
            ->get(CustomizedRdfImporter::class);
    }
}
