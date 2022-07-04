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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use oat\generis\model\data\Ontology;
use oat\tao\model\http\formatter\ResponseFormatter;
use oat\tao\model\http\response\ErrorJsonResponse;
use oat\tao\model\http\response\SuccessJsonResponse;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\routing\Contract\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class tao_actions_Languages implements ActionInterface
{
    /** @var ResponseFormatter */
    private $responseFormatter;

    /** @var LanguageRepositoryInterface */
    private $languageRepository;

    /** @var Ontology */
    private $ontology;

    public function __construct(
        ResponseFormatter $responseFormatter,
        LanguageRepositoryInterface $languageRepository,
        Ontology $ontology
    ) {
        $this->responseFormatter = $responseFormatter;
        $this->languageRepository = $languageRepository;
        $this->ontology = $ontology;
    }

    public function index(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->responseFormatter
                ->withExpiration(time() + 60);

            return $this->responseFormatter
                ->withJsonHeader()
                ->withStatusCode(200)
                ->withBody(new SuccessJsonResponse($this->getLanguages($request)))
                ->format($response);
        } catch (Throwable $exception) {
            return $this->responseFormatter
                ->withJsonHeader()
                ->withStatusCode(400)
                ->withBody(new ErrorJsonResponse(0, $exception->getMessage(), []))
                ->format($response);
        }
    }

    private function getLanguages(ServerRequestInterface $request): array
    {
        $version = $request->getHeader('Accept-version')[0] ?? 'v1';

        if ($version === 'v2') {
            return $this->languageRepository->findAvailableLanguagesByUsage()->jsonSerialize();
        }

        return tao_helpers_I18n::getAvailableLangsByUsage(
            $this->ontology->getResource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA)
        );
    }
}
