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

namespace oat\tao\model\Lists\Presentation\Web\RequestHandler;

use common_exception_BadRequest as BadRequestException;
use core_kernel_classes_Property as RdfProperty;
use oat\tao\model\Lists\Business\Domain\ClassMetadataSearchRequest;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use oat\tao\model\Lists\Presentation\Web\RequestValidator\ClassMetadataSearchRequestValidator;
use oat\tao\model\service\InjectionAwareService;
use Psr\Http\Message\ServerRequestInterface;
use tao_helpers_Uri as Id;

class ClassMetadataSearchRequestHandler extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/ClassMetadataSearchRequestHandler';
    public const QUERY_CLASS_ID = 'classUri';

    /** @var ClassMetadataSearchRequestValidator */
    private $requestValidator;

    public function __construct(ClassMetadataSearchRequestValidator $requestValidator)
    {
        parent::__construct();

        $this->requestValidator = $requestValidator;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ClassMetadataSearchInput
     *
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ClassMetadataSearchInput
    {
        $this->requestValidator->validate($request);

        $queryParameters = $request->getQueryParams();

        $classUri = Id::decode(
            $queryParameters[self::QUERY_CLASS_ID]
        );

        $searchRequest = (new ClassMetadataSearchRequest())
            ->setClassUri($classUri);

        return new ClassMetadataSearchInput($searchRequest);
    }

    /**
     * @param string $propertyUri
     *
     * @return string|null
     */
    protected function getPropertyListUri(string $propertyUri): ?string
    {
        $property = new RdfProperty($propertyUri);
        $list = $property->getRange();

        if ($list !== null) {
            return $list->getUri();
        }

        return null;
    }
}
