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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\test\unit\helpers\form\validators;

use oat\generis\test\TestCase;
use oat\tao\helpers\form\validators\ResourceSignatureValidator;
use oat\tao\model\security\SecurityException;
use oat\tao\model\security\SignatureValidator;

class ResourceSignatureValidatorTest extends TestCase
{
    private function getSignatureValidatorMock()
    {
        return $this->getMockBuilder(SignatureValidator::class)->getMock();
    }

    public function testEvaluate()
    {
        $signatureValidator = $this->getSignatureValidatorMock();

        $resourceValidator = new ResourceSignatureValidator($signatureValidator, 'valid signature');

        $result = $resourceValidator->evaluate('signature');

        $this->assertTrue($result);
    }

    public function testNotSuccessfulEvaluate()
    {
        $this->expectException(SecurityException::class);

        $signatureValidator = $this->getSignatureValidatorMock();

        $signatureValidator->method('checkSignature')->willThrowException(new SecurityException('exception message'));

        $resourceValidator = new ResourceSignatureValidator($signatureValidator, 'valid signature');

        $resourceValidator->evaluate('signature');
    }
}
