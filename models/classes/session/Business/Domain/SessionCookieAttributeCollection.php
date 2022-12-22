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

        if (isset($sessionParams['lifetime'])) {
            $this->attributes[] = new SessionCookieAttribute('lifetime', $sessionParams['lifetime']);
        }

        $this->attributes[] = new SessionCookieAttribute('domain', $cookieDomain);
        $this->attributes[] = new SessionCookieAttribute('secure', $isSecureFlag);
        $this->attributes[] = new SessionCookieAttribute('httponly', true);
    }
    //iterator_to_array not formated properly as we need for cookie paramters
    //iterator to array make a simple array with objects
    public function toArray(): array
    {
        $retVal = [];
        foreach ($this as $attribute) {
            $retVal[$attribute->getName()] = $attribute->getValue();
        }
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
            $rawAttributes[] = (string)$attribute;
        }

        return implode('; ', $rawAttributes);
    }
}
