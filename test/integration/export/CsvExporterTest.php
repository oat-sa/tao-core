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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\test\integration\export;

use GuzzleHttp\Psr7\Response;
use oat\tao\model\export\implementation\CsvExporter;
use common_session_SessionManager;
use oat\tao\test\TaoPhpUnitTestRunner;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;

/**
 * Test of `\oat\tao\model\session\SessionSubstitutionService`
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package generis\test
 */
class CsvExporterTest extends TaoPhpUnitTestRunner
{

    public function setUp(): void
    {
        parent::setUp();
        common_session_SessionManager::startSession(new \common_test_TestUserSession());
    }

    /**
     * @dataProvider dataProvider
     * @param SplFileInfo $file instance of sample file
     * @param array $data data to be exported
     * @param boolean $columnNames
     * @throws \common_exception_InvalidArgumentType
     */
    public function testExport(SplFileInfo $file, array $data, $columnNames)
    {
        $exporter = new CsvExporter($data);
        $exportedData = $this->normalizeLineEndings($exporter->export($columnNames));
        $sampleData = $this->normalizeLineEndings(file_get_contents($file->getPathname()));
        $this->assertEquals($exportedData, $sampleData);
    }

    /**
     * @dataProvider dataProvider
     * @param SplFileInfo $file instance of sample file
     * @param array $data data to be exported
     * @param boolean $columnNames
     * @throws \common_exception_InvalidArgumentType
     */
    public function testGetFileExportResponse(SplFileInfo $file, array $data, $columnNames)
    {
        $exporter = new CsvExporter($data);
        /** @var ResponseInterface $originResponse */
        $originResponse = (new Response())->withHeader('X-Old-Header', 'old_header_val');
        $response = $exporter->getFileExportResponse($originResponse, $columnNames);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $exportedData = $this->normalizeLineEndings($response->getBody()->getContents());
        $sampleData = $this->normalizeLineEndings(file_get_contents($file->getPathname()));
        $this->assertEquals($exportedData, $sampleData);

        $this->assertCount(4, $response->getHeaders());
        $this->assertEquals('old_header_val', $response->getHeader('X-Old-Header')[0]);
        $this->assertEquals(strlen($exportedData), $response->getHeader('Content-Length')[0]);
        $this->assertEquals(
            'attachment; fileName="' . CsvExporter::FILE_NAME . '"',
            $response->getHeader('Content-Disposition')[0]
        );
        $this->assertEquals(CsvExporter::CSV_CONTENT_TYPE, $response->getHeader('Content-Type')[0]);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        $samplesDir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;
        $withColNamesFile = new SplFileInfo($samplesDir . 'withColNames.csv');
        $withoutColNamesFile = new SplFileInfo($samplesDir . 'withoutColNames.csv');

        $csvWithColNames = array_map('str_getcsv', file($withColNamesFile->getPathname()));
        array_walk($csvWithColNames, function (&$a) use ($csvWithColNames) {
            $a = array_combine($csvWithColNames[0], $a);
        });
        array_shift($csvWithColNames); // remove column header

        return [
            [
                'file' => $withColNamesFile,
                'data' => $csvWithColNames,
                'columnNames' => true
            ],
            [
                'file' => $withoutColNamesFile,
                'data' => array_map('str_getcsv', file($withoutColNamesFile->getPathname())),
                'columnNames' => false
            ],
        ];
    }

    /**
     * Convert all line-endings to UNIX format
     * @param $s
     * @return mixed
     */
    private function normalizeLineEndings($s)
    {
        $s = str_replace("\r\n", "\n", $s);
        $s = str_replace("\r", "\n", $s);
        // Don't allow out-of-control blank lines
        $s = preg_replace("/\n{2,}/", "\n\n", $s);
        return $s;
    }
}
