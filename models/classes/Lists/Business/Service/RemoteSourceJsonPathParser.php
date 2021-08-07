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

namespace oat\tao\model\Lists\Business\Service;

use RuntimeException;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\RemoteSourceContext;

class RemoteSourceJsonPathParser extends ConfigurableService implements RemoteSourceParserInterface
{
    /**
     * @deprecated use $this->iterateContext()
     */
    public function iterate(array $json, string $uriRule, string $labelRule): iterable
    {
        yield from $this->iterateContext(
            new RemoteSourceContext([
                RemoteSourceContext::PARAM_JSON => $json,
                RemoteSourceContext::PARAM_URI_PATH => $uriRule,
                RemoteSourceContext::PARAM_LABEL_PATH => $labelRule,
            ])
        );
    }

    public function iterateContext(ContextInterface $context): iterable
    {
        $jsonPath = new JSONPath($context->getParameter(RemoteSourceContext::PARAM_JSON));

        try {
            $uris = $jsonPath->find($context->getParameter(RemoteSourceContext::PARAM_URI_PATH));
            $labels = $jsonPath->find($context->getParameter(RemoteSourceContext::PARAM_LABEL_PATH));
        } catch (JSONPathException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            $dependencyUriRule = $context->getParameter(RemoteSourceContext::PARAM_DEPENDENCY_URI_PATH);
            $dependencyUris = !empty($dependencyUriRule)
                ? $jsonPath->find($dependencyUriRule)
                : null;
        } catch (JSONPathException $e) {
            $dependencyUris = null;
        }

        $urisCount = $uris->count();

        if ($urisCount === 0) {
            return;
        }

        if ($urisCount !== $labels->count()) {
            throw new RuntimeException('Count of URIs and labels should be equal');
        }

        do {
            $value = new Value(null, $uris->current(), $labels->current());

            if ($dependencyUris !== null) {
                $value->setDependencyUri($dependencyUris->current() ?: null);
                $dependencyUris->next();
            }

            yield $value;

            $uris->next();
            $labels->next();
        } while ($uris->valid() && $labels->valid());
    }
}
