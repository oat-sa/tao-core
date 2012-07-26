<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TAO</title>
	<script type="text/javascript" src="<?=ROOT_URL?>/tao/views/js/jquery-1.7.2.min.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=ROOT_URL?>/tao/views/css/custom-theme/jquery-ui-1.8.custom.css"/>
	<script type="text/javascript" src="<?=ROOT_URL?>/tao/views/js/tao.ajaxWrapper.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=ROOT_URL?>/tao/views/css/style.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=ROOT_URL?>/tao/views/css/layout.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=ROOT_URL?>/tao/views/css/portal.css"/>
    <script type="text/javascript">
        function isReady(){
            var from = '<?= $from ?>';
            tao.ajaxWrapper.ajax({
                'url' : '<?=ROOT_URL?>/tao/Main/isReady'
                , type: 'GET'
                ,'success' : function(data){
                    window.location = from;
                }
                ,'error' : function(data){
                    // the system is still in maintenance...
                    // we should test the exception type
                }
            });
        }
        $(function(){
            var timer=setInterval("isReady()", 10000);
        });
    </script>
</head>
<body>
	<div id="content">
		<div id="portal-box" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<h1><?=__('Maintenance')?></h1>
			<?=__('The TAO platform is under maintenance.')?>
            <br/><br/><?=__('Please wait a moment or contact your administrator.')?>
		</div>
	</div>
	<div id="footer">
		TAO<sup>&reg;</sup> - <?=date('Y')?> - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>
