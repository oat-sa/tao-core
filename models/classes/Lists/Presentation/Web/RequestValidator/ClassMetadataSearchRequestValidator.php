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

namespace oat\tao\model\Lists\Presentation\Web\RequestValidator;

use common_exception_BadRequest as BadRequestException;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ClassMetadataSearchRequestHandler;
use oat\tao\model\service\InjectionAwareService;
use Psr\Http\Message\ServerRequestInterface;

class ClassMetadataSearchRequestValidator extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/ClassMetadataSearchRequestValidator';

    private const REQUIRED_QUERY_PARAMETERS = [
        ClassMetadataSearchRequestHandler::QUERY_CLASS_ID,
    ];

    /**
     * @param ServerRequestInterface $request
     *
     * @throws BadRequestException
     */
    public function validate(ServerRequestInterface $request): void
    {
        $this->validateRequired($request);
        $this->validateMaxListSize($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws BadRequestException
     */
    private function validateRequired(ServerRequestInterface $request): void
    {
        $missingQueryParameters = array_diff_key(
            array_flip(self::REQUIRED_QUERY_PARAMETERS),
            $request->getQueryParams()
        );

        if ($missingQueryParameters) {
            throw new BadRequestException(
                sprintf(
                    'The following query parameters must be provided: "%s".',
                    implode('", "', array_keys($missingQueryParameters))
                )
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws BadRequestException
     */
    private function validateMaxListSize(ServerRequestInterface $request): void
    {
        $queryParameters = $request->getQueryParams();

        if (!isset($queryParameters[ClassMetadataSearchRequestHandler::QUERY_MAX_LIST_SIZE])) {
           return;
        }

        $maxListSize = $queryParameters[ClassMetadataSearchRequestHandler::QUERY_MAX_LIST_SIZE];

        if ((int)$maxListSize <= 0) {
            throw new BadRequestException(
                sprintf(
                    'The parameter %s should be a positive integer, got: "%s".',
                    ClassMetadataSearchRequestHandler::QUERY_MAX_LIST_SIZE,
                    $maxListSize
                )
            );
        }

    }
}
