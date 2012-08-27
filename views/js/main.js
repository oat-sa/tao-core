require.config({
	baseUrl: taobase_www + 'js',
	paths: {
		jqueryUI: [
			'jquery-ui-1.8.23.custom.min'
		]
	},
	shim: {
		'jqueryUI': ['jquery'],
		'jsTree/plugins/jquery.tree.contextmenu': ['jsTree/jquery.tree'],
		'jsTree/plugins/jquery.tree.checkbox': ['jsTree/jquery.tree'],
		'generis.tree.select': ['generis.tree', 'jsTree/plugins/jquery.tree.checkbox'],
		'generis.tree.browser': ['generis.tree', 'jsTree/plugins/jquery.tree.contextmenu'],
		'grid/tao.grid': ['jquery.jqGrid-4.4.0/js/jquery.jqGrid.min', 'jquery.jqGrid-4.4.0/js/i18n/grid.locale-'+base_lang],
		'grid/tao.grid.downloadFileResource': ['grid/tao.grid'],
		'grid/tao.grid.rowId': ['grid/tao.grid']
	}
});

var helpers;
var uiBootstrap;
var eventMgr;
var uiForm;
var generisActions;

require(['require', 'jquery', 'uiBootstrap', 'class', 'helpers', 'EventMgr', 'uiForm', 'generis.actions', 'jqueryUI'], function (req, $, UiBootstrap, Class, Helpers, EventMgr, UiForm, GenerisActions) {
	helpers = new Helpers();
	uiBootstrap = new UiBootstrap();
	eventMgr = new EventMgr();
	uiForm = new UiForm();
	generisActions = new GenerisActions();
});