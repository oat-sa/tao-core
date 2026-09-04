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
 * Copyright (c) 2017-2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use oat\generis\model\GenerisRdf;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\user\CommentMentionUserSearchService;

/**
 * Class tao_actions_RestUser
 *
 * Rest interface to manage forms to create and edit users.
 *
 * Request should contains following data:
 * [
 *       "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "Bertrand",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userLastName"  => "Chevrier",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userMail" => "bertrand@taotesting.com",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userDefLg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userUILg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR",
 *       "http://www.tao.lu/Ontologies/generis.rdf#login" => "berty",
 *       "http://www.w3.org/2000/01/rdf-schema#label" => "bertounet",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userRoles"=> [
 *          'http://www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole',
 *          'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole'
 *       ],
 *       'password1' => 'ctl789@CTL789@',
 *       'password2' => 'ctl789@CTL789@',
 * ]
 *
 * Mention search (authoring comments):
 * - GET /tao/RestUser/searchUsers?resourceUri=&resourceType=&q=&limit=&offset=
 */
class tao_actions_RestUser extends tao_actions_RestResource
{
    use HttpJsonResponseTrait;

    /**
     * Mention autocomplete: eligible users filtered by login or display name.
     * Response users include id, login, and displayName.
     */
    public function searchUsers(): void
    {
        try {
            if (!$this->isRequestGet()) {
                $this->setErrorJsonResponse('Method not allowed', 405, [], 405);

                return;
            }

            $query = $this->getPsrRequest()->getQueryParams();
            $resourceUri = $this->requireStringParam($query['resourceUri'] ?? null, 'resourceUri');
            if ($resourceUri === null) {
                return;
            }

            $resourceType = $this->requireStringParam($query['resourceType'] ?? null, 'resourceType');
            if ($resourceType === null) {
                return;
            }

            $search = isset($query['q']) && is_string($query['q']) ? $query['q'] : '';
            $limit = isset($query['limit']) ? (int) $query['limit'] : 20;
            $offset = isset($query['offset']) ? (int) $query['offset'] : 0;

            $this->setSuccessJsonResponse(
                $this->getCommentMentionUserSearchService()->search(
                    $resourceUri,
                    $resourceType,
                    $search,
                    $limit,
                    $offset
                )
            );
        } catch (common_exception_Unauthorized $exception) {
            $this->setErrorJsonResponse($exception->getMessage(), 403, [], 403);
        } catch (InvalidArgumentException $exception) {
            $this->setErrorJsonResponse($exception->getMessage(), 412, [], 412);
        } catch (Throwable $exception) {
            $this->logError($exception->getMessage());
            $this->setErrorJsonResponse('Unable to search mention users', 500, [], 500);
        }
    }

    /**
     * Validate a required query/body parameter as a non-null string.
     *
     * On failure, writes a JSON 400 response and returns null so the caller
     * can early-return without throwing (response is already sent).
     *
     * @param mixed $value Raw request value (typically from query params)
     * @param string $name Parameter name used in the error message
     */
    private function requireStringParam($value, string $name): ?string
    {
        // Missing key / explicit null → 400 "is required"
        if ($value === null) {
            $this->setErrorJsonResponse(sprintf('%s is required', $name), 400, [], 400);

            return null;
        }

        // Wrong PHP type (e.g. int from query casting) → 400 "must be a string"
        if (!is_string($value)) {
            $this->setErrorJsonResponse(sprintf('%s must be a string', $name), 400, [], 400);

            return null;
        }

        return $value;
    }

    private function getCommentMentionUserSearchService(): CommentMentionUserSearchService
    {
        return $this->getPsrContainer()->get(CommentMentionUserSearchService::class);
    }

    /**
     * Return the form object to manage user edition or creation
     *
     * @param $instance
     * @return tao_actions_form_RestUserForm
     */
    protected function getForm($instance)
    {
        return $this->propagate(new \tao_actions_form_RestUserForm($instance));
    }

    /**
     * Return the resource parameter
     *
     * @return core_kernel_classes_Resource
     * @InvalidArgumentException If resource does not belong to GenerisRdf::CLASS_GENERIS_USER
     */
    protected function getResourceParameter()
    {
        $resource = parent::getResourceParameter();
        if ($resource->isInstanceOf($this->getClass(GenerisRdf::CLASS_GENERIS_USER))) {
            return $resource;
        }

        throw new InvalidArgumentException('Only user resource are allowed.');
    }

    /**
     * Return the class parameter
     *
     * @return core_kernel_classes_Resource
     * @InvalidArgumentException If class is not an instance GenerisRdf::CLASS_GENERIS_USER
     */
    protected function getClassParameter()
    {
        $class = parent::getClassParameter();
        $rootUserClass = $this->getClass(GenerisRdf::CLASS_GENERIS_USER);

        if ($class->getUri() == $rootUserClass->getUri()) {
            return $class;
        }

        /** @var core_kernel_classes_Class $instance */
        foreach ($rootUserClass->getSubClasses(true) as $instance) {
            if ($instance->getUri() == $class->getUri()) {
                return $class;
            }
        }

        throw new InvalidArgumentException('Only user classes are allowed as classUri.');
    }
}
