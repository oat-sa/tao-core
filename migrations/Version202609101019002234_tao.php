<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * Fix Crowdin RDF translations wrongly stored as en-US after pluralization PO reader regression.
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202609101019002234_tao extends AbstractMigration
{
    private const DELETE_CHUNK_SIZE = 500;

    public function getDescription(): string
    {
        return 'Remove RDF label/comment triples wrongly tagged as en-US and re-sync ontology locales'
            . ' (irreversible down)';
    }

    public function up(Schema $schema): void
    {
        $deleted = $this->deleteWronglyTaggedEnUsRdfLiterals();
        OntologyUpdater::syncModels();

        $this->addReport(Report::createSuccess(
            sprintf('Deleted %d polluted en-US RDF literals and synced models', $deleted)
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addReport(Report::createInfo(
            'Irreversible: polluted en-US RDF literals were deleted; re-sync after code fix if needed'
        ));
    }

    private function deleteWronglyTaggedEnUsRdfLiterals(): int
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, subject, predicate, object, l_language
             FROM statements
             WHERE predicate IN (?, ?)
               AND (l_language = ? OR l_language IS NULL OR l_language = ?)',
            [
                OntologyRdfs::RDFS_LABEL,
                OntologyRdfs::RDFS_COMMENT,
                'en-US',
                '',
            ]
        );

        $baselines = [];
        foreach ($rows as $row) {
            $lang = (string) ($row['l_language'] ?? '');
            if ($lang !== '') {
                continue;
            }
            $baselines[$row['subject'] . "\0" . $row['predicate']] = (string) $row['object'];
        }

        $idsToDelete = [];
        foreach ($rows as $row) {
            if ((string) ($row['l_language'] ?? '') !== 'en-US') {
                continue;
            }

            $object = (string) $row['object'];
            $key = $row['subject'] . "\0" . $row['predicate'];
            $hasNonAscii = (bool) preg_match('/[^\x00-\x7F]/u', $object);
            $differsFromBaseline = isset($baselines[$key]) && $baselines[$key] !== $object;

            if ($hasNonAscii || $differsFromBaseline) {
                $idsToDelete[] = $row['id'];
            }
        }

        foreach (array_chunk($idsToDelete, self::DELETE_CHUNK_SIZE) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $this->connection->executeStatement(
                "DELETE FROM statements WHERE id IN ({$placeholders})",
                $chunk
            );
        }

        return count($idsToDelete);
    }
}
