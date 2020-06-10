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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers\form\validators;

use InvalidArgumentException;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\model\security\SecurityException;
use oat\tao\model\security\SignatureValidator;

final class ResourceSignatureValidator implements ValidatorInterface
{
    /** @var string */
    private $uri;

    /** string */
    private $message = 'Signature is not valid';
    /**
     * @var SignatureValidator
     */
    private $signatureValidator;

    /**
     * @param SignatureValidator $signatureValidator
     * @param string $uri
     */
    public function __construct(SignatureValidator $signatureValidator, $uri)
    {
        $this->uri = $uri;
        $this->signatureValidator = $signatureValidator;
    }

    /**
     * @param string $signature
     *
     * @return boolean true only if valid
     *
     * @throws SecurityException
     * @throws \oat\tao\model\metadata\exception\InconsistencyConfigException
     */
    public function evaluate($signature)
    {
        $this->signatureValidator->checkSignature($signature, $this->uri);

        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        throw new InvalidArgumentException('This validator does not have any options');
    }
}
