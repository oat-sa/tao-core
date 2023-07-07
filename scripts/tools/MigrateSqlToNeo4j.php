<?php

/*
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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use EasyRdf\Format;
use EasyRdf\Graph;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;

/**
 * php -dmemory_limit=1G index.php 'oat\tao\scripts\tools\MigrateSqlToNeo4j' -u -i -s 10000 -n 10000
 */
class MigrateSqlToNeo4j extends ScriptAction
{
    private const DEFAULT_CHUNK_SIZE = 10000;

    public function initNeo4j(bool $isCreateConstraint, bool $isInitGraphConfig)
    {
        $neo4j = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById('neo4j');

        if ($isCreateConstraint) {
            $neo4j->run('CREATE CONSTRAINT n10s_unique_uri FOR (r:Resource) REQUIRE r.uri IS UNIQUE;');
        }

        if ($isInitGraphConfig) {
            $neo4j->run(<<<CYPHER
CALL n10s.graphconfig.init({handleMultival:"ARRAY", handleVocabUris:"KEEP", handleRDFTypes:"NODES", keepLangTag:true});
CYPHER
            );
        }

        return $neo4j;
    }

    /**
     * @param \Traversable $tripleRowList
     * @param Format $format
     * @param int $chunkSize
     *
     * @return string[]
     */
    public function transformSqlToRdf(\Traversable $tripleRowList, Format $format, int $chunkSize): \Generator
    {
        $graph = new Graph();
        $triplesProcessed = 0;

        foreach ($tripleRowList as $tripleRow) {
            if (!empty($tripleRow['l_language'])) {
                $graph->addLiteral(
                    $tripleRow['subject'],
                    $tripleRow['predicate'],
                    $tripleRow['object'],
                    $tripleRow['l_language']
                );
            } elseif (\common_Utils::isUri($tripleRow['object'])) {
                $graph->addResource($tripleRow['subject'], $tripleRow['predicate'], $tripleRow['object']);
            } else {
                $graph->addLiteral($tripleRow['subject'], $tripleRow['predicate'], $tripleRow['object']);
            }

            $triplesProcessed++;
            if ($triplesProcessed >= $chunkSize) {
                $triplesChunk = $graph->serialise($format);
                $graph = new Graph();
                $triplesProcessed = 0;

                yield $triplesChunk;
            }
        }

        yield $graph->serialise($format);
    }

    public function extractDataFromSqlStorage(int $chunkSize): \Generator
    {
        $sql = $this->getServiceLocator()
            ->get(\common_persistence_Manager::SERVICE_ID)
            ->getPersistenceById('default');

        /** @var \Doctrine\DBAL\ForwardCompatibility\Result $idResult */
        $idResult = $sql->query(
            'SELECT MAX(id) as id FROM "statements" GROUP BY subject, predicate, object, l_language'
        );
        $idList = $idResult->fetchFirstColumn();

        $idList = array_chunk($idList, $chunkSize);
        foreach ($idList as $idChunk) {
            /** @var \Doctrine\DBAL\ForwardCompatibility\Result $result */
            $result = $sql->query(sprintf(
                'SELECT "subject", "predicate", "object", "l_language" FROM "statements" WHERE id IN(%s)',
                implode(',', $idChunk)
            ));

            while ($r = $result->fetchAssociative()) {
                yield $r;
            }
        }
    }

    /**
     * @param $nTriple
     * @param $neo4j
     *
     * @return void
     */
    public function loadNTripleToNeo4j($neo4j, string $nTriple): void
    {
        $nTriple = $this->escapeTriple($nTriple);

        $result = $neo4j->run(<<<CYPHER
CALL n10s.rdf.import.inline('${nTriple}',"N-Triples") YIELD terminationStatus, extraInfo
RETURN terminationStatus, extraInfo
CYPHER
        );

        $responseMessage = $result->first();
        if (!empty($responseMessage->get('extraInfo'))) {
            throw new \RuntimeException(
                sprintf(
                    'Loading line to Neo4j failed: %s.',
                    $responseMessage->get('extraInfo')
                )
            );
        }
    }

    public function escapeTriple(string $nTriple): string
    {
        $escapeCharacters = [
            '\\\\' => '\\\\\\\\', //Escape double slash
            '\"' => '\\\\"', // Escaped slash in escaped double quote
            '\n' => '\\\\n', // Escaped slash in EOL
            "'" => "\'", //Escape single quote
        ];

        $escapeList = [];
        foreach ($escapeCharacters as $needle => $replacement) {
            if (strpos($nTriple, $needle) !== false) {
                $escapeList[$needle] = $replacement;
            }
        }

        if (!empty($escapeList)) {
            $nTriple = str_replace(array_keys($escapeList), array_values($escapeList), $nTriple);
        }

        return $nTriple;
    }

    protected function provideOptions(): array
    {
        return [
            'sql-chunk-size' => [
                'prefix' => 's',
                'longPrefix' => 'sql-chunk-size',
                'required' => false,
                'description' => 'Number of rows fetched from SQL storage per request.',
                'cast' => 'int',
                'defaultValue' => self::DEFAULT_CHUNK_SIZE,
            ],
            'neo4j-chunk-size' => [
                'prefix' => 'n',
                'longPrefix' => 'neo4j-chunk-size',
                'required' => false,
                'description' => 'Number of triples uploaded to Neo4j per request.',
                'cast' => 'int',
                'defaultValue' => self::DEFAULT_CHUNK_SIZE,
            ],
            'create-unique-constraint' => [
                'prefix' => 'u',
                'longPrefix' => 'create-unique-constraint',
                'required' => false,
                'description' => 'Initialize Neo4j Database with unique constraint required for import. '
                               . 'Should be done only once, so do it only for new database.',
                'flag' => true,
                'defaultValue' => false,
            ],
            'init-graph-config' => [
                'prefix' => 'i',
                'longPrefix' => 'init-graph-config',
                'required' => false,
                'description' => 'Initialize Neo4j graph config with required values. '
                               . 'Should be done on empty database.',
                'flag' => true,
                'defaultValue' => false,
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'This script exports data from SQL storage, '
             . 'converts it to one of the N-Triples format and uploads resulted structure to Neo4j';
    }

    protected function run(): Report
    {
        $sqlChunkSize = max(1, (int)$this->getOption('sql-chunk-size'));
        $neo4jChunkSize = max(1, (int)$this->getOption('neo4j-chunk-size'));
        $isCreateConstraint = (bool)$this->getOption('create-unique-constraint');
        $isInitGraphConfig = (bool)$this->getOption('init-graph-config');

        try {
            $sqlTripleList = $this->extractDataFromSqlStorage($sqlChunkSize);
            $format = Format::getFormat('ntriples');
            $nTripleList = $this->transformSqlToRdf($sqlTripleList, $format, $neo4jChunkSize);

            $neo4j = $this->initNeo4j($isCreateConstraint, $isInitGraphConfig);
            foreach ($nTripleList as $nTriple) {
                $this->loadNTripleToNeo4j($neo4j, $nTriple);
            }
        } catch (\Throwable $e) {
            return Report::createError($e->getMessage());
        }

        return Report::createSuccess('Data transfer finished successfully.');
    }
}
