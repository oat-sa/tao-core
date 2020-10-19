<?php /** @noinspection AutoloadingIssuesInspection */

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
 */

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use oat\generis\model\data\Ontology;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\uri\UriTypeMapperRegistry;

class tao_actions_UriResolver extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;

    public function uriFront(ServerRequest $request): void
    {
        $requestUri = $request->getUri();
        $resourceUri = sprintf(
            '%s://%s%s',
            $requestUri->getScheme(),
            $requestUri->getHost(),
            $requestUri->getPath()
        );

        $this->setData('resolverUrl', _url('uri', 'UriResolver', 'tao'));
        $this->setData('resourceUrl', $resourceUri);
        $this->setView('uri/uri_proxy.tpl');
    }

    public function uri(Ontology $ontology, UriTypeMapperRegistry $mapper, $resourceUri): void
    {
        $resource = $ontology->getResource($resourceUri);

        if (null === $resource) {
            $this->setErrorJsonResponse(
                sprintf('Resource %s not found', $resourceUri)
            );
        }

        $this->setSuccessJsonResponse(
            [
                'url' => $mapper->map($resource)
            ]
        );
    }
}
