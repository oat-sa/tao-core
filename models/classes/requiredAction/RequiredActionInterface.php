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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\requiredAction;

/**
 * Interface RequiredActionInterface
 *
 * RequiredAction is action which should be executed by user before performing any activities in the TAO
 *
 * @package oat\tao\model\requiredAction
 * @author Aleh Hutnilau <hutnikau@1pt.com>
 */
interface RequiredActionInterface
{

    /**
     * @return string
     */
    function getName();

    /**
     * @param RequiredActionRuleInterface $rule
     * @return mixed
     */
    function setRule(RequiredActionRuleInterface $rule);

    /**
     * @return RequiredActionRuleInterface[]
     */
    function getRules();

    /**
     * Execute an action.
     * @return mixed
     */
    function execute();

    /**
     * Mark action as completed.
     * @return mixed
     */
    function completed();

    /**
     * Check whether the action must be executed.
     * @return boolean
     */
    function mustBeExecuted();

}