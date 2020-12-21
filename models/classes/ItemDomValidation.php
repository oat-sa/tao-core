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

namespace oat\tao\model;

use DOMNode;
use DOMDocument;
use oat\oatbox\service\ServiceManager;
use oat\generis\persistence\PersistenceManager;
use common_persistence_KeyValuePersistence as KeyValuePersistence;

class ItemDomValidation
{
    /** @var KeyValuePersistence */
    private $kvStorage;

    /** @var DOMNode|null */
    private $assessmentItem;

    /** @var bool */
    private $containsAssessmentItem;

    /** @var string */
    private $validationKey;

    /** @var string */
    private $hashedContent;

    public function __construct(
        DOMDocument $dom,
        string $schema,
        string $content,
        ?KeyValuePersistence $kvStorage = null
    ) {
        $this->kvStorage = $kvStorage ?? $this->getPersistenceManager()->getPersistenceById('default_kv');

        $this->assessmentItem = $dom->getElementsByTagName('assessmentItem')->item(0);
        $this->containsAssessmentItem = $this->assessmentItem !== null;

        $this->validationKey = $this->createValidationKey($schema);
        $this->hashedContent = md5($this->removeResponseProcessingNode($content));
    }

    public function needValidation(): bool
    {
        return !$this->containsAssessmentItem
            || !$this->hasValidatedContent()
            || $this->getValidatedContent() !== $this->hashedContent;
    }

    public function getValidationResult(): bool
    {
        return (bool) $this->kvStorage->get($this->validationKey . '.result');
    }

    public function setValidationResult(bool $isValid): void
    {
        if ($this->containsAssessmentItem) {
            $this->kvStorage->set($this->validationKey . '.content', $this->hashedContent);
            $this->kvStorage->set($this->validationKey . '.result', $isValid);
        }
    }

    private function getPersistenceManager(): PersistenceManager
    {
        return ServiceManager::getServiceManager()->get(PersistenceManager::SERVICE_ID);
    }

    private function createValidationKey(string $schema): ?string
    {
        if ($this->containsAssessmentItem) {
            $validationKey = md5($schema . $this->assessmentItem->getAttribute('identifier'));
        }

        return $validationKey ?? '';
    }

    /**
     * When we are trying to update (save) item - we validate DOM content before processing and this content doesn't
     * contain <responseProcessing/> node.
     * When we are trying to preview item/booklet/etc. - we validate processed DOM content, that already contains it.
     * In this case we need to remove this node to make stored content and validated content similar.
     */
    private function removeResponseProcessingNode(string $content): string
    {
        return preg_replace('/\s+<responseProcessing\/>/', '', $content);
    }

    private function hasValidatedContent(): bool
    {
        return $this->kvStorage->exists($this->validationKey . '.content');
    }

    private function getValidatedContent(): string
    {
        return (string) $this->kvStorage->get($this->validationKey . '.content');
    }
}
