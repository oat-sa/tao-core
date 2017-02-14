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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n'
], function ($, _, __) {
    'use strict';

    /**
     * Defines an HTML data proxy implementation.
     * Will request the DOM to fetch data.
     */
    return {
        init: function htmlDataInit(params) {},
        destroy: function htmlDataDestroy() {},
        create: function htmlDataCreate(params) {},
        read: function htmlDataRead(params) {},
        write: function htmlDataWrite(params) {},
        remove: function htmlDataRemove(params) {},
        action: function htmlDataAction(action, params) {}
    };
});
