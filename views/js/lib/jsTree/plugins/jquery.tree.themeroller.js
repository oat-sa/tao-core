(function ($) {
	$.extend($.tree.plugins, {
		"themeroller" : {
			defaults : {

			},
			callbacks : {
				oninit : function (t) {
					if(this.settings.ui.theme_name != "themeroller") return;
					var opts = $.extend(true, {}, $.tree.plugins.themeroller.defaults, this.settings.plugins.themeroller);
					this.container.addClass("ui-widget ui-widget-content");
					$(document).on('mouseover', "#" + this.container.attr("id") + " li a", function () { $(this).addClass("ui-state-hover"); });
					$(document).on('mouseout', "#" + this.container.attr("id") + " li a",  function () { $(this).removeClass("ui-state-hover"); });
				},
				onparse : function (s, t) {
					if(this.settings.ui.theme_name != "themeroller") return;
					var opts = $.extend(true, {}, $.tree.plugins.themeroller.defaults, this.settings.plugins.themeroller);
					return $(s).find("a").not(".ui-state-default").addClass("ui-state-default").children("ins").addClass("ui-icon").end().end().end();
				},
				onselect : function(n, t) {
					if(this.settings.ui.theme_name != "themeroller") return;
					var opts = $.extend(true, {}, $.tree.plugins.themeroller.defaults, this.settings.plugins.themeroller);
					$(n).children("a").addClass("ui-state-active");
				},
				ondeselect : function(n, t) {
					if(this.settings.ui.theme_name != "themeroller") return;
					var opts = $.extend(true, {}, $.tree.plugins.themeroller.defaults, this.settings.plugins.themeroller);
					$(n).children("a").removeClass("ui-state-active");
				}
			}
		}
	});
})(jQuery);
