<html>
<head>
	<title></title>

	<link href="<?=ROOT_URL?>/tao/views/css/custom-theme/jquery-ui-1.8.custom.css" type="text/css" rel="stylesheet" />
	<link href="<?=ROOT_URL?>/tao/views/js/jquery.jqGrid-4.2.0/css/ui.jqgrid.css" type="text/css" rel="stylesheet" />
	<link href="<?=ROOT_URL?>/tao/views/css/style.css" type="text/css" rel="stylesheet" />
	<link href="<?=ROOT_URL?>/tao/views/css/layout.css" type="text/css" rel="stylesheet" />
	<link href="<?=ROOT_URL?>/tao/views/css/form.css" type="text/css" rel="stylesheet" />
	<link href="<?=ROOT_URL?>/tao/views/css/widgets.css" type="text/css" rel="stylesheet" />

	<script src="<?=ROOT_URL?>/tao/views/js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/jquery-ui-1.8.custom.min.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/jsTree/jquery.tree.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/jsTree/plugins/jquery.tree.checkbox.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/jquery.jqGrid-4.2.0/js/i18n/grid.locale-en.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/jquery.jqGrid-4.2.0/js/jquery.jqGrid.min.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/generis.tree.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/generis.treeform.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/locales/<?= DEFAULT_LANG ?>/messages_po.js" type="text/javascript"></script>
	<script src="<?=ROOT_URL?>/tao/views/js/i18n.js" type="text/javascript"></script>
	
	<script type="text/javascript">
	var root_url 		= "<?=ROOT_URL?>/";
	var ctx_extension 	= "<?=get_data('extension')?>";
	var ctx_module 		= "<?=get_data('module')?>";
	var ctx_action 		= "<?=get_data('action')?>";
	
	$(function(){
		
		<?if(get_data('reload')):?>
			uiBootstrap.initTrees();
		<?endif?>
	
	});
	</script>
   </head>
<body>
