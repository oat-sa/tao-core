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
use oat\oatbox\service\ConfigurableService;
use oat\generis\persistence\PersistenceManager;
use common_persistence_KeyValuePersistence as KeyValuePersistence;

class DomValidator extends ConfigurableService
{
    public const OPTION_KV_PERSISTENCE = 'kv_persistence';
    public const OPTION_KV_TTL = 'kv_ttl';

    /** @var array */
    private $errors = [];

    /** @var DOMNode|null */
    private $assessmentItem;

    /** @var string */
    private $validationKey;

    /** @var string */
    private $hashedContent;

    /** @var KeyValuePersistence */
    private $keyValuePersistence;

    public function validate(string $content, string $schema): bool
    {
        $this->resetProperties();
        libxml_use_internal_errors(true);

        $dom = $this->createDomDocument();
        $isValid = $dom->loadXML($content);

        $this->assessmentItem = $this->getAssessmentItem($dom);
        $this->validationKey = $this->createValidationKey($schema);
        $this->hashedContent = $this->processAndHashContent(clone $dom);

        if ($isValid && !empty($schema)) {
            if ($this->needValidation()) {
                $isValid = $dom->schemaValidate($schema);
                $this->setValidationResult($isValid);
            } else {
                $isValid = $this->getValidationResult();

                if ($isValid === false) {
                    $errors = $this->getValidationErrors();
                }
            }
        }

        if (!$isValid) {
            $this->errors = $errors ?? libxml_get_errors();
        }

        libxml_clear_errors();

        return $isValid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function resetProperties(): void
    {
        $this->errors = [];
        unset($this->assessmentItem, $this->validationKey, $this->hashedContent);
    }

    private function createDomDocument(): DOMDocument
    {
        $dom = new DomDocument();
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;

        return $dom;
    }

    private function getAssessmentItem(DOMDocument $dom): ?DOMNode
    {
        return $dom->getElementsByTagName('assessmentItem')->item(0);
    }

    private function createValidationKey(string $schema): ?string
    {
        if ($this->assessmentItem !== null) {
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
    private function processAndHashContent(DOMDocument $dom): string
    {
        $responseProcessing = $dom->getElementsByTagName('responseProcessing')->item(0);

        if ($responseProcessing !== null) {
            $responseProcessing->parentNode->removeChild($responseProcessing);
        }

        return md5($dom->saveXML());
    }

    private function needValidation(): bool
    {
        return $this->assessmentItem === null
            || !$this->hasValidatedContent($this->validationKey)
            || $this->getValidatedContent($this->validationKey) !== $this->hashedContent;
    }

    private function hasValidatedContent(string $validationKey): bool
    {
        return $this->getKeyValuePersistence()->exists($validationKey . '.content');
    }

    private function getValidatedContent(string $validationKey): string
    {
        return (string) $this->getKeyValuePersistence()->get($validationKey . '.content');
    }

    private function getKeyValuePersistence(): KeyValuePersistence
    {
        if (!isset($this->keyValuePersistence)) {
            $this->keyValuePersistence = $this->getOption(self::OPTION_KV_PERSISTENCE);

            if ($this->keyValuePersistence === null || !$this->keyValuePersistence instanceof KeyValuePersistence) {
                $this->keyValuePersistence = $this->getPersistenceManager()->getPersistenceById(
                    'default_kv'
                );
            }
        }

        return $this->keyValuePersistence;
    }

    private function getPersistenceManager(): PersistenceManager
    {
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
    }

    private function setValidationResult(bool $isValid): void
    {
        if ($this->assessmentItem !== null) {
            $ttl = $this->getKeyValuePersistenceTTL();

            $this->getKeyValuePersistence()->set($this->validationKey . '.content', $this->hashedContent, $ttl);
            $this->getKeyValuePersistence()->set($this->validationKey . '.result', $isValid, $ttl);

            if ($isValid === false) {
                $this->getKeyValuePersistence()->set(
                    $this->validationKey . '.errors',
                    json_encode(libxml_get_errors()),
                    $ttl
                );
            }
        }
    }

    private function getKeyValuePersistenceTTL(): ?int
    {
        $ttl = $this->getOption(self::OPTION_KV_TTL);

        return is_numeric($ttl) ? (int) $ttl : null;
    }

    private function getValidationResult(): bool
    {
        return (bool) $this->getKeyValuePersistence()->get($this->validationKey . '.result');
    }

    private function getValidationErrors(): array
    {
        return json_decode(
            $this->getKeyValuePersistence()->get($this->validationKey . '.errors'),
            true
        );
    }
}
