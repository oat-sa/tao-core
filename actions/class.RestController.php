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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyAwareTrait;

abstract class tao_actions_RestController extends \tao_actions_CommonModule
{
    use OntologyAwareTrait;
    use \tao_actions_RestTrait;

    const CLASS_URI_PARAM = 'class-uri';
    const CLASS_LABEL_PARAM = 'class-label';
    const CLASS_COMMENT_PARAM = 'class-comment';
    const PARENT_CLASS_URI_PARAM = 'parent-class-uri';

    /**
     * Check response encoding requested
     *
     * tao_actions_RestModule constructor.
     */
    public function __construct()
    {
        if ($this->hasHeader("Accept")) {
            try {
                $this->responseEncoding = (tao_helpers_Http::acceptHeader($this->getAcceptableMimeTypes(), $this->getHeader("Accept")));
            } catch (common_exception_ClientException $e) {
                $this->returnFailure($e);
            }
        }

        header('Content-Type: '.$this->responseEncoding);
    }

    /**
     * @OA\Schema(
     *     schema="tao.GenerisClass.Search",
     *     type="object",
     *     @OA\Property(
     *         property="class-uri",
     *         type="string",
     *         description="Target class uri"
     *     ),
     *     @OA\Property(
     *         property="class-label",
     *         type="string",
     *         description="Target class label. If label is not unique first match will be used"
     *     )
     * )
     *
     * Get class instance from request parameters
     * If more than one class with given label exists the first open will be picked up.
     * @param core_kernel_classes_Class $rootClass
     * @return core_kernel_classes_Class|null
     * @throws common_exception_RestApi
     */
    protected function getClassFromRequest(\core_kernel_classes_Class $rootClass)
    {
        $class = null;
        if ($this->hasRequestParameter(self::CLASS_URI_PARAM) && $this->hasRequestParameter(self::CLASS_LABEL_PARAM)) {
            throw new \common_exception_RestApi(
                self::CLASS_URI_PARAM . ' and ' . self::CLASS_LABEL_PARAM . ' parameters do not supposed to be used simultaneously.'
            );
        }

        if (!$this->hasRequestParameter(self::CLASS_URI_PARAM) && !$this->hasRequestParameter(self::CLASS_LABEL_PARAM)) {
            $class = $rootClass;
        }

        if ($this->hasRequestParameter(self::CLASS_URI_PARAM)) {
            $classUriParam = $this->getRequestParameter(self::CLASS_URI_PARAM);
            if (!$classUriParam) {
                throw new \common_exception_RestApi(
                    self::CLASS_URI_PARAM .  ' is not valid.'
                );
            }
            $class = $this->getClass($classUriParam);
        }
        if ($this->hasRequestParameter(self::CLASS_LABEL_PARAM)) {
            $label = $this->getRequestParameter(self::CLASS_LABEL_PARAM);
            foreach ($rootClass->getSubClasses(true) as $subClass) {
                if ($subClass->getLabel() === $label) {
                    $class = $subClass;
                    break;
                }
            }
        }
        if ($class === null || !$class->exists()) {
            throw new \common_exception_RestApi(
                'Class does not exist. Please use valid '.self::CLASS_URI_PARAM . ' or '.self::CLASS_LABEL_PARAM
            );
        }
        return $class;
    }

    /**
     * @OA\Schema(
     *     schema="tao.GenerisClass.New",
     *     type="object",
     *     @OA\Property(
     *         property="class-label",
     *         type="string",
     *         description="Class label"
     *     ),
     *     @OA\Property(
     *         property="class-comment",
     *         type="string",
     *         description="Class comment"
     *     ),
     *     @OA\Property(
     *         property="parent-class-uri",
     *         type="string",
     *         description="Parent class uri, root class by default"
     *     )
     * )
     *
     * Create sub class of given root class.
     *
     * @param core_kernel_classes_Class $rootClass
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_ClassAlreadyExists
     * @return \core_kernel_classes_Class
     */
    protected function createSubClass(\core_kernel_classes_Class $rootClass)
    {
        if (!$this->hasRequestParameter(static::CLASS_LABEL_PARAM)) {
            throw new \common_exception_RestApi('Missed required parameter: ' . static::CLASS_LABEL_PARAM);
        }
        $label = $this->getRequestParameter(static::CLASS_LABEL_PARAM);

        if ($this->hasRequestParameter(static::PARENT_CLASS_URI_PARAM)) {
            $parentClass = $this->getClass($this->getRequestParameter(static::PARENT_CLASS_URI_PARAM));
            if ($parentClass->getUri() !== $rootClass->getUri() && !$parentClass->isSubClassOf($rootClass)) {
                throw new \common_Exception(__('Class uri provided is not a valid class.'));
            }
            $rootClass = $parentClass;
        }

        $comment = $this->hasRequestParameter(static::CLASS_COMMENT_PARAM)
            ? $this->getRequestParameter(static::CLASS_COMMENT_PARAM)
            : '';

        $class = null;

        /** @var \core_kernel_classes_Class $subClass */
        foreach ($rootClass->getSubClasses() as $subClass) {
            if ($subClass->getLabel() === $label) {
                throw new \common_exception_ClassAlreadyExists($subClass);
            }
        }

        if (!$class) {
            $class = $rootClass->createSubClass($label, $comment);
        }

        return $class;
    }

}