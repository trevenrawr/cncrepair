<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
?>

<script type="text/javascript" src="/js/quote/messages.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return saveMessages();">
<div id="hiddenInputs">
</div>
<div id="messages"><?php foreach ($messages as $message): ?>
	<label for="<?=$message['view']?>"><?=$message['name']?></label><br />
	<textarea id="<?=$message['view']?>" name="<?=$message['view']?>" <?php if ($message['view']== 'text_email' || $message['view'] == 'hours') echo 'class="text"'?> rows="15"><?=htmlentities($message['message'])?></textarea><br /><br />
	<?php endforeach; ?>
	<input type="submit" value="Save Messages" />
	</div>
<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	mode: "specific_textareas",
	editor_deselector: "text",
	theme: "advanced"
});
</script>
</form>