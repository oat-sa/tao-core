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

namespace oat\tao\model\user;

use common_exception_Unauthorized;
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\accessControl\PermissionCheckerInterface;
use tao_models_classes_UserService;

/**
 * Search users for comment @mentions (login OR display name).
 * Eligibility scope comes from MentionEligibleUsersProviderInterface.
 */
class CommentMentionUserSearchService
{
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 50;
    private const CANDIDATE_BATCH = 100;
    private const IN_MEMORY_ELIGIBLE_THRESHOLD = 200;

    /**
     * Authoring resource types accepted by the mention search API.
     *
     * @var list<string>
     */
    private const RESOURCE_TYPES = ['item', 'test', 'asset'];

    private Ontology $ontology;
    private PermissionCheckerInterface $permissionChecker;
    private tao_models_classes_UserService $userService;
    private MentionEligibleUsersProviderInterface $eligibleUsersProvider;

    public function __construct(
        Ontology $ontology,
        PermissionCheckerInterface $permissionChecker,
        tao_models_classes_UserService $userService,
        MentionEligibleUsersProviderInterface $eligibleUsersProvider
    ) {
        $this->ontology = $ontology;
        $this->permissionChecker = $permissionChecker;
        $this->userService = $userService;
        $this->eligibleUsersProvider = $eligibleUsersProvider;
    }

    /**
     * @return array{
     *     users: array<int, array{id: string, login: string, displayName: string}>,
     *     limit: int,
     *     offset: int,
     *     total: int
     * }
     */
    public function search(
        string $resourceUri,
        string $resourceType,
        string $query,
        int $limit = self::DEFAULT_LIMIT,
        int $offset = 0
    ): array {
        $resourceUri = trim($resourceUri);
        $this->assertValidResourceType(trim($resourceType));
        $query = trim($query);

        if ($resourceUri === '') {
            throw new InvalidArgumentException('resourceUri is required');
        }

        if (!$this->permissionChecker->hasReadAccess($resourceUri)) {
            throw new common_exception_Unauthorized('Read access required to mention users on this resource');
        }

        $limit = max(1, min($limit, self::MAX_LIMIT));
        $offset = max(0, $offset);

        $eligibleUris = $this->eligibleUsersProvider->getEligibleUserUris($resourceUri);

        if (is_array($eligibleUris) && $eligibleUris === []) {
            return $this->emptyResult($limit, $offset);
        }

        if (is_array($eligibleUris) && count($eligibleUris) <= self::IN_MEMORY_ELIGIBLE_THRESHOLD) {
            $matched = $this->matchFromEligibleSet($eligibleUris, $query);
        } else {
            $matched = $this->matchFromUserSearch($query, $eligibleUris);
        }

        usort(
            $matched,
            static function (array $left, array $right): int {
                return strcasecmp($left['login'], $right['login']);
            }
        );

        $total = count($matched);

        return [
            'users' => array_values(array_slice($matched, $offset, $limit)),
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
        ];
    }

    private function assertValidResourceType(string $resourceType): string
    {
        if (!in_array($resourceType, self::RESOURCE_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'resourceType must be one of: %s',
                    implode(', ', self::RESOURCE_TYPES)
                )
            );
        }

