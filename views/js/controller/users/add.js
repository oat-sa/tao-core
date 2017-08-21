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
    'util/url',
    'core/dataProvider/request',
    'ui/feedback',
    'ui/generis/form/form'
], function(
    $,
    _,
    __,
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
            var route = url.route('create', 'RestUser', 'tao');
            var classUri = 'http://www.tao.lu/Ontologies/TAO.rdf#User';

            request(route, {
                classUri: classUri
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
                    { title: __('Add a user') }
                )
                .render($('.add-user.form-container'))
                .on('submit', function (formData) {
                    var self = this;

                    formData.push({ name: 'classUri', value: classUri });

                    this.toggleLoading();
                    this.validate();

                    if (!this.errors.length) {
                        request(route, formData, 'post')
                        .then(function () {
                            self.clearWidgets();
                            self.toggleLoading();

                            feedback().success(__('User added'));
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
