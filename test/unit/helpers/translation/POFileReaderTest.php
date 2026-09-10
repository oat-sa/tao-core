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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers\translation;

use common_ext_Extension;
use common_persistence_InMemoryKvDriver;
use common_persistence_KeyValuePersistence;
use core_kernel_classes_Triple;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\translation\rdf\RdfPack;
use oat\tao\model\service\ApplicationService;
use PHPUnit\Framework\TestCase;
use tao_helpers_translation_POFileReader;

/**
 * @package tao
 */
class POFileReaderTest extends TestCase
{
    private string $tmpRoot;
    private ?ServiceManager $previousServiceManager = null;

    protected function setUp(): void
    {
        if (!defined('DEFAULT_LANG')) {
            define('DEFAULT_LANG', 'en-US');
        }

        $this->previousServiceManager = ServiceManager::getServiceManager();
        $config = new common_persistence_KeyValuePersistence(new common_persistence_InMemoryKvDriver(), []);
        $applicationService = $this->createMock(ApplicationService::class);
        $applicationService->method('getDefaultEncoding')->willReturn('UTF-8');
        $config->set(ApplicationService::SERVICE_ID, $applicationService);
        ServiceManager::setServiceManager(new ServiceManager($config));

        $this->tmpRoot = sys_get_temp_dir() . '/tao-po-reader-' . uniqid('', true);
        mkdir($this->tmpRoot);
    }

    protected function tearDown(): void
    {
        if ($this->previousServiceManager !== null) {
            ServiceManager::setServiceManager($this->previousServiceManager);
        }
        $this->removeTree($this->tmpRoot);
    }

    public function testCrowdinHeaderUsesLocaleFolderAsTargetLanguage(): void
    {
        $poPath = $this->writePo(
            'ja-JP',
            'foo.rdf.po',
            <<<'PO'
msgid ""
msgstr ""
"sourceLanguage: en-US\n"
"targetLanguage: en-US\n"

# http://www.tao.lu/Ontologies/TAOItem.rdf#Item
msgctxt "http://www.w3.org/2000/01/rdf-schema#label"
msgid "Item"
msgstr "アイテム"
PO
        );

        $reader = new tao_helpers_translation_POFileReader($poPath);
        $reader->read();
        $tf = $reader->getTranslationFile();
        $tus = $tf->getTranslationUnits();

        $this->assertSame('ja-JP', $tf->getTargetLanguage());
        $this->assertCount(1, $tus);
        $this->assertSame('ja-JP', $tus[0]->getTargetLanguage());
        $this->assertSame('アイテム', $tus[0]->getTarget());
    }

    public function testCorrectTargetLanguageHeaderIsPreserved(): void
    {
        $poPath = $this->writePo(
            'zh-TW',
            'foo.rdf.po',
            <<<'PO'
msgid ""
msgstr ""
"sourceLanguage: en-US\n"
"targetLanguage: zh-TW\n"

msgctxt "http://www.w3.org/2000/01/rdf-schema#label"
msgid "Item"
msgstr "題目"
PO
        );

        $reader = new tao_helpers_translation_POFileReader($poPath);
        $reader->read();
        $tf = $reader->getTranslationFile();
        $tus = $tf->getTranslationUnits();

        $this->assertSame('zh-TW', $tf->getTargetLanguage());
        $this->assertCount(1, $tus);
        $this->assertSame('zh-TW', $tus[0]->getTargetLanguage());
    }

    public function testPluralEntriesAreParsed(): void
    {
        $poPath = $this->writePo(
            'sk-SK',
            'messages.po',
            <<<'PO'
msgid ""
msgstr ""
"sourceLanguage: en-US\n"
"targetLanguage: sk-SK\n"
"Plural-Forms: nplurals=4; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3;\n"

msgid "%d day"
msgid_plural "%d days"
msgstr[0] "%d den"
msgstr[1] "%d dni"
msgstr[2] "%d dni"
msgstr[3] "%d dni"
PO
        );

        $reader = new tao_helpers_translation_POFileReader($poPath);
        $reader->read();
        $tus = $reader->getTranslationFile()->getTranslationUnits();

        $this->assertCount(1, $tus);
        $this->assertSame('%d days', $tus[0]->getSourcePlural());
        $this->assertSame('%d den', $tus[0]->getTargetByIndex(0));
        $this->assertSame('%d dni', $tus[0]->getTargetByIndex(3));
        $this->assertSame('sk-SK', $tus[0]->getTargetLanguage());
    }

    public function testRdfPackUsesConstructorLangCodeForTripleLanguage(): void
    {
        $poPath = $this->writePo(
            'hu-HU',
            'item.rdf.po',
            <<<'PO'
msgid ""
msgstr ""
"sourceLanguage: en-US\n"
"targetLanguage: en-US\n"

# http://www.tao.lu/Ontologies/TAOItem.rdf#Item
msgctxt "http://www.w3.org/2000/01/rdf-schema#label"
msgid "Item"
msgstr "Elem"
PO
        );

        $extension = $this->createMock(common_ext_Extension::class);
        $pack = new class ('hu-HU', $extension) extends RdfPack {
            public function exposeTriplesFromFile(string $file, int $modelId): \ArrayIterator
            {
                return $this->getTriplesFromFile($file, $modelId);
            }
        };

        $triples = iterator_to_array($pack->exposeTriplesFromFile($poPath, 1));

        $this->assertCount(1, $triples);
        /** @var core_kernel_classes_Triple $triple */
        $triple = $triples[0];
        $this->assertSame('hu-HU', $triple->lg);
        $this->assertSame(OntologyRdfs::RDFS_LABEL, $triple->predicate);
        $this->assertSame('Elem', $triple->object);
    }

    private function writePo(string $locale, string $filename, string $content): string
    {
        $dir = $this->tmpRoot . '/locales/' . $locale;
        mkdir($dir, 0777, true);
        $path = $dir . '/' . $filename;
        file_put_contents($path, $content);

        return $path;
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $full = $path . '/' . $entry;
            if (is_dir($full)) {
                $this->removeTree($full);
            } else {
                unlink($full);
            }
        }
        rmdir($path);
    }
}
