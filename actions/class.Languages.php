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
 */

declare(strict_types=1);

class tao_actions_Languages extends tao_actions_CommonModule
{
    public function index(): void
    {
        try {
            $this->getResponseFormatter()
                ->withExpiration(time() + 60);

            $this->returnSuccessJsonResponse(
                tao_helpers_I18n::getAvailableLangsByUsage(
                    new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA)
                )
            );
        } catch (Throwable $exception) {
            $this->returnErrorJsonResponse($exception->getMessage());
        }
    }
}
