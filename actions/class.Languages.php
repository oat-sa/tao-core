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

use oat\generis\model\data\Ontology;
use oat\tao\model\http\formatter\ResponseFormatter;
use oat\tao\model\http\response\ErrorJsonResponse;
use oat\tao\model\http\response\SuccessJsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class tao_actions_Languages
{
    /** @var ResponseFormatter */
    private $responseFormatter;

    /** @var ServerRequestInterface */
    private $request;

    /** @var Ontology */
    private $ontology;

    public function __construct(
        ResponseFormatter $responseFormatter,
        Ontology $ontology,
        ServerRequestInterface $request
    ) {
        $this->responseFormatter = $responseFormatter;
        $this->request = $request;
        $this->ontology = $ontology;
    }

    public function index(ResponseInterface $response): ResponseInterface
    {
        try {
            $this->responseFormatter
                ->withExpiration(time() + 60);

            return $this->responseFormatter
                ->withJsonHeader()
                ->withStatusCode(200)
                ->withBody(
                    new SuccessJsonResponse(
                        tao_helpers_I18n::getAvailableLangsByUsage(
                            $this->ontology->getResource(
                                tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA
                            )
                        )
                    )
                )
                ->format($response);
        } catch (Throwable $exception) {
            return $this->responseFormatter
                ->withJsonHeader()
                ->withStatusCode(400)
                ->withBody(new ErrorJsonResponse(0, $exception->getMessage(), []))
                ->format($response);
        }
    }
}
