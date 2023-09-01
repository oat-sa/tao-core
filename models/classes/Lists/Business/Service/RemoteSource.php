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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use Traversable;
use RuntimeException;
use GuzzleHttp\Client;
use oat\tao\model\Context\ContextInterface;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Domain\RemoteSourceContext;

class RemoteSource extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/RemoteSource';

    /** @var RemoteSourceParserInterface[] */
    private $parsers;

    /** @var Client|null */
    private $client;

    public function __construct(array $parsers, Client $client = null)
    {
        parent::__construct();

        $this->parsers = $parsers;
        $this->client  = $client;
    }

    /**
     * @deprecated Use $this->fetchByContext()
     */
    public function fetch(string $sourceUrl, string $uriPath, string $labelPath, string $parser): Traversable
    {
        $context = new RemoteSourceContext([
            RemoteSourceContext::PARAM_SOURCE_URL => $sourceUrl,
            RemoteSourceContext::PARAM_URI_PATH => $uriPath,
            RemoteSourceContext::PARAM_LABEL_PATH => $labelPath,
            RemoteSourceContext::PARAM_PARSER => $parser,
        ]);

        yield from $this->fetchByContext($context);
    }

    public function fetchByContext(ContextInterface $context): Traversable
    {
        $response = $this->getClient()->get($context->getParameter(RemoteSourceContext::PARAM_SOURCE_URL));
        $context->setParameter(
            RemoteSourceContext::PARAM_JSON,
            json_decode((string) $response->getBody(), true)
        );

        yield from $this
            ->getParser($context->getParameter(RemoteSourceContext::PARAM_PARSER))
            ->iterateByContext($context);
    }

    private function getClient(): Client
    {
        return $this->client ?? new Client();
    }

    private function getParser(string $key): RemoteSourceParserInterface
    {
        if (empty($this->parsers[$key])) {
            throw new RuntimeException(
                sprintf('No %s parsers defined', $key)
            );
        }

        return $this->parsers[$key] instanceof ConfigurableService
            ? $this->propagate($this->parsers[$key])
            : $this->parsers[$key];
    }
}
