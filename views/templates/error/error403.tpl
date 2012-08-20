<?php header("HTTP/1.0 403 Forbidden"); ?>
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>
	<b>Error: </b>
	<p><?php echo isset($message)?$message:__("Access forbidden"); ?>
	</p>

	<?php if (isset($login) && !empty($login)) { ?>
	<b><?=__('Your authentication expired')?>
	</b>
	<p><?php echo __("User name:").' '.$login; ?>
	</p>
	<div>
		<p></p>
		<ul>
			<li><?=__('Back Office (test creator)')?>
				:
				<a href="<?=_url('login','Main','tao')?>" title="<?=__('TAO back office')?>"><?=__('TAO Back Office')?>
				</a>
			</li>
			<li><?=__('Test Front Office (test takers)')?>
				:
				<a
					href="<?=_url('login','DeliveryServerAuthentification','taoDelivery')?>"
					title="<?=__('TAO front office')?>"><?=__('TAO Delivery Server')?>
				</a>
			</li>
			<li><?=__('Process Front Office')?>
				:
				<a href="<?=_url('index','Authentication','wfEngine')?>" title="<?=__('TAO front office')?>"
					title="<?=__('TAO workflow engine')?>"><?=__('TAO Workflow Engine')?>
				</a>
			</li>
		</ul>

	</div>
	<?php } ?>
	


</body>
</html>