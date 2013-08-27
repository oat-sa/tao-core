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

		<table>
		    <tr>
			<td>
			<span class="hintMsg">
			    <?=__('Create new tests or items, register test takers or watch results ...(Advanced Users)')?>
			</span>
			</td>
		    </tr>
		    <tr>
			<td>
			<span class="portalOperation">
			    <a href="<?=_url('login','Main','tao')?>" title="<?=__('TAO back office')?>"><?=__('TAO Back Office')?></a>
			</span>
			</td>
		    </tr>
			<tr/>
		    <tr>
			<td>
			<span class="hintMsg">
			    <?=__('Take Tests ...(Test Takers)')?>
			</span>
			</td>
		    </tr>
			<tr/>
		    <tr>
			<td>
			<span class="portalOperation">
			    <a href="<?=_url('index','DeliveryServerAuthentification','taoDelivery')?>" title="<?=__('TAO front office')?>"><?=__('TAO Test Delivery Server')?></a>
			</span>
			</td>
		    </tr>
			<tr/>
		    <tr>
			<td>
			<span class="hintMsg">
			   <?=__('Check pending tasks for assessment preparation (Advanced Users)')?>
		       </span>
			</td>
		    </tr>
		    <tr>
			<td>
			<span class="portalOperation">
			    <a href="<?=_url('index','Authentication','wfEngine')?>" title="<?=__('TAO front office')?>" title="<?=__('TAO workflow engine')?>"><?=__('TAO Workflow Engine')?></a>
			</span>
		    </td>
		    </tr>
		</table>

		</div>
	</div>
	<div id="footer">
		TAO<sup>&reg;</sup> - <?=date('Y')?> - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>