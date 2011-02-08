<b>template</b><br />
<ul>
<?foreach($namespaces as $key => $val):?>
	<li><?=$key?> ::> <?=$val?> </li>
<?endforeach?>
</ul>