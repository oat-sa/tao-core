<div class="data-container-wrapper flex-container-full">
    My task Queue list here....

	<div id="task-queue-list"></div>
</div>

<script>
	requirejs.config({
		config : {
			'controller/users/add' : {
				loginId : <?=json_encode(get_data('loginUri'))?>,
                exit : <?=json_encode(get_data('exit'))?>
            }
        }
	});
</script>