        return $resourceType;
    }

    /**
     * @param list<string> $eligibleUris
     * @return list<array{id: string, login: string, displayName: string}>
     */
    private function matchFromEligibleSet(array $eligibleUris, string $query): array
    {
        $matched = [];

        foreach ($eligibleUris as $userUri) {
            $userResource = $this->ontology->getResource($userUri);
            if (!$userResource->exists()) {
                continue;
            }

            $row = $this->mapUserRow($userResource);
            if ($row === null) {
                continue;
            }

            if ($query !== '' && !$this->matchesQuery($row, $query)) {
                continue;
            }

            $matched[] = $row;
        }

        return $matched;
    }

    /**
     * @param list<string>|null $eligibleUris null = unrestricted
     * @return list<array{id: string, login: string, displayName: string}>
     */
    private function matchFromUserSearch(string $query, ?array $eligibleUris): array
    {
        $eligibleSet = $eligibleUris === null ? null : array_fill_keys($eligibleUris, true);
        $candidates = $this->userService->getAllUsers(
            [
                'recursive' => true,
                'like' => true,
                'chaining' => 'or',
                'limit' => self::CANDIDATE_BATCH,
                'offset' => 0,
                'order' => GenerisRdf::PROPERTY_USER_LOGIN,
            ],
            $this->buildNameFilters($query)
        );

        $matched = [];
        foreach ($candidates as $userResource) {
            if (!$userResource instanceof core_kernel_classes_Resource) {
                continue;
            }

            if ($eligibleSet !== null && !isset($eligibleSet[$userResource->getUri()])) {
                continue;
            }

            $row = $this->mapUserRow($userResource);
            if ($row === null) {
                continue;
            }

            if ($query !== '' && !$this->matchesQuery($row, $query)) {
                continue;
            }

            $matched[] = $row;
        }

        return $matched;
    }

    /**
     * @return array<string, string>
     */
    private function buildNameFilters(string $query): array
    {
        if ($query === '') {
            return [
                GenerisRdf::PROPERTY_USER_LOGIN => '*',
            ];
        }

        return [
            GenerisRdf::PROPERTY_USER_LOGIN => $query,
            OntologyRdfs::RDFS_LABEL => $query,
            GenerisRdf::PROPERTY_USER_FIRSTNAME => $query,
            GenerisRdf::PROPERTY_USER_LASTNAME => $query,
        ];
    }

    /**
     * @param array{id: string, login: string, displayName: string} $row
     */
    private function matchesQuery(array $row, string $query): bool
    {
        return stripos($row['login'], $query) !== false
            || stripos($row['displayName'], $query) !== false;
    }

    /**
     * @return array{id: string, login: string, displayName: string}|null
     */
    private function mapUserRow(core_kernel_classes_Resource $userResource): ?array
    {
        $login = $this->resolveLogin($userResource);
        if ($login === null) {
            return null;
        }

        if (!$this->hasValidEmail($userResource)) {
            return null;
        }

        return [
            'id' => $userResource->getUri(),
            'login' => $login,
            'displayName' => $this->resolveDisplayName($userResource, $login),
        ];
    }

    private function resolveLogin(core_kernel_classes_Resource $userResource): ?string
    {
        $loginProperty = $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_LOGIN);
        $login = trim((string) $userResource->getOnePropertyValue($loginProperty));

        return $login !== '' ? $login : null;
    }

    /**
     * Mention candidates must have a usable account email (same rule as notification send).
     */
    private function hasValidEmail(core_kernel_classes_Resource $userResource): bool
    {
        $email = trim((string) $userResource->getOnePropertyValue(
            $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_MAIL)
        ));

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Same semantics as UserHelper::getUserName($user, true): first+last, else label, else login.
     */
    private function resolveDisplayName(core_kernel_classes_Resource $userResource, string $login): string
    {
        $firstName = trim((string) $userResource->getOnePropertyValue(
            $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_FIRSTNAME)
        ));
        $lastName = trim((string) $userResource->getOnePropertyValue(
            $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_LASTNAME)
        ));
        $displayName = trim($firstName . ' ' . $lastName);

        if ($displayName === '') {
            $displayName = trim((string) $userResource->getOnePropertyValue(
                $this->ontology->getProperty(OntologyRdfs::RDFS_LABEL)
            ));
        }

        return $displayName !== '' ? $displayName : $login;
    }

    /**
     * @return array{users: array{}, limit: int, offset: int, total: int}
     */
    private function emptyResult(int $limit, int $offset): array
    {
        return [
            'users' => [],
            'limit' => $limit,
            'offset' => $offset,
            'total' => 0,
        ];
    }
}
