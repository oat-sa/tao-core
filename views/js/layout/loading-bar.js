/**
 * @author Dieter Raber <dieter@taotesting.com>
 */
define(
    function(){

        var loadingBar = document.getElementsByClassName('loading-bar')[0];

        return {
            start: function() {
                loadingBar.className += ' loading';
            },
            stop: function() {
                loadingBar.className = loadingBar.className.replace(' loading', '');
            }
        };
    });