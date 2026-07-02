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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\i18n;

use oat\oatbox\service\ServiceManager;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\i18n\TranslationBundleProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use tao_models_classes_LanguageService;

class LanguageServiceTranslationBundleProcessingTest extends TestCase
{
    public function testApplyTranslationBundleProcessingDelegatesToProcessor(): void
    {
        $translations = ['Hello' => '{r}x{/r}'];
        $expected = ['Hello' => '<ruby>x</ruby>'];

        /** @var TranslationBundleProcessorInterface|MockObject $processor */
        $processor = $this->createMock(TranslationBundleProcessorInterface::class);
        $processor->expects($this->once())
            ->method('process')
            ->with($translations, 'en-US')
            ->willReturn($expected);

        $sut = $this->createLanguageServiceWithMockLocator($processor);

        $this->assertSame($expected, $sut->exposeApplyTranslationBundleProcessing($translations, 'en-US'));
    }

    public function testApplyTranslationBundleProcessingReturnsInputWhenServiceMissing(): void
    {
        $translations = ['a' => 'b'];

        $sm = $this->createMock(ServiceManager::class);
        $sm->method('get')
            ->with(TranslationBundleProcessorInterface::SERVICE_ID)
            ->willThrowException(new ServiceNotFoundException(TranslationBundleProcessorInterface::SERVICE_ID));

        $sut = new LanguageServiceTranslationBundleTestDouble($sm);

        $this->assertSame($translations, $sut->exposeApplyTranslationBundleProcessing($translations, 'en-US'));
    }

    public function testApplyTranslationBundleProcessingReturnsInputWhenServiceHasWrongType(): void
    {
        $translations = ['a' => 'b'];

        $sm = $this->createMock(ServiceManager::class);
        $sm->method('get')
            ->with(TranslationBundleProcessorInterface::SERVICE_ID)
            ->willReturn(new stdClass());

        $sut = new LanguageServiceTranslationBundleTestDouble($sm);

        $this->assertSame($translations, $sut->exposeApplyTranslationBundleProcessing($translations, 'en-US'));
    }

    public function testApplyTranslationBundleProcessingReturnsInputWhenProcessThrows(): void
    {
        $translations = ['a' => 'b'];

        /** @var TranslationBundleProcessorInterface|MockObject $processor */
        $processor = $this->createMock(TranslationBundleProcessorInterface::class);
        $processor->expects($this->once())
            ->method('process')
            ->with($translations, 'en-US')
            ->willThrowException(new \RuntimeException('processor boom'));

        $sut = $this->createLanguageServiceWithMockLocator($processor);

        $this->assertSame($translations, $sut->exposeApplyTranslationBundleProcessing($translations, 'en-US'));
    }

    private function createLanguageServiceWithMockLocator(
        TranslationBundleProcessorInterface $processor
    ): LanguageServiceTranslationBundleTestDouble {
        $sm = $this->createMock(ServiceManager::class);
        $sm->method('get')
            ->with(TranslationBundleProcessorInterface::SERVICE_ID)
            ->willReturn($processor);

        return new LanguageServiceTranslationBundleTestDouble($sm);
    }
}

/**
 * @internal
 */
final class LanguageServiceTranslationBundleTestDouble extends tao_models_classes_LanguageService
{
    public function __construct(private ServiceManager $serviceManager)
    {
        parent::__construct();
    }

    public function getServiceLocator()
    {
        return $this->serviceManager;
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, mixed>
     */
    public function exposeApplyTranslationBundleProcessing(array $translations, string $langCode): array
    {
        return $this->applyTranslationBundleProcessing($translations, $langCode);
    }
}
