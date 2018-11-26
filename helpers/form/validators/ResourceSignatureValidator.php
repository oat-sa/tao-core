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
use oat\tao\model\security\SignatureGenerator;

class ResourceSignatureValidator implements ValidatorInterface
{
    private $uri;
    private $classUri;

    public function __construct($uri, $classUri)
    {
        $this->uri = $uri;
        $this->classUri = $classUri;
    }

    /**
     * @param string $signature
     *
     * @return boolean true only if valid
     *
     * @throws \common_exception_InconsistentData
     */
    public function evaluate($signature)
    {
        //$tokenService = $this->getServiceManager()->get(TokenService::SERVICE_ID);
        $generator = ServiceManager::getServiceManager()->get(SignatureGenerator::class);

        if (!($generator->generate($this->uri) === $signature)) {
            $message = 'Signature is not valid';
            \common_Logger::e($message);

            throw new \common_exception_InconsistentData($message);
        }

        return true;
    }

    /**
     * return validator name
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }

    /**
     * return validator options
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * return error message
     * @return string
     */
    public function getMessage()
    {
        return 'Signature is not valid';
    }

    /**
     * set up error message
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return $this;
    }

    /**
     * set up validator options
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        return $this;
    }
}
