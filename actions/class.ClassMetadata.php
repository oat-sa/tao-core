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
 *
 */

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\Lists\Business\Domain\ClassInformation;
use oat\tao\model\Lists\Business\Service\ClassMetadataSearcherProxy;
use oat\tao\model\Lists\Business\Service\GetClassMetadataValuesService;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ClassMetadataSearchRequestHandler;

class tao_actions_ClassMetadata extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;

    /**
     * @deprecated $this->getWithMapping should be used
     */
    public function get(
        ServerRequest $request,
        ClassMetadataSearchRequestHandler $classMetadataSearchRequestHandler,
        ClassMetadataSearcherProxy $classMetadataSearcher
    ): void {
        $this->setSuccessJsonResponse(
            $classMetadataSearcher->findAll($classMetadataSearchRequestHandler->handle($request))
        );
    }

    public function getWithMapping(
        ServerRequest $request,
        ClassMetadataSearchRequestHandler $classMetadataSearchRequestHandler,
        ClassMetadataSearcherProxy $classMetadataSearcher
    ): void {
        $this->setSuccessJsonResponse(
            new ClassInformation(
                $classMetadataSearcher->findAll($classMetadataSearchRequestHandler->handle($request))
                ,[
                    GetClassMetadataValuesService::DATA_TYPE_LIST => 'uri',
                    GetClassMetadataValuesService::DATA_TYPE_TEXT => 'label'
                ]
            )
        );
    }
}
