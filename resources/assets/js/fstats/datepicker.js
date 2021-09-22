/* fs-datepicker */

$(function(){
	//container
	$('#fs-datepicker').closest('.container')
	.addClass('position-relative')
	.prepend('<div id="fs-calendar"></div>');
	
	//datepicker
	$('#fs-datepicker').datepicker({
		container: '#fs-calendar',
		format: 'yyyy-mm-dd',
		todayHighlight: true,
		todayBtn: true,
		useCurrent: false,
		autoclose: true,
		orientation: 'right',
		//debug: true,
		//startDate: START_DATE,
		//endDate: END_DATE,
	})
	.on('changeDate', function(e){
		let date = e.format().trim();
		$(document).trigger('fs-matches', date);
	});
});


