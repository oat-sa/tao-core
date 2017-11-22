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
 * Copyright (c) 2017  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\scripts\tools\rdf;

use common_report_Report as Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\AbstractAction;


/**
 * Counts all duplicates of the rdf properties
 *   [properties]
 *      -- fix - delete all duplicate rows from the statements table
 *          (duplicate row - this is row which has same fields: modelid, subject, predicate, object, l_language)
 *
 * To quickly delete all duplicates can be used SQL query:
 * ```
 * DELETE FROM statements s where s.id IN (SELECT s1.id FROM statements s1
 *       LEFT OUTER JOIN (
 *           SELECT MIN(s2.id) as id, s2.modelid, s2.subject, s2.predicate, s2.object, s2.l_language
 *           FROM statements s2
 *           GROUP BY s2.modelid, s2.subject, s2.predicate, s2.object, s2.l_language
 *       ) as KeepRows ON s1.id = KeepRows.id
 *       WHERE KeepRows.id IS NULL)
 * ```
 *
 * Class FindDuplicates
 * @package oat\taoNccer\scripts\tools
 *
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\rdf\RdfDuplicates' [--fix]
 *
 * for Windows:
 * php index.php "oat\tao\scripts\tools\rdf\RdfDuplicates"
 */
class RdfDuplicates extends AbstractAction
{
    use OntologyAwareTrait;

    private $persistence;

    /**
     * @param $params
     * @return Report
     */
    public function __invoke($params)
    {
        $fixed = 0;
        $report = Report::createInfo('Duplication searching');

        $sql = 'SELECT COUNT(*) FROM statements s JOIN statements s1 ON s.subject=s1.subject AND s.id!=s1.id
                  AND s.predicate=s1.predicate AND s.object=s1.object AND s.l_language=s1.l_language AND s.modelid=s1.modelid';
        $stmt = $this->getPersistence()->query($sql);
        $total = $stmt->fetchAll()[0]['count'];
        if (!$total) {
            $report->add(Report::createSuccess('Duplicates not found. Everything is fine!'));
        } else {

            if (isset($params[0]) && $params[0] == '--fix') {
                do {
                    $sql = 'SELECT s.id AS source FROM statements s JOIN statements s1 ON s.subject=s1.subject AND s.id!=s1.id
                      AND s.predicate=s1.predicate AND s.object=s1.object AND s.l_language=s1.l_language AND s.modelid=s1.modelid LIMIT 1';
                    $stmt = $this->getPersistence()->query($sql);
                    $res = $stmt->fetchAll();
                    $id = 0;
                    if (isset($res[0]) && isset($res[0]['source'])) {
                        $id = $res[0]['source'];
                    }
                    if ($id) {
                        $sql = 'SELECT s1.id FROM statements s JOIN statements s1 ON s.subject=s1.subject AND s.id!=s1.id
                            AND s.predicate=s1.predicate AND s.object=s1.object AND s.l_language=s1.l_language AND s.modelid=s1.modelid
                          WHERE s.id=?';
                        $stmt = $this->getPersistence()->query($sql, [$id]);
                        $duplicates = array_column($stmt->fetchAll(), 'id');

                        if (count($duplicates)) {
                            $sql = 'DELETE FROM statements WHERE id IN (' . implode(',', $duplicates) . ')';
                            $this->getPersistence()->exec($sql);
                            $fixed++;
                        }
                    }

                } while ($id);
            }

            if (!$fixed) {
                $report->add(Report::createInfo(sprintf('%s duplicates were found', $total)));
                $report->add(Report::createInfo('Use --fix parameter to delete duplicates'));
           } else {
                $report->add(Report::createSuccess(sprintf('fixed %s, deleted %s', $total, $fixed)));
            }
        }
        return $report;
    }

    private function getPersistence()
    {
        if (!$this->persistence) {
            $this->persistence = $this->getServiceLocator()
                ->get(\common_persistence_Manager::SERVICE_ID)
                ->getPersistenceById('default');
        }

        return $this->persistence;
    }
}
