$(function(){
	
	/* dashboar initialization */
	/* for elements just call initElements() and same for other sections */
	
	initCommon();
	initForms();
	initMenu();
	
	
});

$(document).ready(function(){
	$(".votifier").css("display","none");
	$(".minequery").css("display","none");

	$("#votifier_enabled").click(function(){
		// If checked
		if ($("#votifier_enabled").is(":checked")) {
			$(".votifier").hide("fast");
		} else {
			$(".votifier").show("fast");
		}
        });
	$("#minequery_enabled").click(function(){
		// If checked
		if ($("#minequery_enabled").is(":checked")) {
			$(".minequery").hide("fast");
		} else {
			$(".minequery").show("fast");
		}
        });
});