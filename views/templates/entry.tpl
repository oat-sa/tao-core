<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TAO</title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/custom-theme/jquery-ui-1.8.22.custom.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/style.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/layout.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=BASE_WWW?>css/portal.css"/>
</head>
<body>
	<div id="content">
		<div id="portal-box" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<h1><?=__('Welcome to the TAO platform')?></h1>
			  <ul>
			    <li><?=__('Back Office (test creator)')?>: <a href="<?=_url('login','Main','tao')?>" title="<?=__('TAO back office')?>"><?=__('TAO Back Office')?></a></li>
			    <li><?=__('Test Front Office (test takers)')?>: <a href="<?=_url('index','DeliveryServerAuthentification','taoDelivery')?>" title="<?=__('TAO front office')?>"><?=__('TAO Delivery Server')?></a></li>
				<li><?=__('Process Front Office')?>: <a href="<?=_url('index','Authentication','wfEngine')?>" title="<?=__('TAO front office')?>" title="<?=__('TAO workflow engine')?>"><?=__('TAO Workflow Engine')?></a></li>
			</ul>

		</div>
	</div>
	<div id="footer">
		TAO<sup>&reg;</sup> - <?=date('Y')?> - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>