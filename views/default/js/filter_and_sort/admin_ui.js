define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	$(document).ready(function(){
	  // disable / enable areas based on plugin activation status
	  $('.hypelists-enabled').prop( "disabled", false );
	  $('.hypelists-disabled').prop( "disabled", true );
		// each time you click a checkbox to update; loops through all the hidden
		$('.list_types input[type=checkbox]').click(function(){
			$('#list_types_hidden').val("");
			$('.list_types input[type=checkbox]').each(function(){
				if ( $(this).is(':checked') ){
					// ugly hack to not render the first comma
					if ( $('#list_types_hidden').val() == ""){
						$('#list_types_hidden').val( $(this).val() );
					}else{
						$('#list_types_hidden').val( $('#list_types_hidden').val() + ',' + $(this).val() );
					}
				}
			});
		});
	});
});
