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
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\TaoOntology;
use WikibaseSolutions\CypherDSL\Query;

/**
 * php -dmemory_limit=1G index.php 'oat\tao\scripts\tools\MigrateSqlToNeo4j' -u -i -s 10000 -n 10000 -vvv
 */
class MigrateSqlToNeo4j extends ScriptAction
{
    private const DEFAULT_CHUNK_SIZE = 10000;
    private ?\common_persistence_SqlPersistence $sqlAdapter = null;

    /**
     * @return \common_persistence_SqlPersistence
     */
    public function getSqlAdapter(): \common_persistence_SqlPersistence
    {
        if (!$this->sqlAdapter) {
            $sql = $this->getServiceLocator()
                ->get(PersistenceManager::SERVICE_ID)
                ->getPersistenceById('default');

            if (!$sql instanceof \common_persistence_SqlPersistence) {
                throw new \RuntimeException('Migration only supports SQL-based RDBMS as a source. '
                    . 'Please set your default persistence accordingly.');
            }

            $this->sqlAdapter = $sql;
        }
        return $this->sqlAdapter;
    }

    protected function showTime()
    {
        return true;
    }


    public function initNeo4j(bool $isCreateConstraint, bool $isInitGraphConfig)
    {
        $neo4j = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)->getPersistenceById('neo4j');

        if ($isCreateConstraint) {
            $this->logNotice('Creating unique constraint for Resource URI.');
            $neo4j->run('CREATE CONSTRAINT n10s_unique_uri FOR (r:Resource) REQUIRE r.uri IS UNIQUE;');
        }

        if ($isInitGraphConfig) {
            $this->logNotice('Init graph settings with proper options.');

            $multiValuePropertyList = $this->getMultiValuePropertyList();
            if (!empty($multiValuePropertyList)) {
                $multiValueSetting = sprintf('"%s"', implode('","', $multiValuePropertyList));
            } else {
                $multiValueSetting = '';
            }

            $this->logInfo(sprintf('Following multi-value properties found: %s', $multiValueSetting));
            $neo4j->run(<<<CYPHER
CALL n10s.graphconfig.init({
    handleMultival:"ARRAY",
    multivalPropList:[${multiValueSetting}],
    keepLangTag:true,
    handleVocabUris:"KEEP", 
    handleRDFTypes:"NODES"
});
CYPHER
            );
        }

