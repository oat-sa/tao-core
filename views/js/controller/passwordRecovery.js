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
 */

/**
 * Recovery password page controller
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
define([
    'jquery',
    'i18n',
    'module',
    'ui/feedback',
    'layout/version-warning',
    'core/request'
], function ($, __, module, feedback, versionWarning, request) {
    'use strict';
    var conf = module.config(),
        feedbackType;

    versionWarning.init();
    if (conf.message) {
        for (feedbackType in conf.message) {
            if (conf.message[feedbackType]) {
                feedback()[feedbackType](conf.message[feedbackType]);
            }
        }
    }

    return {
        start: function start() {
            // email address submisssion via AJAX with token:
            $('#passwordRecoveryForm').on('submit', function(e) {
                var $form = $(this);
                e.preventDefault();

                request({
                    url: $form.attr('action'),
                    method: $form.attr('method'),
                    data: $form.serialize(),
                    noToken: false
                })
                .then(function(response) {
                    if (response.success) {
                        feedback().success(response.message || __('OK'));
                    }
                })
                .catch(function(err) {
                    feedback().error(err);
                });
            });
        }
    };
});
