<p>Welcome to the CNC Repair Database!<br /><br />
<strong><em>BEWARE! Item information IS NOT CURRENT at the moment.</em></strong><br />
Please bear with us as we work to get the baseline database updated.<br /><br />
Thank you from T-Bag Technologies.</p>

<p><?=date('l, F jS, Y ');?><span id="clock"><?=date('H:i:s');?></span></p>
<script type="text/javascript">
var sec = <?=date('s');?>;
var min = <?=date('i');?>;
var hour = <?=date('H');?>;

var updateClock = function() {
	sec++;
	if (sec >= 60) {
		sec = sec - 60;
		min++;
		if (min >= 60) {
			min = min - 60;
			hour++;
			if (hour >= 24) {
				window.location.reload();
			}
		}
	}
	if (sec < 10) sec = "0"+sec;
	if (min < 10) min = "0"+Number(min);
	if (hour < 10) hour = "0"+Number(hour);
	document.getElementById("clock").innerHTML = hour+":"+min+":"+sec;
	setTimeout("updateClock();", 1000);
}

setTimeout("updateClock();", 1000);
</script>