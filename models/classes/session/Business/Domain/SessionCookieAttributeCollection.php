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

declare (strict_types = 1);

namespace oat\tao\model\session\Business\Domain;

use common_http_Request as Request;
use IteratorAggregate;
use oat\tao\model\session\Business\Contract\SessionCookieAttributeInterface;
use tao_helpers_Uri as UriHelper;

final class SessionCookieAttributeCollection implements IteratorAggregate
{
    /** @var SessionCookieAttributeInterface[] */
    private $attributes = [];

    public function __construct()
    {
        $sessionParams = session_get_cookie_params();
        $cookieDomain = UriHelper::isValidAsCookieDomain(ROOT_URL)
        ? UriHelper::getDomain(ROOT_URL)
        : $sessionParams['domain'];
        $isSecureFlag = Request::isHttps();

        $this->add(new SessionCookieAttribute('lifetime', (string) $sessionParams['lifetime']));
        $this->add(new SessionCookieAttribute('domain', $cookieDomain));
        $this->add(new SessionCookieAttribute('secure', (string) $isSecureFlag));
        $this->add(new SessionCookieAttribute('httponly', 'true'));
    }
    public function getCookieParams(?string $lifetime): array
    {
        $retVal = [];
        foreach ($this as $attribute) {
            $retVal[$attribute->name()] = $attribute->value();
        }
        $retVal['lifetime'] = $lifetime;
        return $retVal;
    }

    public function add(SessionCookieAttributeInterface $attribute): self
    {
        $collection = clone $this;

        $collection->attributes[] = $attribute;

        return $collection;
    }

    /**
     * @return iterable|SessionCookieAttributeInterface[]
     */
    public function getIterator(): iterable
    {
        yield from $this->attributes;
    }

    public function __toString(): string
    {
        $rawAttributes = [];

        foreach ($this as $attribute) {
            $rawAttributes[] = (string) $attribute;
        }

        return implode('; ', $rawAttributes);
    }
}
