<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/quote/attachments.js"></script>
<form id="add_form" method="post" action="/<?=$qORi?>/sendPDF/<?=$id?>/<?=$view?>">
<div id="attachments">
Available Attachments:
	<ul class="treemenu">
<?php
function print_dirs($filelist) {
	foreach ($filelist as $dir => $file) {
		if (is_array($file) && !isset($file['path'])) { // Recurse into directories
			echo '<li class="treenode"><a href="">'.$dir.'</a><ul>';
			print_dirs($file);
			echo '</ul></li>';
		} else { // Print files
			echo '<li><input type="checkbox" id="'.$file['path'].'" name="attachments[]" value="'.$file['path'].'" /> <label for="'.$file['path'].'">'.$file['name'].'</label></li>';
		}
	}
}
// function cmp($a, $b) {
	// if ((is_array($b) && !isset($b['path'])) && !(is_array($a) && !isset($a['path'])))
		// return 1;
	// else
		// return -1;
// }
// log_message('error', print_r($files, true));
// usort($files, "cmp");
// log_message('error', print_r($files, true));
print_dirs($files);
?>
	</ul>
</div>

<div id="assemButtons">
	<input type="submit" value="Send Email!" id="close" onclick="return monitor.init();" />
</div>
</form>
</div>
</body>
</html>
