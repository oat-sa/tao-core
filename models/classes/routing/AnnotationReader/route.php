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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Siarhei Baradzin <siarhei.baradzin@1pt.com>
 */

namespace oat\tao\model\routing\AnnotationReader;


/**
 * Attach this annotation to controller's public methods to use FastRoute features
 * To make it work use ControllerAnnotationsRoute in your manifest
 * @see ControllerAnnotationsRoute
 * @Annotation
 */
class route
{
    /**
     * Allowed HTTP request method, if not specified any method is allowed
     * @var string
     */
    public $method;

    /**
     * Path pattern relative to controller path, path variables could be specified using FastRoute syntax
     * @see \FastRoute\Route
     * @var string
     */
    public $relativePath;
}
