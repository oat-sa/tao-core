<?include(TAO_TPL_PATH . 'header.tpl')?>

<div class="main-container">
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=get_data('formTitle')?>
	</div>
	<div id="form-container" class="ui-widget-content ui-corner-bottom">
		<?=get_data('myForm')?>
	</div>
	
	<script type="text/javascript">
	$(function(){
		var $fileImport = $("#file_import"); 
		$fileImport.bind("async_file_uploaded", function(event, data){

			var fileName = data.name;
			var $fileNameField = $('input[id="http%3A%2F%2Fwww__tao__lu%2FOntologies%2Fgeneris__rdf%23FileName"]');
			
			// Catch the file upload and fill the file name field, if it is empty
			if($.trim($fileNameField.val()) == ''){
				//fill the file name field
				$fileNameField.val(fileName.replace(' ', '_'));
			}
		});
		
		
		$('#delete-versioned-file').unbind('click').one('click', function(){
			if(confirm('<?=__()?>')){
				$(this).siblings('input[name=delete]').val(1);
				$('a.form-submiter:first').click();
			}
			return false;
		});
	});
	</script>
</div>

<?include(TAO_TPL_PATH . 'footer.tpl');?>
