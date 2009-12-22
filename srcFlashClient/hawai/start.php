<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
  <head>    
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">    
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>    
    <meta name="Author" content="Raynald Jadoul" />
    <title>PIAAC assessment</title>    
    <style type="text/css">
      body,html{ margin:0px; padding:0px; height:100%; }
      #dialogBox{
        position:absolute;
        padding:6px 0 0 0 0px;
        width:1024px;
        height:768px;
        left:50%;
        top:50%;
        margin-left:-512px;
        margin-top:-384px;
      }
    </style>
  </head>
  <body bgcolor="#ffffff">
   <div id="dialogBox" style="">

<?
$got_preview_mode = $_GET['mode'];
if($got_preview_mode != "item") {
	echo "<object id='testcommand' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' align='middle' width='1024' height='768' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'>    
      <param name='movie' value='./Test.swf?TestXmlFile=Test.xml&subject=XXX&label=YYY&comment=ZZZ&wsdlurl=http://127.0.0.1/piaac.wsdl.php&printResult=on&taoIP=127.0.0.1&noresult=0'/> 
      <param name='allowScriptAccess' value='sameDomain' /> 
      <param name='swLiveConnect' value='true'/> 
      <param name='quality' value='high'/> 
      <param name='allowFullScreen' value='true'/> 
      <param name='bgcolor' value='#ffffff'/> 
      <embed src='./Test.swf?TestXmlFile=Test.xml&subject=XXX&label=YYY&comment=ZZZ&wsdlurl=http://127.0.0.1/piaac.wsdl.php&printResult=on&taoIP=127.0.0.1&noresult=0' pluginspage='http://www.macromedia.com/go/getflashplayer' name='testcommand' bgcolor='#ffffff' type='application/x-shockwave-flash' align='middle' width='1024' height='768' swLiveConnect='true' allowScriptAccess='sameDomain' allowfullscreen='true' quality='high'/> 
    </object>";
} else {
  
  $dir = opendir('../');
  while($file = readdir($dir)) {
    if(filetype('../' . $file) === 'dir' AND preg_match('~.*-OTHER$~', $file)) {
      $stimulus_main_folder = $file;
      break;
    }
  }
	echo "<object id='testcommand' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' align='middle' width='1024' height='768' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'>    
        <param name='movie' value='../common_runtime/tao_item.swf?localXmlFile=../" . $stimulus_main_folder . "/Uxx_en-US.xml'/>  
        <param name='allowScriptAccess' value='sameDomain' /> 
        <param name='swLiveConnect' value='true'/> 
        <param name='quality' value='high'/> 
        <param name='allowFullScreen' value='true'/> 
        <param name='bgcolor' value='#ffffff'/> 
        <embed src='../common_runtime/tao_item.swf?localXmlFile=../" . $stimulus_main_folder . "/Uxx_en-US.xml' pluginspage='http://www.macromedia.com/go/getflashplayer' name='testcommand' bgcolor='#ffffff' type='application/x-shockwave-flash' align='middle' width='1024' height='768' swLiveConnect='true' allowScriptAccess='sameDomain' allowfullscreen='true' quality='high'/> 
      </object>";
}
?>

  </div>
  </body>
</html>