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

namespace oat\tao\scripts\tools\export\RDF;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\export\CustomizedGenerisAdapterRdf;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\export\RDF\ExportRdfStructure' \
 * -o /var/www/html/data/mediaManager.rdf -c 'http://www.tao.lu/Ontologies/TAOMedia.rdf#Media'
 * -o /var/www/html/data/items.rdf -c 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item'
 * -o /var/www/html/data/tests.rdf -c 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test'
 */
class ExportRdfStructure extends ScriptAction
{
    use OntologyAwareTrait;

    private const CLASS_WILDCARD = 'all';

    protected function provideOptions(): array
    {
        return [
            'class-uri' => [
                'prefix' => 'c',
                'longPrefix' => 'class-uri',
                'required' => true,
                'description' => 'The parent class for the imported structure',
            ],
            'output-file-path' => [
                'prefix' => 'o',
                'longPrefix' => 'output-file-path',
                'required' => true,
                'description' => 'Output file path and name',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'This script exports RDF data under target class to rdf file';
    }

    protected function run(): Report
    {
        $path = $this->getOption('output-file-path');
        $parentClassUri = $this->getOption('class-uri');

        if ($parentClassUri === self::CLASS_WILDCARD) {
            $class = null;
        } else {
            $class = $this->getClass($parentClassUri);
        }

        try {
            $rdf = $this->getCustomizedGenerisAdapterRdf()
                ->export($class);

            file_put_contents($path, $rdf);
        } catch (\Exception $e) {
            return Report::createError($e->getMessage());
        }

        return Report::createSuccess(sprintf('%s content saved to %s file', $parentClassUri, $path));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidServiceManagerException
     */
    private function getCustomizedGenerisAdapterRdf(): CustomizedGenerisAdapterRdf
    {
        return $this->getServiceManager()
            ->getContainer()
            ->get(CustomizedGenerisAdapterRdf::class);
    }
}
