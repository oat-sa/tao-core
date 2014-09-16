/**
 * @author Dieter Raber <dieter@taotesting.com>
 */
define(['jquery'],
    function ($) {

        var $loadingBar = $('.loading-bar'),
            originalHeight = $loadingBar.height(),
            $win = $(window),
            headerHeight = $('header').height(),
            winHeight = $win.height();

        $win.on('scroll', function () {
            if(!$loadingBar.hasClass('loading')) {
                return;
            }
            var versionWarning = $('.version-warning'),
                _headerHeight = versionWarning.length && versionWarning.is(':visible')
                    ? headerHeight + versionWarning.height() - originalHeight
                    : headerHeight - originalHeight;

            if (_headerHeight <= $win.scrollTop()) {
                $loadingBar.addClass('fixed');
                $loadingBar.height(winHeight - originalHeight);
            }
            else {
                $loadingBar.removeClass('fixed');
                $loadingBar.height(winHeight - _headerHeight);
            }
        });

        return {
            start: function () {
                if($loadingBar.hasClass('loading')) {
                    $loadingBar.stop();
                }
                $loadingBar.addClass('loading');
                $win.trigger('scroll');
            },
            stop: function () {
                $loadingBar.removeClass('loading').height(originalHeight);
            }
        };
    });