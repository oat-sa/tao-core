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
 * Copyright (c) 2014-2024 Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'i18n',
    'lodash',
    'core/promise',
    'core/request',
    'layout/section',
    'layout/actions/binder',
    'layout/permissions',
    'provider/resources',
    'ui/destination/selector',
    'uri',
    'ui/feedback',
    'ui/dialog/confirm',
    'ui/taskQueue/taskQueue'
], function (
    module,
    $,
    __,
    _,
    Promise,
    request,
    section,
    binder,
    permissionsManager,
    resourceProviderFactory,
    destinationSelectorFactory,
    uri,
    feedback,
    confirmDialog,
    taskQueue
) {
    'use strict';

    const messages = {
        // prettier-ignore
        confirmMove: __('The properties of the source class will be replaced by those of the destination class. This might result in a loss of metadata. Continue anyway?')
    };

    /**
     * Cleans up the main panel and creates a container
     * @returns {jQuery}
     */
    function emptyPanel() {
        section.current().updateContentBlock('<div class="main-container flex-container-form-main"></div>');
        return $(section.selected.panel).find('.main-container');
    }

    /**
     * Register common actions.
     *
     * TODO this common actions may be re-structured, split in different files or moved in a more obvious location.
     *
     * @exports layout/actions/common
     */
    function commonActions() {
        /**
         * Register the load action: load the url and into the content container
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         */
        binder.register('load', function load(actionContext) {
            section.current().loadContentBlock(this.url, _.pick(actionContext, ['uri', 'classUri', 'id']));
        });

        /**
         * Register the load class action: load the url into the content container
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.classUri - the URI of the parent class
         */
        binder.register('loadClass', function load(actionContext) {
            section.current().loadContentBlock(this.url, {
                classUri: actionContext.classUri,
                id: uri.decode(actionContext.classUri)
            });
        });

        /**
         * Register the subClass action: creates a sub class
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.classUri - the URI of the parent class
         * @returns {Promise<Object>} resolves with the new class data
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('subClass', function subClass(actionContext) {
            const classUri = uri.decode(actionContext.classUri);
            let signature = actionContext.signature;
            if (actionContext.type !== 'class') {
                signature = actionContext.classSignature;
            }

            const currentSection = section.current();
            if (currentSection.clearContentBlock) {
                currentSection.clearContentBlock();
            }

            return request({
                url: this.url,
                method: 'POST',
                data: { id: classUri, type: 'class', signature: signature },
                dataType: 'json'
            }).then(response => {
                if (response.success && response.uri) {
                    if (actionContext.tree) {
                        $(actionContext.tree).trigger('addnode.taotree', [
                            {
                                uri: uri.decode(response.uri),
                                label: response.label,
                                parent: uri.decode(actionContext.classUri),
                                cssClass: 'node-class'
                            }
                        ]);
                    }

                    //return format (resourceSelector)
                    return {
                        uri: uri.decode(response.uri),
                        label: response.label,
                        classUri: uri.decode(actionContext.classUri),
                        type: 'class'
                    };
                } else {
                    throw new Error(__('Adding the new class has failed'));
                }
            });
        });

        /**
         * Register the instanciate action: creates a new instance from a class
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.classUri - the URI of the class' instance
         * @returns {Promise<Object>} resolves with the new instance data
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('instanciate', function instanciate(actionContext) {
            const classUri = uri.decode(actionContext.classUri);
            let signature = actionContext.signature;
            if (actionContext.type !== 'class') {
                signature = actionContext.classSignature;
            }
            return request({
                url: this.url,
                method: 'POST',
                data: { id: classUri, type: 'instance', signature: signature },
                dataType: 'json'
            }).then(function (response) {
                if (response.success && response.uri) {
                    //backward compat format for jstree
                    if (actionContext.tree) {
                        $(actionContext.tree).trigger('addnode.taotree', [
                            {
                                uri: uri.decode(response.uri),
                                label: response.label,
                                parent: uri.decode(actionContext.classUri),
                                cssClass: 'node-instance'
                            }
                        ]);
                    }

                    //return format (resourceSelector)
                    return {
                        uri: uri.decode(response.uri),
                        label: response.label,
                        classUri: uri.decode(actionContext.classUri),
                        type: 'instance'
                    };
                } else {
                    throw new Error(__('Adding the new resource has failed'));
                }
            });
        });

        /**
         * Register the duplicateNode action: creates a clone of a node.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.uri - the URI of the base instance
         * @param {String} actionContext.classUri - the URI of the class' instance
         * @returns {Promise<Object>} resolves with the new instance data
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('duplicateNode', function duplicateNode(actionContext) {
            return request({
                url: this.url,
                method: 'POST',
                data: {
                    uri: actionContext.id,
                    classUri: uri.decode(actionContext.classUri),
                    signature: actionContext.signature
                },
                dataType: 'json'
            }).then(function (response) {
                if (response.success && response.uri) {
                    //backward compat format for jstree
                    if (actionContext.tree) {
                        $(actionContext.tree).trigger('addnode.taotree', [
                            {
                                uri: uri.decode(response.uri),
                                label: response.label,
                                parent: uri.decode(actionContext.classUri),
                                cssClass: 'node-instance'
                            }
                        ]);
                    }

                    //return format (resourceSelector)
                    return {
                        uri: uri.decode(response.uri),
                        label: response.label,
                        classUri: uri.decode(actionContext.classUri),
                        type: 'instance'
                    };
                } else {
                    throw new Error(__('Node duplication has failed'));
                }
            });
        });

        /**
         * Register the removeNode action: removes a resource.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         *
         * @fires layout/tree#removenode.taotree
         */
        binder.register('removeNode', function remove(actionContext) {
            const data = {};

            data.uri = uri.decode(actionContext.uri);
            data.classUri = uri.decode(actionContext.classUri);
            data.id = actionContext.id;
            data.signature = actionContext.signature;

            return new Promise((resolve, reject) => {
                confirmDialog(
                    __('Please confirm deletion'),
                    // accept
                    () => {
                        request({
                            url: this.url,
                            method: 'POST',
                            data: data,
                            dataType: 'json'
                        }).then(response => {
                            if (response.success && response.deleted) {
                                feedback().success(response.message || __('Resource deleted'));

                                if (actionContext.tree) {
                                    $(actionContext.tree).trigger('removenode.taotree', [
                                        {
                                            id: actionContext.uri || actionContext.classUri
                                        }
                                    ]);
                                }
                                return resolve({
                                    uri: actionContext.uri || actionContext.classUri
                                });
                            } else {
                                if (response.success && !response.deleted) {
                                    $(actionContext.tree).trigger('refresh.taotree');
                                    reject(
                                        response.msg ||
                                            response.message ||
                                            // prettier-ignore
                                            __('Unable to delete the selected resource because you do not have the required rights to delete part of its content.')
                                    );
                                }

                                reject(
                                    response.msg || response.message || __('Unable to delete the selected resource')
                                );
                            }
                        });
                    },
                    // cancel
                    () => reject({ cancel: true })
                );
            });
        });

        /**
         * Register the removeNodes action: removes multiple resources
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object[]|Object} actionContexts - single or multiple action contexts
         * @returns {Promise<String[]>} with the list of deleted ids/uris
         */
        binder.register('removeNodes', function removeNodes(actionContexts) {
            let confirmMessage = '';
            const data = {};

            if (!_.isArray(actionContexts)) {
                actionContexts = [actionContexts];
            }

            const classes = _.filter(actionContexts, { type: 'class' });
            const instances = _.filter(actionContexts, { type: 'instance' });

            data.ids = _.map(actionContexts, function (elem) {
                return { id: elem.id, signature: elem.signature };
            });

            if (actionContexts.length === 1) {
                confirmMessage = __('Please confirm deletion');
            } else if (actionContexts.length > 1) {
                if (instances.length) {
                    if (instances.length === 1) {
                        confirmMessage = __('an instance');
                    } else {
                        confirmMessage = __('%s instances', instances.length);
                    }
                }
                if (classes.length) {
                    if (confirmMessage) {
                        confirmMessage += __(' and ');
                    }
                    if (classes.length === 1) {
                        confirmMessage = __('a class');
                    } else {
                        confirmMessage += __('%s classes', classes.length);
                    }
                }
                confirmMessage = __('Please confirm deletion of %s.', confirmMessage);
            }

            return new Promise((resolve, reject) => {
                confirmDialog(
                    confirmMessage,
                    //accept
                    () => {
                        request({
                            url: this.url,
                            method: 'POST',
                            data: data,
                            dataType: 'json'
                        }).then(response => {
                            if (response.success && response.deleted) {
                                resolve(response.deleted);
                            } else {
                                reject(new Error(response.message || __('Unable to delete the selected resources')));
                            }
                        });
                    },
                    //cancel
                    () => reject({ cancel: true })
                );
            });
        });

        /**
         * Register the moveNode action: moves a resource.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         */
        binder.register('moveNode', function remove(actionContext) {
            const data = _.assign(
                _.pick(actionContext, ['id', 'uri', 'destinationClassUri', 'confirmed', 'signature']), 
                { aclMode: module.config().aclTransferMode }
            );

            //wrap into a private function for recusion calls
            function _moveNode(url) {
                request({
                    url: url,
                    method: 'POST',
                    data: data,
                    dataType: 'json'
                }).then(response => {
                    if (response && response.status === true) {
                        return;
                    } else if (response && response.status === 'diff') {
                        // prettier-ignore
                        let message = __('Moving this element will replace the properties of the previous class by those of the destination class :');
                        message += '\n';
                        for (let i = 0; i < response.data.length; i++) {
                            if (response.data[i].label) {
                                message += `- ${response.data[i].label}\n`;
                            }
                        }
                        message += `${__('Please confirm this operation.')}\n`;

                        // eslint-disable-next-line no-alert
                        if (window.confirm(message)) {
                            data.confirmed = true;
                            return _moveNode(url, data);
                        }
                    }

                    //ask to rollback the tree
                    $(actionContext.tree).trigger('rollback.taotree');
                });
            }
            _moveNode(this.url, data);
        });

        /**
         * Register the launchEditor action.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         *
         * @fires layout/tree#removenode.taotree
         */
        binder.register('launchEditor', function launchEditor(actionContext) {
            const { actionParams } = actionContext;
            const data = _.pick(actionContext, ['id', ...(actionParams || [])]);
            const wideDifferenciator = '[data-content-target="wide"]';

            $.ajax({
                url: this.url,
                type: 'GET',
                data: data,
                dataType: 'html',
                success(response) {
                    const $response = $($.parseHTML(response, document, true));
                    //check if the editor should be displayed widely or in the content area
                    if ($response.is(wideDifferenciator) || $response.find(wideDifferenciator).length) {
                        section
                            .create({
                                id: 'authoring',
                                name: __('Authoring'),
                                url: this.url,
                                content: $response,
                                visible: false
                            })
                            .show();
                    } else {
                        section.updateContentBlock($response);
                    }
                }
            });
        });

        /**
         * Register the copyTo action: select a destination class to copy a resource
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object[]|Object} actionContext - single or multiple action contexts
         * @returns {Promise<String>} with the new resource URI
         */
        binder.register('copyTo', function copyTo(actionContext) {
            //create the container manually...
            const $container = emptyPanel();

            //get the resource provider configured with the action URL
            const resourceProvider = resourceProviderFactory({
                copyTo: {
                    url: this.url
                }
            });

            return new Promise((resolve, reject) => {
                //set up a destination selector
                destinationSelectorFactory($container, {
                    showACL: !!module.config().aclTransferMode,
                    aclTransferMode: module.config().aclTransferMode,
                    classUri: actionContext.rootClassUri,
                    preventSelection(nodeUri, node, $node) {
                        //prevent selection on nodes without WRITE permissions
                        if (($node.length && $node.data('access') === 'partial') || $node.data('access') === 'denied') {
                            if (!permissionsManager.hasPermission(nodeUri, 'WRITE')) {
                                feedback().warning(__('You are not allowed to write in the class %s', node.label), {
                                    encodeHtml: false
                                });
                                return true;
                            }
                        }
                        return false;
                    }
                })
                    .on('query', function onQuery(params) {
                        //asks only classes
                        params.classOnly = true;
                        resourceProvider
                            .getResources(params, true)
                            .then(resources => {
                                //ask the server the resources from the component query
                                this.update(resources, params);
                            })
                            .catch(err => this.trigger('error', err));
                    })
                    .on('select', function onSelect(destinationClassUri, aclTransferMode) {
                        if (!_.isEmpty(destinationClassUri)) {
                            this.disable();

                            resourceProvider
                                .copyTo(actionContext.id, destinationClassUri, actionContext.signature, aclTransferMode)
                                .then(result => {
                                    if (result && result.uri) {
                                        feedback().success(__('Resource copied'));

                                        //backward compatible for jstree
                                        if (actionContext.tree) {
                                            $(actionContext.tree).trigger('refresh.taotree', [result]);
                                        }
                                        return resolve(result);
                                    }
                                    return reject(new Error(__('Unable to copy the resource')));
                                })
                                .catch(err => this.trigger('error', err));
                        }
                    })
                    .on('error', reject);
            });
        });

        /**
         * Register the copyClassTo action: select a destination class to copy a class
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object[]|Object} actionContext - single or multiple action contexts
         * @returns {Promise<String>} with the destination class URI
         */
        binder.register('copyClassTo', function copyClassTo(actionContext) {
            //create the container manually...
            const $container = emptyPanel();

            //get the resource provider configured with the action URL
            const resourceProvider = resourceProviderFactory();

            /**
             * wrapped the old jstree API used to refresh the tree and optionally select a resource
             * @param {String} [uriResource] - the uri resource node to be selected
             */
            const refreshTree = uriResource => {
                if (actionContext.tree) {
                    $(actionContext.tree).trigger('refresh.taotree', [uriResource]);
                }
            };

            return new Promise((resolve, reject) => {
                //set up a destination selector
                const destinationSelector = destinationSelectorFactory($container, {
                    showACL: !!module.config().aclTransferMode,
                    aclTransferMode: module.config().aclTransferMode,
                    taskQueue: taskQueue,
                    taskCreationData: {
                        uri: actionContext.id,
                        signature: actionContext.signature
                    },
                    taskCreationUrl: this.url,
                    classUri: actionContext.rootClassUri,
                    preventSelection(nodeUri, node, $node) {
                        //prevent selection on nodes without WRITE permissions
                        if (($node.length && $node.data('access') === 'partial') || $node.data('access') === 'denied') {
                            if (!permissionsManager.hasPermission(nodeUri, 'WRITE')) {
                                feedback().warning(__('You are not allowed to write in the class %s', node.label), {
                                    encodeHtml: false
                                });
                                return true;
                            }
                        }
                        return false;
                    }
                })
                    .on('query', params => {
                        params.classOnly = true;
                        resourceProvider
                            .getResources(params, true)
                            .then(resources => destinationSelector.update(resources, params))
                            .catch(err => destinationSelector.trigger('error', err));
                    })
                    .on('finished', (result, button) => {
                        if (
                            result.task &&
                            result.task.report &&
                            _.isArray(result.task.report.children) &&
                            result.task.report.children.length &&
                            result.task.report.children[0]
                        ) {
                            if (
                                result.task.report.children[0].data &&
                                result.task.report.children[0].data.uriResource
                            ) {
                                feedback().info(__('%s completed', result.task.taskLabel), {
                                    encodeHtml: false
                                });

                                refreshTree(result.task.report.children[0].data.uriResource);
                            } else {
                                button.displayReport(result.task.report.children[0], __('Error'));
                            }
                        }
                    })
                    .on('continue', () => refreshTree(actionContext.id))
                    .on('select', (uri, aclMode) => {
                        destinationSelector.config.taskCreationData.aclMode = aclMode;

                        return resolve(uri);
                    })
                    .on('error', reject);
            });
        });

        /**
         * Register the moveTo action: select a destination class to move resources
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object|Object[]} actionContext - multiple action contexts
         * @returns {Promise<String>} with the destination class URI
         */
        binder.register('moveTo', function moveTo(actionContext) {
            //create the container manually...
            const $container = emptyPanel();

            //backward compatible for jstree
            const tree = actionContext.tree;

            //get the resource provider configured with the action URL
            const resourceProvider = resourceProviderFactory({
                moveTo: {
                    url: this.url
                }
            });

            if (!_.isArray(actionContext)) {
                actionContext = [actionContext];
            }

            return new Promise((resolve, reject) => {
                const rootClassUri = _.map(actionContext, 'rootClassUri').pop();
                const selectedUri = _.map(actionContext, 'id');
                const selectedData = _.map(actionContext, a => {
                    return { id: a.id, signature: a.signature };
                });

                //set up a destination selector
                destinationSelectorFactory($container, {
                    aclTransferMode: module.config().aclTransferMode,
                    showACL: !!module.config().aclTransferMode,
                    title: __('Move to'),
                    actionName: __('Move'),
                    icon: 'move-item',
                    classUri: rootClassUri,
                    confirm: messages.confirmMove,
                    preventSelection(nodeUri, node, $node) {
                        //prevent selection on nodes without WRITE permissions
                        if (($node.length && $node.data('access') === 'partial') || $node.data('access') === 'denied') {
                            if (!permissionsManager.hasPermission(nodeUri, 'WRITE')) {
                                feedback().warning(__('You are not allowed to write in the class %s', node.label), {
                                    encodeHtml: false
                                });
                                return true;
                            }
                        }

                        const uriList = [nodeUri];
                        $node.parents('.class').each(function () {
                            if (this.dataset.uri !== rootClassUri) {
                                uriList.push(this.dataset.uri);
                            }
                        });

                        //prevent selection on nodes that are already the containers of the resources or the resources themselves
                        if (_.intersection(selectedUri, uriList).length) {
                            feedback().warning(
                                __('You cannot move the selected resources in the class %s', node.label),
                                { encodeHtml: false }
                            );
                            return true;
                        }

                        return false;
                    }
                })
                    .on('query', function onQuery(params) {
                        //asks only classes
                        params.classOnly = true;
                        resourceProvider
                            .getResources(params, true)
                            .then(resources => {
                                //ask the server the resources from the component query
                                this.update(resources, params);
                            })
                            .catch(err => this.trigger('error', err));
                    })
                    .on('select', function onSelect(destinationClassUri, aclTransferMode) {
                        if (!_.isEmpty(destinationClassUri)) {
                            this.disable();

                            resourceProvider
                                .moveTo(selectedData, destinationClassUri, aclTransferMode)
                                .then(results => {
                                    const failed = [];
                                    const success = [];

                                    _.forEach(results, (result, resUri) => {
                                        const resource = _.find(actionContext, { uri: resUri });
                                        if (result.success) {
                                            success.push(resource);
                                        } else {
                                            failed.push(result.message);
                                        }
                                    });

                                    if (!success.length) {
                                        feedback().warning(__(failed.join(', ')));
                                    } else if (failed.length) {
                                        feedback().warning(
                                            __('Some resources have not been moved: %s', failed.join(', '))
                                        );
                                    } else {
                                        feedback().success(__('Resources moved'));
                                    }

                                    //backward compatible for jstree
                                    if (tree) {
                                        $(tree).trigger('refresh.taotree', [destinationClassUri]);
                                    }
                                    return resolve(destinationClassUri);
                                })
                                .catch(err => this.trigger('error', err));
                        }
                    })
                    .on('error', reject);
            });
        });
    }

    return commonActions;
});
