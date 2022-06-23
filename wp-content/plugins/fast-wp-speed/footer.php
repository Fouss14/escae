<!-- Bootstrap core JavaScript -->
<script type="text/javascript">var google_api = "<?php echo $fwps_google_api; ?>";</script>
<script src="asset/script.js"></script>
<script>
  let options = {
    startAngle: -1.55,
    size: 150,
    value: 0,
    fill: 'red',
  }

$(".circle .bar").circleProgress(options).on('circle-animation-progress',
function(event, progress, stepValue){
  $(this).parent().find("span").text(String(stepValue.toFixed(2).substr(2)));
  if (stepValue == 1) {

    $(this).parent().find("span").text('100');
  }else if(stepValue == 0) {

    $(this).parent().find("span").text('0');
  }
});

$(".mobileScore .bar").circleProgress({
  value: '0.6',
});
</script>
