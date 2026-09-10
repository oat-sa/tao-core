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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

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
        // Pass 1: collect empty-lang baselines (needed before deciding deletes).
        $baselines = [];
        $afterId = 0;
        while (($rows = $this->fetchMatchingLiteralBatch($afterId)) !== []) {
            $afterId = (int) $rows[array_key_last($rows)]['id'];
            foreach ($rows as $row) {
                if ((string) ($row['l_language'] ?? '') !== '') {
                    continue;
                }
                $baselines[$row['subject'] . "\0" . $row['predicate']] = (string) $row['object'];
            }
        }

        // Pass 2: page en-US/empty matches again and delete polluted en-US rows per batch.
        $deleted = 0;
        $afterId = 0;
        while (($rows = $this->fetchMatchingLiteralBatch($afterId)) !== []) {
            $afterId = (int) $rows[array_key_last($rows)]['id'];
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

            if ($idsToDelete === []) {
                continue;
            }

            $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
            $this->connection->executeStatement(
                "DELETE FROM statements WHERE id IN ({$placeholders})",
                $idsToDelete
            );
            $deleted += count($idsToDelete);
        }

        return $deleted;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchMatchingLiteralBatch(int $afterId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT id, subject, predicate, object, l_language
             FROM statements
             WHERE predicate IN (?, ?)
               AND (l_language = ? OR l_language IS NULL OR l_language = ?)
               AND id > ?
             ORDER BY id ASC
             LIMIT ' . self::DELETE_CHUNK_SIZE,
            [
                OntologyRdfs::RDFS_LABEL,
                OntologyRdfs::RDFS_COMMENT,
                'en-US',
                '',
                $afterId,
            ]
        );
    }
}
