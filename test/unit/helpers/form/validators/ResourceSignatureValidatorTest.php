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

use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\validators\ResourceSignatureValidator;
use oat\tao\model\security\SecurityException;
use oat\tao\model\security\SignatureValidator;
use PHPUnit\Framework\MockObject\MockObject;

class ResourceSignatureValidatorTest extends TestCase
{
    /** @var SignatureValidator|MockObject */
    private $signatureValidator;

    /** @var ResourceSignatureValidator */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signatureValidator = $this->createMock(SignatureValidator::class);
        $this->subject = new ResourceSignatureValidator($this->signatureValidator, 'http://www.dot.com');
    }

    /**
     * @throws SecurityException
     * @throws \oat\tao\model\metadata\exception\InconsistencyConfigException
     */
    public function testEvaluate()
    {
        $this->assertTrue($this->subject->evaluate('signature'));
    }

    /**
     * @throws SecurityException
     * @throws \oat\tao\model\metadata\exception\InconsistencyConfigException
     */
    public function testNotSuccessfulEvaluate()
    {
        $this->expectException(SecurityException::class);

        $this->signatureValidator
            ->method('checkSignature')
            ->willThrowException(new SecurityException('exception message'));

        $this->subject->evaluate('signature');
    }
}
