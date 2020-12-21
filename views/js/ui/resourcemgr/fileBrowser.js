define([
    'jquery',
    'lodash',
    'ui/pagination',
    'tpl!ui/resourcemgr/tpl/rootFolder',
    'tpl!ui/resourcemgr/tpl/folder'
], function($, _, paginationComponent, rootFolderTpl, folderTpl) {
    'use strict';

    var ns = 'resourcemgr';

    return function (options) {
        var root = options.root || 'local';
        var rootPath = options.path || '/';
        var $container = options.$target;
        var $fileBrowser = $('.file-browser .file-browser-wrapper', $container);
        var $divContainer = $('.' + root, $fileBrowser);
        var $folderContainer = $('.folders', $divContainer);
        var fileTree = {};
        // for pagination
        var selectedClass = {
            path: rootPath,
            childrenLimit: 10,
            total: 0,
            page: 1
        };

        //load the content of the ROOT
        getFolderContent(fileTree, rootPath, function (content) {
            //create the tree node for the ROOT folder by default once the initial content loaded
            $folderContainer.append(rootFolderTpl(content));

            var $rootNode = $('.root-folder', $folderContainer);
            //create an inner list and append found elements
            var $innerList = $('.root ul', $folderContainer);
            if (content.children) {
                $rootNode.addClass('opened');
            }
            updateFolders(content, $innerList);
            //internal event to set the file-selector content
            $('.file-browser').find('li.active').removeClass('active');
            updateSelectedClass(content.path, content.total, content.childrenLimit);
            $container.trigger('folderselect.' + ns, [content.label, getPage(content.children), content.path]);
            renderPagination();
        });

        // by clicking on the tree (using a live binding  because content is not complete yet)
        $divContainer.off('click', '.folders a').on('click', '.folders a', function (e) {
            e.preventDefault();
            var $selected = $(this);
            var $folders = $('.folders li', $fileBrowser);
            var fullPath = $selected.data('path');
            var subTree = getByPath(fileTree, fullPath);
            updateSelectedClass(fullPath, subTree.total, $selected.data('children-limit'));

            //get the folder content
            getFolderContent(subTree, fullPath, function (content) {
                if (content) {
                    //either create the inner list of the content is new or just show it
                    var $innerList = $selected.siblings('ul');
                    if (!$innerList.length && content.children && _.find(content.children, 'path') && !content.empty) {
                        $innerList = $('<ul></ul>').insertAfter($selected);
                        updateFolders(content, $innerList);
                        $selected.addClass('opened');
                    } else if ($innerList.length) {
                        if ($innerList.css('display') === 'none') {
                            $innerList.show();
                            $selected.addClass('opened');
                        } else if ($selected.parent('li').hasClass('active')) {
                            $innerList.hide();
                            $selected.removeClass('opened');
                        }
                    }

                    //toggle active element
                    $folders.removeClass('active');
                    $selected.parent('li').addClass('active');

                    //internal event to set the file-selector content
                    $container.trigger('folderselect.' + ns, [content.label, getPage(content.children), content.path]);
                    renderPagination();
                }
            });
        });

        $container.on('filenew.' + ns, function (e, file, path) {
            var subTree = getByPath(fileTree, path);
            if (subTree) {
                if (!subTree.children) {
                    subTree.children = [];
                }
                if (root !== 'local' || !_.find(subTree.children, { name: file.name })) {
                    subTree.children.push(file);
                    subTree.total++;
                    selectedClass.total++;
                    $container.trigger('folderselect.' + ns, [subTree.label, getPage(subTree.children), path]);
                    renderPagination();
                }
            }
        });

        $container.on('filedelete.' + ns, function (e, path) {
            if (removeFromPath(fileTree, path)) {
                selectedClass.total--;
                loadPage();
            }
        });
        /**
         * Get files for page
         * @param {Array} children
         * @returns {Array} files for this page
         */
        function getPage(children) {
            var files = _.filter(children, function (item) {
                return !!item.uri;
            });
            if (selectedClass.childrenLimit) {
                return files.slice(
                    (selectedClass.page - 1) * selectedClass.childrenLimit,
                    selectedClass.page * selectedClass.childrenLimit
                );
            }
            return files;
        }
        /**
         * Get the content of a folder, either in the model or load it
         * @param {Object} tree - the tree model
         * @param {String} path - the folder path (relative to the root)
         * @param {Function} cb - called back with the content in 1st parameter
         */
        function getFolderContent(tree, path, cb) {
            var content = getByPath(tree, path);
            if (!content || (!content.children && !content.empty)) {
                loadContent(path).done(function (data) {
                    if (!tree.path) {
                        tree = _.merge(tree, data);
                    } else if (data.children) {
                        if (!_.find(data.children, 'path')) {
                            // no subfolders inside folder
                            tree.empty = true;
                        }
                        setToPath(tree, path, data);
                    } else {
                        tree.empty = true;
                    }
                    cb(data);
                });
            } else if (content.children) {
                var files = _.filter(content.children, function (item) {
                    return !!item.uri;
                });
                // if files less then total and need toload this page
                if ((files.length < selectedClass.total) &&
                    (files.length < selectedClass.page * selectedClass.childrenLimit)
                ) {
                    loadContent(path).done(function (data) {
                        var loadedFiles = _.filter(data.children, function (item) {
                            return !!item.uri;
                        });
                        setToPath(tree, path, {children: loadedFiles});
                        content = getByPath(tree, path);
                        cb(content);
                    });
                } else {
                    cb(content);
                }
            } else {
                cb(content);
            }
        }

        /**
         * Get a subTree from a path
         * @param {Object} tree - the tree model
         * @param {String} path - the path (relative to the root)
         * @returns {Object} the subtree that matches the path
         */
        function getByPath(tree, path) {
            var match;
            if (tree) {
                if (tree.path && tree.path.indexOf(path) === 0) {
                    match = tree;
                } else if (tree.children) {
                    _.forEach(tree.children, function (child) {
                        match = getByPath(child, path);
                        if (match) {
                            return false;
                        }
                    });
                }
            }
            return match;
        }

        /**
         * Merge data into at into the subtree
         * @param {Object} tree - the tree model
         * @param {String} path - the path (relative to the root)
         * @param {Object} data - the sbutree to merge at path level
         * @returns {Boolean}  true if done
         */
        function setToPath(tree, path, data) {
            var done = false;
            if (tree) {
                if (tree.path === path) {
                    tree.children = tree.children ? tree.children.concat(data.children) : data.children;
                    if (data.total) {
                        tree.total = data.total;
                    }
                } else if (tree.children) {
                    _.forEach(tree.children, function (child) {
                        done = setToPath(child, path, data);
                        if (done) {
                            return false;
                        }
                    });
                }
            }
            return done;
        }
        /**
         * Remove file from tree
         * @param {Object} tree - the tree model
         * @param {String} path - the path (relative to the root)
         * @returns {boolean} is file removed
         */
        function removeFromPath(tree, path) {
            var done = false;
            var removed = [];
            if (tree && tree.children) {
                removed = _.remove(tree.children, function (child) {
                    return child.path === path || (child.name && tree.path + child.name === path) || child.uri === path;
                });
                done = removed.length > 0;
                tree.total--;
                if (!done) {
                    _.forEach(tree.children, function (child) {
                        done = removeFromPath(child, path);
                        if (done) {
                            return false;
                        }
                    });
                }
            }
            return done;
        }

        /**
         * Get the content of a folder
         * @param {String} path - the folder path
         * @returns {jQuery.Deferred} the defferred object to run done/complete/fail
         */
        function loadContent(path) {
            var parameters = {};
            parameters[options.pathParam] = path;
            return $.getJSON(options.browseUrl, _.merge(parameters, options.params), { childrenOffset: (selectedClass.page - 1) * selectedClass.childrenLimit });
        }

        /**
         * Update the HTML Tree
         * @param {Object} data - the tree data
         * @param {jQueryElement} $parent - the parent node to append the data
         * @param {Boolean} [recurse] - internal recursive condition
         */
        function updateFolders(data, $parent, recurse) {
            if (recurse && data && data.path) {
                if (data.relPath === undefined) {
                    data.relPath = data.path;
                }
                $(folderTpl(data)).appendTo($parent);
            }
            if (data && data.children && _.isArray(data.children) && !data.empty) {
                _.forEach(data.children, function (child) {
                    updateFolders(child, $parent, true);
                });
            }
        }

        /**
         * Update the selectedClass
         * @param {String} path - the folder path
         * @param {Number} total - files in class
         * @param {Number} childrenLimit - page size
         */
        function updateSelectedClass(path, total, childrenLimit) {
            selectedClass = {
                path: path,
                total: total,
                childrenLimit: childrenLimit,
                page: 1
            };
        }
        /**
         * Render pagination
         */
        function renderPagination() {
            var $paginationContainer = $('.pagination-bottom', $container);
            $paginationContainer.empty();
            var totalPages = Math.ceil(selectedClass.total / selectedClass.childrenLimit);

            if (selectedClass.total && totalPages > 1) {
                paginationComponent({
                    mode: 'simple',
                    activePage: selectedClass.page,
                    totalPages: totalPages
                })
                    .on('prev', function () {
                        selectedClass.page--;
                        loadPage();
                    })
                    .on('next', function () {
                        selectedClass.page++;
                        loadPage();
                    })
                    .render($paginationContainer);
            }
        }
        /**
         * Load page
         */
        function loadPage() {
            var subTree = getByPath(fileTree, selectedClass.path);

            //get the folder content
            getFolderContent(subTree, selectedClass.path, function (content) {
                if (content) {
                    //internal event to set the file-selector content
                    $container.trigger('folderselect.' + ns, [content.label, getPage(content.children), content.path]);
                }
            });
        }

    };
});
