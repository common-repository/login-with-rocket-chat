jQuery(document).ready(function () {
	
	var googleEnabled = jQuery("#google_enable").is(":checked");
	var eveEnabled = jQuery("#eve_enable").is(":checked");
	var facebookEnabled = jQuery("#facebook_enable").is(":checked");
	
	if(!googleEnabled) {
		jQuery("#panel2").toggle();
	}
	if(!eveEnabled) {
		jQuery("#panel3").toggle();
	}
	if(!facebookEnabled) {
		jQuery("#panel4").toggle();
	}
	
	//show and hide instructions
    jQuery("#api_help").click(function () {
        jQuery("#api_instru").toggle();
    });
	jQuery("#eve_help").click(function () {
        jQuery("#eve_instru").toggle();
    });
	jQuery("#google_help").click(function () {
        jQuery("#google_instru").toggle();
    });
	jQuery("#facebook_help").click(function () {
        jQuery("#facebook_instru").toggle();
    });
	
	//toggle content
	jQuery("#toggle2").click(function() {
		jQuery("#panel2").toggle();
	});
	jQuery("#toggle3").click(function() {
		jQuery("#panel3").toggle();
	});
	jQuery("#toggle4").click(function() {
		jQuery("#panel4").toggle();
	});

	$idp = jQuery;
	$idp("#mo_idp_users_dd").val('100');
    $idp("#mo_idp_users_dd").change(function(){
        switch($idp("#mo_idp_users_dd").val())
        {
            case '100':
                $idp(".mo_idp_price_slab_100").css("display","table-cell");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '200':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","table-cell");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;    
            case '300':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","table-cell");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '400':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","table-cell");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '500':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","table-cell");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '1000':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","table-cell");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '2000':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","table-cell");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '3000':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","table-cell");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '4000':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","table-cell");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;                    
            case '5000':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","table-cell");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case '5000+':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","table-cell");
                $idp(".mo_idp_price_slab_ul").css("display","none");
                break;
            case 'UL':
                $idp(".mo_idp_price_slab_100").css("display","none");
                $idp(".mo_idp_price_slab_200").css("display","none");
                $idp(".mo_idp_price_slab_300").css("display","none");
                $idp(".mo_idp_price_slab_400").css("display","none");
                $idp(".mo_idp_price_slab_500").css("display","none");
                $idp(".mo_idp_price_slab_1000").css("display","none");
                $idp(".mo_idp_price_slab_2000").css("display","none");
                $idp(".mo_idp_price_slab_3000").css("display","none");
                $idp(".mo_idp_price_slab_4000").css("display","none");
                $idp(".mo_idp_price_slab_5000").css("display","none");
                $idp(".mo_idp_price_slab_5000p").css("display","none");
                $idp(".mo_idp_price_slab_ul").css("display","table-cell");
                break;
        }
    }); 
});

function mo2f_upgradeform(a) {
    jQuery("#requestOrigin").val(a);
    jQuery("#mocf_loginform").submit()
}

function gatherplaninfo(name,users){
    document.getElementById("plan-name").value=name;
    document.getElementById("plan-users").value=users;
    document.getElementById("mo_idp_request_quote_form").submit();
}