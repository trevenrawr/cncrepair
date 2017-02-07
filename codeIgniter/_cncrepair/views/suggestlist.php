<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
</head>

<body>
<?php if (!isset($suggName)) $suggName = $suggType;?>
<ul id="<?=$suggName?>sugglist" class="suggList" onclick="suggestSelect('<?=$suggName?>');">
<?php if (count($suggestions['suggs']) == 0): ?>
	<li id='nosugg' style='color: #777777;'>No suggestions available.</li>
<?php else:
	$i = 1;
	foreach ($suggestions['suggs'] as $suggestion): 
	// $sugg = preg_replace('/[\-\s]/', '', $input);
	?>
	<li id="<?=$suggName?>sugg<?=$i?>" onmouseover="suggestHL(<?=$i++?>, '<?=$suggName?>');"><?php
		echo $suggestion[$suggType];
		if ($suggType == 'modelnum') {
			echo ' <span class="make">[' . $suggestion['make'] . ']</span>';
		} else if ($suggType == 'serial') {
			echo ' <span class="make">['.$suggestion['modelnum'].']</span>';
		}
	?></li>
<?php endforeach; endif; ?>
</ul>
</body>

</html>