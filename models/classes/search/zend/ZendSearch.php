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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\search\zend;

use oat\tao\model\search\Search;
use tao_models_classes_FileSourceService;
use common_Logger;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Search\QueryHit;

/**
 * Zend Lucene Search implementation 
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class ZendSearch implements Search
{	
    /**
     * 
     * @var \core_kernel_fileSystem_FileSystem
     */
    private $fileSystem;
    
    /**
     * 
     * @var \ZendSearch\Lucene\SearchIndexInterface
     */
    private $index;
    
    public function __construct($fileSystemId) {
        $this->fileSystem = tao_models_classes_FileSourceService::singleton()->getFileSource($fileSystemId);
        $this->index = Lucene::open($this->fileSystem->getPath());
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::query()
     */
    public function query($queryString) {
        $hits = $this->index->find($queryString);
        
        $ids = array();
        foreach ($hits as $hit) {
            $ids[] = $hit->getDocument()->getField('uri')->getUtf8Value();
        }
        
        \common_Logger::d('found '.count($ids));
        
        return $ids;
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::index()
     */
    public function index($resourceUris) {
        
        Lucene::create($this->fileSystem->getPath());
        // hardcoded item indexing
        foreach ($resourceUris as $uri) {
            $item = new \core_kernel_classes_Resource($uri);
            
            $indexer = new ZendIndexer($item);
            $doc = $indexer->toDocument();

            $this->index->addDocument($indexer->toDocument());
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\PhpSerializable::__toPhpCode()
     */
    public function __toPhpCode() {
        return 'new oat\\tao\\model\\search\\zend\\ZendSearch(\''.$this->fileSystem->getUri().'\')';
    }
    
    /**
     * 
     * @return \oat\tao\model\search\zend\ZendSearch
     */
    public static function createSearch() {
        $privateDataPath = FILES_PATH.'tao'.DIRECTORY_SEPARATOR.'ZendSearch'.DIRECTORY_SEPARATOR;
        
        if (file_exists($privateDataPath)) {
            helpers_File::emptyDirectory($privateDataPath);
        }
        $privateFs = \tao_models_classes_FileSourceService::singleton()->addLocalSource('Zend Search index folder', $privateDataPath);
        Lucene::create($privateDataPath);
        return new ZendSearch($privateFs->getUri());
    }
}