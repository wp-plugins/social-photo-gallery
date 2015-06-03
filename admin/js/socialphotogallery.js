(function(jQuery) {
	jQuery(document).ready( function() {
            
            var d = new Date();
            var strDate = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
            
    jQuery( "#album_date" ).datepicker({
	showOn: "both",
	buttonImage: jQuery('#calendar_image_path').val(),
	buttonImageOnly: true,
	dateFormat: 'yy-mm-dd',
	altField: 'Select Date' ,
	//minDate: 0,
        current: strDate
	});
	});
})(jQuery);