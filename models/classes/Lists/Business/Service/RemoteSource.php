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

use GuzzleHttp\Client;
use oat\tao\model\service\InjectionAwareService;
use RuntimeException;
use Traversable;

class RemoteSource extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/RemoteSource';

    /** @var RemoteSourceParserInterface[] */
    private $parsers;

    /** @var Client */
    private $client;

    public function __construct(array $parsers, ?Client $client = null)
    {
        parent::__construct();

        $this->client = $client ?? new Client([]);
        $this->parsers = $parsers;
    }

    public function fetch(string $sourceUrl, string $uriPath, string $labelPath, string $parser): Traversable
    {
        $response = $this->client->get($sourceUrl);

        $body = json_decode((string)$response->getBody(), true);

        yield from $this->getParser($parser)->iterate($body, $uriPath, $labelPath);
    }

    private function getParser(string $key): RemoteSourceParserInterface
    {
        if (empty($this->parsers[$key])) {
            throw new RuntimeException(
                sprintf('No %s parsers defined', $key)
            );
        }

        return $this->parsers[$key];
    }
}
