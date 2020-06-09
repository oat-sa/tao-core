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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index\DocumentBuilder;

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\search\index\IndexDocument;

abstract class AbstractIndexDocumentBuilder implements IndexDocumentBuilderInterface
{
    use OntologyAwareTrait;
    
    /**
     * {@inheritdoc}
     */
    public function createDocumentFromArray(array $resource = []): IndexDocument
    {
        if (!isset($resource['id'])) {
            throw new \common_exception_MissingParameter('id');
        }
    
        if (!isset($resource['body'])) {
            throw new \common_exception_MissingParameter('body');
        }
    
        $body = $resource['body'];
        $indexProperties = [];
    
        if (isset($resource['indexProperties'])) {
            $indexProperties = $resource['indexProperties'];
        }
    
        $document = new IndexDocument(
            $resource['id'],
            $body,
            $indexProperties
        );
    
        return $document;
    }
}
