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

define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'util/url',
    'core/dataProvider/request',
    'ui/feedback',
    'ui/generis/form/form'
], function(
    $,
    _,
    __,
    module,
    url,
    request,
    feedback,
    generisFormFactory
) {
    'use strict';

    /**
     * The user add controller
     * @exports controller/users/add
     */
    return {
        start: function() {
            var route = url.route('edit', 'RestUser', 'tao');
            var uri = module.config().uri;

            request(route, {
                uri: uri
            }, 'get')
            .then(function (data) {
                var labels, passwords;

                // Reorder widgets (i.e. #label comes first and #password goes last)
                labels = _.remove(data.properties, function (property) {
                    return property.uri === 'http://www.w3.org/2000/01/rdf-schema#label';
                });
                if (labels.length) {
                    data.properties.unshift(labels[0]);
                }

                passwords = _.remove(data.properties, function (property) {
                    return property.uri === 'http://www.tao.lu/Ontologies/generis.rdf#password';
                });
                if (passwords.length) {
                    data.properties.push(passwords[0]);
                }

                generisFormFactory(
                    data,
                    { title: __('Edit a user') }
                )
                .render($('.edit-user.form-container'))
                .on('submit', function (formData) {
                    var jsonData = {};
                    var self = this;

                    _.each(formData, function (val) {
                        jsonData[val.name] = val.value;
                    });

                    jsonData['uri'] = uri;

                    this.toggleLoading();
                    this.validate();

                    if (!this.errors.length) {
                        request(
                            route,
                            JSON.stringify(jsonData),
                            'post',
                            { 'Content-Type': 'application/json' }
                        )
                        .then(function () {
                            self.clearWidgetErrors();
                            self.toggleLoading();

                            feedback().success(__('User saved'));
                        })
                        .catch(function (err) {
                            self.toggleLoading();

                            self.clearWidgetErrors();
                            if (err.response) {
                                _.each(err.response.data || [], function (message, widgetUri) {
                                    var widget = self.getWidget(widgetUri);
                                    if (widget) {
                                        widget.addErrors(message);
                                    }
                                });
                            }

                            feedback().error(err);
                        });
                    } else {
                        this.toggleLoading();
                        feedback().error(__('Some fields are invalid'));
                    }
                });
            });
        }
    };
});
