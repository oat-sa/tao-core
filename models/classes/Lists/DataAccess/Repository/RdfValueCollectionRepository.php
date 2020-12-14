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
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use common_exception_Error;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use Throwable;
use oat\generis\model\OntologyAwareTrait;

class RdfValueCollectionRepository extends InjectionAwareService implements ValueCollectionRepositoryInterface
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ValueCollectionRepository';

    public function isApplicable(string $collectionUri): bool
    {
        return CollectionType::fromCollectionUri($collectionUri)->equals(CollectionType::default());
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        $listClass = $this->getList($searchRequest);
        $values = [];
        foreach ($listClass->getInstances() as $resource) {
            $values[] = new Value(null, $resource->getUri(), $resource->getLabel());
        }

        return new ValueCollection($listClass->getUri(), ...$values);
    }

    public function persist(ValueCollection $valueCollection): bool
    {
        if ($valueCollection->hasDuplicates()) {
            throw new ValueConflictException("Value Collection {$valueCollection->getUri()} has duplicate values.");
        }

        $platform = $this->getPersistence()->getPlatForm();

        $platform->beginTransaction();

        try {
            foreach ($valueCollection as $value) {
                $this->verifyUriUniqueness($value);

                if (null === $value->getId()) {
                    $this->insert($valueCollection, $value);
                } else {
                    $this->update($value);
                }
            }

            $platform->commit();

            return true;
        } catch (ValueConflictException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            return false;
        } finally {
            if (isset($exception)) {
                $platform->rollBack();
            }
        }
    }

    /**
     * @param string $valueCollectionUri
     *
     * @throws common_exception_Error
     */
    public function delete(string $valueCollectionUri): void
    {
        $listClass = $this->getClass($valueCollectionUri);

        $listItems = $listClass->getInstances(false);

        foreach ($listItems as $listItem) {
            $listItem->delete();
        }
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param Value $value
     *
     * @throws ValueConflictException
     */
    protected function verifyUriUniqueness(Value $value): void
    {
        if (!$value->hasModifiedUri()) {
            return;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        if ($this->getResource($value->getUri())->exists()) {
            throw new ValueConflictException("Value with {$value->getUri()} is already defined");
        }
    }

    protected function insert(ValueCollection $valueCollection, Value $value): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $valueCollectionResource = $this->getClass($valueCollection->getUri());

        $valueCollectionResource->createInstance($value->getLabel(), '', $value->getUri());
    }

    private function update(Value $value): void
    {
        if (!$value->hasChanges()) {
            return;
        }

        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update('statements')
            ->set('object', ':label')
            ->set('subject', ':uri')
            ->where($expressionBuilder->eq('id', ':id'))
            ->setParameters(
                [
                    'id'    => $value->getId(),
                    'uri'   => $value->getUri(),
                    'label' => $value->getLabel(),
                ]
            )
            ->execute();

        $this->updateRelations($value);
    }

    private function updateRelations(Value $value): void
    {
        if (!$value->hasModifiedUri()) {
            return;
        }

        $this->updateValues($value);
        $this->updateProperties($value);
    }

    /**
     * @param Value $value
     */
    private function updateValues(Value $value): void
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update('statements')
            ->set('subject', ':uri')
            ->where($expressionBuilder->eq('subject', ':original_uri'))
            ->setParameters(
                [
                    'uri'          => $value->getUri(),
                    'original_uri' => $value->getOriginalUri(),
                ]
            )
            ->execute();
    }

    /**
     * @param Value $value
     */
    private function updateProperties(Value $value): void
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update('statements')
            ->set('object', ':uri')
            ->where($expressionBuilder->eq('object', ':original_uri'))
            ->setParameters(
                [
                    'uri'          => $value->getUri(),
                    'original_uri' => $value->getOriginalUri(),
                ]
            )
            ->execute();
    }
    
    protected function getList(ValueCollectionSearchRequest $searchRequest) : \core_kernel_classes_Class
    {
        if ($searchRequest->hasValueCollectionUri()) {
            return $this->getClass($searchRequest->getValueCollectionUri());
        } elseif ($searchRequest->hasPropertyUri()) {
            return $this->getProperty($searchRequest->getPropertyUri())->getRange();
        } else {
            throw new \common_exception_BadRequest("Unable to determine correct list");
        }
        
    }
}
