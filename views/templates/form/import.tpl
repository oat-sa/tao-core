<div class="main-container" data-tpl="tao/import.tpl">
    <h2><?=get_data('formTitle')?></h2>
    <div class="form-content">
        <?=get_data('myForm')?>
    </div>
</div>
<div class="data-container-wrapper"></div>

<?php if(has_data('importErrorTitle')):?>
    <?php if(get_data('importErrors')):?>
        <?php
        $msg = '<div>' . get_data('importErrorTitle') ?get_data('importErrorTitle') :__('Error during file import') . '</div>';
        $msg .= '<ul>';
        foreach(get_data('importErrors') as $ierror) {
            $msg .= '<li><?=$ierror->__toString()?></li>';
        }
        $msg .= '</ul>';
        ?>
    <?php endif?>
    <script>
        require(['ui/feedback'], function(feedback){
            feedback().error(<?=$msg?>);
        });
    </script>
<?php endif ?>


<script type="text/javascript">
require(['jquery'], function($) {
	
	//by changing the format, the form is sent
	$(":radio[name='importHandler']").change(function(){

		var form = $(this).parents('form');
		$(":input[name='"+form.attr('name')+"_sent']").remove();
		
		form.submit();
	});
	
	//for the csv import options
	$("#first_row_column_names_0").attr('checked', true);
	$("#first_row_column_names_0").click(function(){
            $("#column_order").attr('disabled', this.checked);
	});
});
</script>