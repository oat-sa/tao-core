<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TAO</title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/custom-theme/jquery-ui-1.8.22.custom.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/style.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/layout.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/portal.css"/>
	 <script src="<?=BASE_WWW?>js/jquery-1.8.0.min.js"></script>
</head>
    <script type="text/javascript">
	$( document ).ready(function(){
	    $('.portalButton').mouseover(function() {
		$(this).addClass("portalButtonSelected");;
	    });
	    $('.portalButton').mouseleave(function() {
		$(this).removeClass("portalButtonSelected");;
	    });
	});
    </script>

<body>
	<div id="content">
		<div id="portal-box" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<h1><?=__('Welcome to the TAO platform')?></h1>

		<a href="<?=_url('login','Main','tao')?>" title="<?=__('TAO back office')?>">
		    <span class="portalButton">
			    <?=__('Advanced Users')?>
			    <span class="hintMsg">
				<?=__('Create new tests or items, register test takers or watch results ...')?>
			    </span>
			    <span class="portalOperation">
				<?=__('TAO Back Office')?>
			    </span>

		    </span>
		</a>
		<a href="<?=_url('index','Authentication','wfEngine')?>" title="<?=__('TAO front office')?>" title="<?=__('TAO workflow engine')?>">
		<span class="portalButton">
			<?=__('Advanced Users')?>
			<span class="hintMsg">
			    <?=__('Check pending tasks for assessment preparation ...')?>
			</span>
			<span class="portalOperation">
			   <?=__('TAO Workflow Engine')?>
			</span>
			</a>
		</span>
		</a>
		<a href="<?=_url('index','DeliveryServerAuthentification','taoDelivery')?>" title="<?=__('TAO front office')?>">
		<span class="portalButton">
			<?=__('Test Takers')?>
			<span class="hintMsg">
			    <?=__('Check or take online tests available to you ...')?>
			</span>
			<span class="portalOperation">
			    <?=__('TAO Delivery Server')?>
			</span>
			
		</span>
		</a>
		
		

		</div>
	</div>
	<div id="footer">
		TAO<sup>&reg;</sup> - <?=date('Y')?> - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>