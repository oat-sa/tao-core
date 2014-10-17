define(['jquery', 'lodash', 'form/post-render-props'], function ($, _, postRenderProps) {

    /**
     * The data context for actions
     * @typedef {Object} ActionContext
     * @property {String} [uri] - the resource uri
     * @property {String} [classUri] - the class uri
     */

    /**
     * @exports form/property
     */
    var propertyManager = {

        /**
         * Add a new property
         * @param {String} uri
         * @param {String} classUri
         * @param {String} url
         */
        add: function (uri, classUri, url) {
            var $existingProperties = $('.property-block'),
                index = $existingProperties.length;

            $existingProperties.each(function () {
                index = Math.max(parseInt(this.id.replace(/[\D]+/, '')), index);
            });
            index++;

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    index: index,
                    classUri: classUri
                },
                dataType: 'html',
                success: function (response) {
                    response = $(response);
                    // reduce response to a single jquery object by adding the hidden input to the div
                    var property = response.last(),
                        input = response.first();

                    input.appendTo(property);

                    postRenderProps.init(property);
                }
            });
        },
        /**
         * Remove  property
         * @param {String} uri
         * @param {String} classUri
         * @param {String} url
         */
        remove: function (uri, classUri, url, successCallback) {

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    uri: uri,
                    classUri: classUri
                },
                dataType: 'html',
                success: function () {
                    if (_.isFunction(successCallback)) {
                        successCallback();
                    }
                }
            });
        }

    };

    return propertyManager;
});
