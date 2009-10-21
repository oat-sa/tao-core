<?if(get_data('trees')):?>
	
<div id="tree-accordion" class="ui-accordion ui-widget ui-helper-reset">
		
	<?foreach(get_data('trees') as $i => $tree):?>
	
	<h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all">
	    <span class="ui-icon"/>
	     <a href="#"><?=(string)$tree['name']?></a>
	  </h3>
	<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" style="padding:0em 0em 1em 1em;">
		<div id="tree-<?=$i?>" ></div>
	</div>
		
	<?endforeach?>
	
</div>
<script type="text/javascript">
	
	$(function(){
		$(".ui-accordion").accordion({
			fillSpace: false,
			autoHeight: false,
			collapsible: true,
			active: 0,
			icons: { 'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus' }
		});
		
		<?foreach(get_data('trees') as $i => $tree):?>
		
		new GenerisTreeClass('#tree-<?=$i?>', "<?=(string)$tree['dataUrl']?>", {
			formContainer: '#main-container',
			classEditable: <?=(!empty($tree['editClassUrl']))?'true':'false'?>,
			editClassAction: "<?=(string)$tree['editClassUrl']?>",
			editInstanceAction: "<?=(string)$tree['editInstanceUrl']?>",
			createInstanceAction: "<?=(string)$tree['addInstanceUrl']?>",
			subClassAction: "<?=(string)$tree['addSubClassUrl']?>"
		});
		
		<?endforeach?>
		
	});
	
</script>
<?endif?>