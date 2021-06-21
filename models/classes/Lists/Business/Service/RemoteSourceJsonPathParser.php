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

namespace oat\tao\model\Lists\Business\Service;

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Domain\Value;
use RuntimeException;

class RemoteSourceJsonPathParser extends ConfigurableService implements RemoteSourceParserInterface
{
    /**
     * @inheritDoc
     */
    public function iterate(array $json, string $uriRule, string $labelRule, string $dependencyUriRule): iterable
    {
        $jsonPath = new JSONPath($json);
        $isDependencyUriRuleFilled = !empty($dependencyUriRule);

        try {
            $uris = $jsonPath->find($uriRule);
            $labels = $jsonPath->find($labelRule);
            $dependencyUris = $isDependencyUriRuleFilled ? $jsonPath->find($dependencyUriRule) : null;
        } catch (JSONPathException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $count = $uris->count();

        if ($count === 0) {
            return;
        }

        if ($count !== $labels->count()) {
            throw new RuntimeException('Count of URIs and labels should be equal');
        }

        do {
            $dependencyUri = null;
            $dependencyUrisValid = true;

            if ($isDependencyUriRuleFilled) {
                $dependencyUri = $dependencyUris->current() ?: null;
                $dependencyUrisValid = $dependencyUris->valid();
                $dependencyUris->next();
            }

            yield new Value(null, $uris->current(), $labels->current(), $dependencyUri);

            $uris->next();
            $labels->next();
        } while ($uris->valid() && $labels->valid() && $dependencyUrisValid);
    }
}
