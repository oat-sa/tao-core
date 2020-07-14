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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use core_kernel_classes_Property as RdfProperty;
use core_kernel_persistence_Exception;
use Generator;
use GuzzleHttp\Client;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\service\InjectionAwareService;
use oat\taoBackOffice\model\lists\ListService;
use RuntimeException;
use Traversable;

class RemoteSourcedListService extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/RemoteSourcedListService';

    public const PROPERTY_SOURCE_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#RemoteListSource';
    public const PROPERTY_ITEM_URI_PATH = 'http://www.tao.lu/Ontologies/TAO.rdf#RemoteListItemUriPath';
    public const PROPERTY_ITEM_LABEL_PATH = 'http://www.tao.lu/Ontologies/TAO.rdf#RemoteListItemLabelPath';

    /** @var Client */
    private $client;
    /** @var ValueCollectionService */
    private $valueCollectionService;
    /** @var array */
    private $parsers;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     *
     * @param Client                 $client
     * @param ValueCollectionService $valueCollectionService
     * @param array                  $parsers
     */
    public function __construct(Client $client, ValueCollectionService $valueCollectionService, array $parsers)
    {
        $this->client = $client;
        $this->valueCollectionService = $valueCollectionService;
        $this->parsers = $parsers;
    }

    public function createList(string $label, string $source, string $labelPath, string $uriPath): string
    {
        $class = $this->getListService()->createList($label);

        $propertyType = new RdfProperty('http://www.tao.lu/Ontologies/TAO.rdf#ListType');
        $propertyRemote = new RdfProperty('http://www.tao.lu/Ontologies/TAO.rdf#ListRemote');
        $class->setPropertyValue($propertyType, $propertyRemote);

        $propertySource = new RdfProperty(self::PROPERTY_SOURCE_URI);
        $class->setPropertyValue($propertySource, $source);

        $propertySource = new RdfProperty(self::PROPERTY_ITEM_LABEL_PATH);
        $class->setPropertyValue($propertySource, $labelPath);

        $propertySource = new RdfProperty(self::PROPERTY_ITEM_URI_PATH);
        $class->setPropertyValue($propertySource, $uriPath);

        return $class->getUri();
    }

    /**
     * @param string $url
     *
     * @throws core_kernel_persistence_Exception
     */
    public function sync(string $url): void
    {
        $collection = new ValueCollection($url, ...iterator_to_array($this->fetch($url)));

        $result = $this->valueCollectionService->persist($collection);

        if (!$result) {
            throw new RuntimeException('Sync was not successful');
        }
    }

    public function findAll(string $listUri): ValueCollection
    {
        $request = new ValueCollectionSearchRequest();
        $request->setValueCollectionUri($listUri);

        return $this->valueCollectionService->findAll(
            new ValueCollectionSearchInput($request)
        );
    }

    /**
     * @param string $listUri
     *
     * @return Generator|Value[]
     * @throws core_kernel_persistence_Exception
     */
    public function fetch(string $listUri): Traversable
    {
        $class = $this->getListService()->getList($listUri);

        if ($class === null) {
            throw new RuntimeException(sprintf('Wrong remote list uri %s', $listUri));
        }

        $sourceUrl = (string)$class->getOnePropertyValue($class->getProperty(self::PROPERTY_SOURCE_URI));
        $uriPath = (string)$class->getOnePropertyValue($class->getProperty(self::PROPERTY_ITEM_URI_PATH));
        $labelPath = (string)$class->getOnePropertyValue($class->getProperty(self::PROPERTY_ITEM_LABEL_PATH));


        $this->client = new Client([]);

        $response = $this->client->get($sourceUrl);

        $body = json_decode((string)$response->getBody(), true);

        yield from $this->getParser('jsonpath')->iterate($body, $uriPath, $labelPath);
    }

    protected function getListService(): ListService
    {
        return ListService::singleton();
    }

    private function getParser(string $key)
    {
        if (empty($this->parsers[$key])) {
            throw new RuntimeException(
                sprintf('No %s parsers defined', $key)
            );
        }

        return $this->parsers[$key];
    }
}