        return $neo4j;
    }

    public function getMultiValuePropertyList(): array
    {
        $sql = $this->getSqlAdapter();

        $typePropertyUri = OntologyRdf::RDF_TYPE;
        $rangePropertyUri = OntologyRdfs::RDFS_RANGE;
        $multiplePropertyUri = GenerisRdf::PROPERTY_MULTIPLE;
        $languageDependentPropertyUri = GenerisRdf::PROPERTY_IS_LG_DEPENDENT;

        $propertyObjectUri = OntologyRdf::RDF_PROPERTY;
        $literalObjectUri = OntologyRdfs::RDFS_LITERAL;
        $trueObjectUri = GenerisRdf::GENERIS_TRUE;

        /** @var \Doctrine\DBAL\ForwardCompatibility\Result $result */
        $result = $sql->query(<<<SQL
SELECT s1.subject
FROM statements as s1
         LEFT JOIN statements as s2
                   ON (s1.subject = s2.subject
                       AND s2.predicate IN ('${multiplePropertyUri}',
                                            '${languageDependentPropertyUri}')
                       AND s2.object = '${trueObjectUri}')
         LEFT JOIN statements as s3
                   ON (s1.subject = s3.subject AND s3.predicate = '${rangePropertyUri}' AND
                       s3.object != '${literalObjectUri}')
WHERE s2.id IS NOT NULL
  AND s3.id is NULL
  AND s1.predicate = '${typePropertyUri}'
  AND s1.object = '${propertyObjectUri}';
SQL);

        return $result->fetchFirstColumn();
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

                $this->logDebug(sprintf('%d triple(s) transformed.', $triplesProcessed));

                $graph = new Graph();
                $triplesProcessed = 0;

                yield $triplesChunk;
            }
        }

        $this->logDebug(sprintf('%d triple(s) transformed.', $triplesProcessed));
        yield $graph->serialise($format);
    }

    public function extractDataFromSqlStorage(int $chunkSize): \Generator
    {
        $sql = $this->getSqlAdapter();

        $updatedByPropertyUri = TaoOntology::PROPERTY_UPDATED_BY;

        $dataProviderList = [
            'physical_triples' => [
                'id_query' => 'SELECT MAX(id) as id FROM "statements" GROUP BY subject, predicate, object, l_language;',
                'data_query' => 'SELECT subject, predicate, object, l_language FROM statements WHERE id IN(%s);',
            ],
            'virtual_author_triples' => [
                'id_query' => <<<'SQL'
                    SELECT MAX(s1.id) as id
                    FROM statements AS s1
                    INNER JOIN (SELECT subject, MAX(epoch) as epoch
                                FROM statements
                                WHERE author <> ''
                                GROUP BY subject) as s2
                        ON (s1.subject = s2.subject AND s1.epoch = s2.epoch)
                    GROUP BY s1.subject;
SQL,
                'data_query' => <<<SQL
                    SELECT
                        subject,
                        '${updatedByPropertyUri}' as predicate,
                        author AS object,
                        '' as l_language
                    FROM statements
                    WHERE id IN(%s);
SQL,
            ],
        ];

        foreach ($dataProviderList as $dataProviderName => $dataProvider) {
            $this->logNotice(sprintf('Extracting data using "%s" provider.', $dataProviderName));

            /** @var \Doctrine\DBAL\ForwardCompatibility\Result $idResult */
            $idResult = $sql->query($dataProvider['id_query']);
            $idList = $idResult->fetchFirstColumn();

            $this->logInfo(sprintf('Extracted %d row(s) of data.', count($idList)));
            $idList = array_chunk($idList, $chunkSize);
            foreach ($idList as $idChunk) {
                /** @var \Doctrine\DBAL\ForwardCompatibility\Result $result */
                $result = $sql->query(sprintf($dataProvider['data_query'], implode(',', $idChunk)));

                while ($r = $result->fetchAssociative()) {
                    yield $r;
                }
            }
        }
    }

    /**
     * @param $neo4j
     * @param string $nTriple
     * @param int $neo4jChunkSize
     *
     * @return void
     */
    public function loadNTripleToNeo4j($neo4j, string $nTriple, int $neo4jChunkSize): void
    {
        $result = $neo4j->run(
            <<<CYPHER
CALL n10s.rdf.import.inline(\$nTriple,"N-Triples",{commitSize:${neo4jChunkSize}}) YIELD terminationStatus, extraInfo
RETURN terminationStatus, extraInfo
CYPHER,
            ['nTriple' => $nTriple]
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

        $this->logInfo('Chunk of triples successfully loaded.');
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

            $nTripleList = $this->transformSqlToRdf(
                $sqlTripleList,
                Format::getFormat('ntriples'),
                $neo4jChunkSize
            );

            $neo4j = $this->initNeo4j($isCreateConstraint, $isInitGraphConfig);
            foreach ($nTripleList as $nTriple) {
                $this->loadNTripleToNeo4j($neo4j, $nTriple, $neo4jChunkSize);
            }

            $this->addSystemLabel($neo4j, $sqlChunkSize, $neo4jChunkSize);
        } catch (\Throwable $e) {
            return Report::createError($e->getMessage());
        }

        return Report::createSuccess('Data transfer finished successfully.');
    }

    private function addSystemLabel($neo4j, int $sqlChunkSize, int $neo4jChunkSize)
    {
        $sql = $this->getSqlAdapter();
        $nonSystemModelId = \core_kernel_persistence_smoothsql_SmoothModel::DEFAULT_WRITABLE_MODEL;

            /** @var \Doctrine\DBAL\ForwardCompatibility\Result $idResult */
        $result = $sql->query(<<<SQL
                SELECT subject
                    FROM statements
                    WHERE modelid <> {$nonSystemModelId}
                    GROUP BY subject;
SQL);

        $subjectList = [];
        while ($r = $result->fetchColumn()) {
            $subjectList[] = $r;

            if (count($subjectList) >= $neo4jChunkSize) {
                $systemNode = Query::node('Resource');
                $query = Query::new()->match($systemNode)
                    ->where($systemNode->property('uri')->in($subjectList))
                    ->set($systemNode->labeled('System'));
                $neo4j->runStatement($query);
                $subjectList = [];
            }
        }

        if (!empty($subjectList)) {
            $systemNode = Query::node('Resource');
            $query = Query::new()->match($systemNode)
                ->where($systemNode->property('uri')->in($subjectList))
                ->set($systemNode->labeled('System'));
            $neo4j->run($query->build());
        }
    }
}
