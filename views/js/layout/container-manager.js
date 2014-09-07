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
            $dataContainerWrapper = $('<div>', { 'class' : 'data-container-wrapper'});
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
                $formContainer = $scope.find('.main-container');

            $scope.find('.data-container').appendTo(getDataContainerWrapper($scope, $formContainer));

            _cleanUp();
        }



    return {
        /**
         * Reposition all boxes in a more consistent fashion
         */
        init : init
    };
});


