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

use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ValueCollectionSearchRequestHandler;

class tao_actions_PropertyValues extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;

    public function get(
        ValueCollectionService $valueCollectionService,
        ValueCollectionSearchRequestHandler $valueCollectionSearchRequestHandler
    ): void {
        $this->setSuccessJsonResponse(
            $valueCollectionService->findAll(
                $valueCollectionSearchRequestHandler->handle(
                    $this->getPsrRequest()
                )
            )
        );
    }
}
