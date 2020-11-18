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
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Presentation\Web\RequestValidator;

use common_exception_BadRequest as BadRequestException;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ValueCollectionSearchRequestHandler;
use oat\tao\model\service\InjectionAwareService;
use Psr\Http\Message\ServerRequestInterface;

class ValueCollectionSearchRequestValidator extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/ValueCollectionSearchRequestValidator';

    private const REQUIRED_QUERY_PARAMETERS = [
        ValueCollectionSearchRequestHandler::QUERY_PARAMETER_ID,
    ];

    /**
     * @param ServerRequestInterface $request
     *
     * @throws BadRequestException
     */
    public function validate(ServerRequestInterface $request): void
    {
        $this->validateRequired($request);
        $this->validateSubject($request);
        $this->validateExclude($request);
        $this->validateExcludeElements($request);
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
    private function validateSubject(ServerRequestInterface $request): void
    {
        $queryParameters = $request->getQueryParams();

        if (
            isset($queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_SUBJECT])
            && !is_string($queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_SUBJECT])
        ) {
            throw $this->createBadTypeException(
                ValueCollectionSearchRequestHandler::QUERY_PARAMETER_SUBJECT,
                $queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_SUBJECT],
                'string'
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws BadRequestException
     */
    private function validateExclude(ServerRequestInterface $request): void
    {
        $queryParameters = $request->getQueryParams();

        if (
            isset($queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_EXCLUDE])
            && !is_array($queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_EXCLUDE])
        ) {
            throw $this->createBadTypeException(
                ValueCollectionSearchRequestHandler::QUERY_PARAMETER_EXCLUDE,
                $queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_EXCLUDE],
                'array'
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws BadRequestException
     */
    private function validateExcludeElements(ServerRequestInterface $request): void
    {
        $queryParameters = $request->getQueryParams();

        foreach ($queryParameters[ValueCollectionSearchRequestHandler::QUERY_PARAMETER_EXCLUDE] ?? [] as $key => $excluded) {
            if (!is_string($excluded)) {
                throw $this->createBadTypeException(
                    ValueCollectionSearchRequestHandler::QUERY_PARAMETER_EXCLUDE . "[$key]",
                    $excluded,
                    'string'
                );
            }
        }
    }

    private function createBadTypeException(string $parameter, $value, string $expectedType): BadRequestException
    {
        return new BadRequestException(
            sprintf(
                '"%s" query parameter is expected to be of %s type, %s given.',
                $parameter,
                $expectedType,
                gettype($value)
            )
        );
    }
}
