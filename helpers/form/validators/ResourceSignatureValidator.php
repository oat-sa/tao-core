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

use oat\oatbox\service\ServiceManager;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\model\security\SecurityException;
use oat\tao\model\security\SignatureGenerator;

class ResourceSignatureValidator implements ValidatorInterface
{
    /** @var string */
    private $uri;

    /**
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
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
        /** @var SignatureGenerator $generator */
        $generator = ServiceManager::getServiceManager()->get(SignatureGenerator::class);

        if (!($generator->generate($this->uri) === $signature)) {
            $message = 'Signature is not valid';

            throw new SecurityException($message);
        }

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
        return 'Signature is not valid';
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        return $this;
    }
}
