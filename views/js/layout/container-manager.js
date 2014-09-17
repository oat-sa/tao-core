define([
    'jquery'
],
    function($){

        /**
         * Wrapper for all data-container, create if it doesn't exist yet
         *
         * @param $scope
         * @param $formContainer
         * @returns {*}
         */
        function getDataContainerWrapper($scope, $formContainer) {
            var $dataContainerWrapper = $scope.find('.data-container-wrapper');
            if($dataContainerWrapper.length) {
                return $dataContainerWrapper;
            }

            // list container is similar though they are in the wrong position
            $dataContainerWrapper = $scope.find('#list-container');
            if($dataContainerWrapper.length) {
                $dataContainerWrapper.addClass('data-container-wrapper');
            }
            else {
                $dataContainerWrapper = $('<div>', { 'class' : 'data-container-wrapper'});
            }

            $formContainer.after($dataContainerWrapper);
            return $dataContainerWrapper;
        }


        /**
         * Remove orphaned boxes
         *
         * @private
         */
        function _cleanUp() {
            $('#test-left-container').remove();
        }


        /**
         * Init repositioning and clean up
         */
        function init() {
            var $scope = $('.content-block'),
                $formContainer = $scope.find('.main-container'),
                $dataContainer = $scope.find('.data-container');

            $dataContainer.appendTo(getDataContainerWrapper($scope, $formContainer));
            $dataContainer.find('> div:last').addClass('data-container-footer');

            _cleanUp();
        }



    return {
        /**
         * Reposition all boxes in a more consistent fashion
         */
        init : init
    };
});


