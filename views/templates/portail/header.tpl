<html>
<head>
	<title></title>

	<script src="<?=TAOBASE_WWW?>js/require-jquery.js"></script>

	<?=tao_helpers_Scriptloader::render()?>

	<script src="<?=TAOBASE_WWW?>js/main.js"></script>

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
