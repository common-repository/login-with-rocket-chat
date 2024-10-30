<?php


function rocketchat_oauth_server_page() {

	$currenttab = "";
	if(isset($_GET['tab']))
		$currenttab = stripslashes($_GET['tab']);
	?>
	<?php
		if(mo_oauth_server_is_curl_installed()==0){ ?>
			<p style="color:red;">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled. Please install/enable it before you proceed.)</p>
		<?php
		}
	?>
	<div id="mo_oauth_settings">
	<?php
        if ( $currenttab == 'licensing' || ! get_option( 'mo_oauth_show_mo_server_message' ) ) {
            ?>
            <form name="f" method="post" action="" id="mo_oauth_mo_server_form">
			<?php wp_nonce_field('mo_oauth_mo_server_form','mo_oauth_mo_server_form_field'); ?>
                <input type="hidden" name="option" value="mo_oauth_mo_server_message"/>
                <div class="notice notice-info" style="padding-right: 38px;position: relative;">
                    <h4>If you are looking for an OAuth Server,you can try out <a href="https://idp.miniorange.com" target="_blank">miniOrange On-Premise OAuth Server</a>.</h4>
                    <button type="button" class="notice-dismiss" id="mo_oauth_mo_server"><span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            </form>
            <script>
                jQuery("#mo_oauth_mo_server").click(function () {
                    jQuery("#mo_oauth_mo_server_form").submit();
                });
            </script>
            <?php
        }
		?>
<div class="mo_tutorial_overlay" id="mo_tutorial_overlay" hidden></div>
<div id="tab">
	<h2 class="nav-tab-wrapper">
        <a class="nav-tab <?php if($currenttab == 'config'|| $currenttab === '') echo 'nav-tab-active';?>" href="admin.php?page=mo_oauth_server_settings&tab=config">OAuth Clients</a>
        <a class="nav-tab <?php if($currenttab == 'attributemapping') echo 'nav-tab-active';?>" href="admin.php?page=mo_oauth_server_settings&tab=attributemapping">Server Response</a>
		<a class="nav-tab <?php if($currenttab == 'general_settings') echo 'nav-tab-active';?>" href="admin.php?page=mo_oauth_server_settings&tab=general_settings">Configurations</a>
		<a class="nav-tab <?php if($currenttab == 'requestfordemo') echo 'nav-tab-active';?>" href="admin.php?page=mo_oauth_server_settings&tab=requestfordemo">Request For Demo</a>
		<a class="nav-tab <?php if($currenttab == 'login') echo 'nav-tab-active';?>" href="admin.php?page=mo_oauth_server_settings&tab=login">Account Setup</a>
		<a class="nav-tab <?php if($currenttab == 'licensing') echo 'nav-tab-active';?>" href="admin.php?page=mo_oauth_server_settings&tab=licensing">Licensing Plans</a>
	</h2>
</div>
	<div class="miniorange_container">
		<table style="width:100%;">
			<tr>
				<td style="vertical-align:top;width:65%;">

		<?php
	if ($currenttab == 'licensing') {
		mo_oauth_server_app_licensing();
	} else if($currenttab == 'advanced_settings') {
		rocketchat_oauth_server_advanced_settings();
	} else if($currenttab == 'general_settings') {
		rocketchat_oauth_server_general_settings();
	} else if($currenttab == 'attributemapping') {
		rocketchat_oauth_server_attribute_mapping();
	} else if($currenttab == 'faq') {
		rocketchat_oauth_server_faq();
	} else if($currenttab == 'requestfordemo') {
		rocketchat_oauth_server_requestfordemo();
	} else if($currenttab == 'config' || $currenttab === '') {
		rocketchat_oauth_server_apps_config();
	} else if(get_option ( 'goto_registration' ) == true || $currenttab == 'login') {
		if ( 'MO_OAUTH_REGISTRATION_COMPLETE' === get_option( 'mo_oauth_registration_status' ) || 'MO_OAUTH_CUSTOMER_RETRIEVED' === get_option( 'mo_oauth_registration_status' ) || boolval( mo_oauth_server_is_customer_registered() ) ) {
			rocketchat_oauth_show_customer_details();
		} else if (get_option ( 'verify_customer' ) == 'true') {
			rocketchat_oauth_server_show_verify_password_page();
		} else if (trim ( get_option ( 'mo_oauth_admin_email' ) ) != '' && trim ( get_option ( 'mo_oauth_admin_api_key' ) ) == '' && get_option ( 'new_registration' ) != 'true') {
			rocketchat_oauth_server_show_new_registration_page();
		} else if(get_option('mo_oauth_registration_status') == 'MO_OTP_DELIVERED_SUCCESS' || get_option('mo_oauth_registration_status') == 'MO_OTP_VALIDATION_FAILURE' ){
			rocketchat_oauth_server_show_otp_verification();
		} else if(get_option('mo_oauth_registration_status') == 'MO_OTP_DELIVERED_SUCCESS_PHONE' || get_option('mo_oauth_registration_status') == 'MO_OTP_VALIDATION_FAILURE' ){
			rocketchat_oauth_server_show_otp_verification_phone();
		} else {
			delete_option ( 'password_mismatch' );
			rocketchat_oauth_server_show_new_registration_page();
		}
	}else if($currenttab == 'requestforquote') { 
		mo_oauth_server_requestforquote();
	}else {
		rocketchat_oauth_server_apps_config();
	}
	?>
			</td>
					<?php if($currenttab != 'licensing') { ?>
					<td style="vertical-align:top;padding-left:1%;">
						<?php echo rocketchat_oauth_server_miniorange_support(); ?>
						<br><br/>
					</td>
					<?php } ?>
				</tr>
			</table>

		</div>
		<?php
}
function rocketchat_oauth_show_customer_details(){
	?>
	<div class="mo_table_layout" >
		<h2>Thank you for registering with miniOrange.</h2>

		<table border="1"
		   style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:85%">
		<tr>
			<td style="width:45%; padding: 10px;">miniOrange Account Email</td>
			<td style="width:55%; padding: 10px;"><?php echo get_option( 'mo_oauth_admin_email' ); ?></td>
		</tr>
		<tr>
			<td style="width:45%; padding: 10px;">Customer ID</td>
			<td style="width:55%; padding: 10px;"><?php echo get_option( 'mo_oauth_admin_customer_key' ) ?></td>
		</tr>
		</table>
		<br /><br />

	<table>
	<tr>
	<td>
	<form name="f1" method="post" action="" id="mo_oauth_goto_login_form">
	<?php wp_nonce_field('mo_oauth_goto_login_form','mo_oauth_goto_login_form_field'); ?>
		<input type="hidden" value="change_miniorange" name="option"/>
		<input type="submit" value="Change Email Address" class="button button-primary button-large"/>
	</form>
	</td><td>
	<a href="<?php echo add_query_arg( array( 'tab' => 'licensing' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>"><input type="button" class="button button-primary button-large" value="Check Licensing Plans"/></a>
	</td>
	</tr>
	</table>

				<br />
	</div>

	<?php
}
function rocketchat_oauth_server_show_new_registration_page() {
	if(mo_oauth_server_is_customer_registered()) {
		rocketchat_oauth_show_customer_details();
	} else {

	update_option ( 'new_registration', 'true' );
	$current_user = wp_get_current_user();
	?>
			<!--Register with miniOrange-->
		<form name="f" method="post" action="">
		<?php wp_nonce_field('mo_oauth_register_customer_form','mo_oauth_register_customer_form_field'); ?>
			<input type="hidden" name="option" value="mo_oauth_register_customer" />
			<div class="mo_table_layout">
				<div id="toggle1" class="panel_toggle">
					<h3>Register with miniOrange</h3>
				</div>
				<div id="panel1">
					
					<p style="font-size:14px;"><b>Why should I register? </b></p>
	                    <div id="help_register_desc" style="background: aliceblue; padding: 10px 10px 10px 10px; border-radius: 10px;">
	                        You should register so that in case you need help, we can help you with step by step instructions.
	                        <b>You will also need a miniOrange account to upgrade to the premium version of the plugins.</b> We do not store any information except the email that you will use to register with us.
	                    </div>
                    </p>
					<table class="mo_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo get_option('mo_oauth_admin_email');?>" />
							</td>
						</tr>
						<tr class="hidden">
							<td><b><font color="#FF0000">*</font>Website/Company Name:</b></td>
							<td><input class="mo_table_textbox" type="text" name="company"
							required placeholder="Enter website or company name"
							value="<?php echo $_SERVER['SERVER_NAME']; ?>"/></td>
						</tr>
						<tr class="hidden">
							<td><b>&nbsp;&nbsp;First Name:</b></td>
							<td><input class="mo_openid_table_textbox" type="text" name="fname"
							placeholder="Enter first name" value="<?php echo $current_user->user_firstname;?>" /></td>
						</tr>
						<tr class="hidden">
							<td><b>&nbsp;&nbsp;Last Name:</b></td>
							<td><input class="mo_openid_table_textbox" type="text" name="lname"
							placeholder="Enter last name" value="<?php echo $current_user->user_lastname;?>" /></td>
						</tr>
						<tr class="hidden">
							<td></td>
							<td>We will call only if you need support.</td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							<td><input class="mo_table_textbox" required type="password"
								name="password" placeholder="Choose your password (Min. length 8)" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
							<td><input class="mo_table_textbox" required type="password"
								name="confirmPassword" placeholder="Confirm your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><br><input type="submit" name="submit" value="Register" class="button button-primary button-large"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" name="mo_oauth_goto_login" id="mo_oauth_goto_login" value="Already have an account?" class="button button-primary button-large"/>&nbsp;&nbsp;</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
		<form name="f1" method="post" action="" id="mo_oauth_goto_login_form">
		<?php wp_nonce_field('mo_oauth_goto_login_form','mo_oauth_goto_login_form_field'); ?>
            <?php wp_nonce_field("mo_oauth_goto_login");?>
                <input type="hidden" name="option" value="mo_oauth_goto_login"/>
            </form>
            <script>
            	jQuery("#phone").intlTelInput();
                jQuery('#mo_oauth_goto_login').click(function () {
                    jQuery('#mo_oauth_goto_login_form').submit();
                } );
            </script>
	

		<?php
		
	}
}
function rocketchat_oauth_server_show_verify_password_page() {
	?>
			<!--Verify password with miniOrange-->
		<form name="f" method="post" action="">
		<?php wp_nonce_field('mo_oauth_verify_password_form','mo_oauth_verify_password_form_field'); ?>
			<input type="hidden" name="option" value="mo_oauth_verify_customer" />
			<div class="mo_table_layout">
				<div id="toggle1" class="panel_toggle">
					<h3>Login with miniOrange</h3>
				</div>
				<div id="panel1">
					</p>
					<table class="mo_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo get_option('mo_oauth_admin_email');?>" /></td>
						</tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_table_textbox" required type="password"
							name="password" placeholder="Choose your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" name="submit" value="Login"
								class="button button-primary button-large" />&nbsp;&nbsp;</form>

									<input type="button" name="back-button" id="mo_oauth_back_button" onclick="document.getElementById('mo_oauth_change_email_form').submit();" value="Back" class="button button-primary button-large" />

								<form id="mo_oauth_change_email_form" method="post" action="">
									<?php wp_nonce_field('mo_oauth_change_email_form','mo_oauth_change_email_form_field');?>
									<input type="hidden" name="option" value="mo_oauth_change_email" />
								</form></td>
							</td>
									&nbsp;&nbsp;
									<a target="_blank" href="<?php echo get_option('host_name') . "/moas/idp/userforgotpassword"; ?>">Forgot
									your password?</a></td>
						</tr>
					</table>
				</div>
			</div>
	
		<?php
}

function rocketchat_oauth_server_faq() {
	echo '<div class="mo_table_layout">
	<object type="text/html" data="https://faq.miniorange.com/kb/oauth-server/" width="100%" height="600px" >
	</object>
</div>';
}

function rocketchat_oauth_server_requestfordemo(){
			$democss = "width: 350px; height:35px;";
		?>
			<div class="mo_table_layout">
			    <h3> Demo Request Form : </h3>
			    	<form method="post" action="">
					<?php wp_nonce_field('mo_oauth_request_demo_form','mo_oauth_request_demo_form_field'); ?>
					<input type="hidden" name="option" value="mo_oauth_server_demo_request_form" />
			    	<table cellpadding="4" cellspacing="4">
						<tr>
						  	<td><strong>Usecase : </strong></td>
							<td>
							<textarea type="text" minlength="10" name="mo_oauth_server_demo_usecase" style="resize: vertical; width:350px; height:100px;" rows="4" placeholder="Write us about your usecase. (Example - login into WP using Cognito)" required value="" /></textarea>
							</td>
					  	</tr>
			    		<tr>
							<td><strong>Email : </strong></td>
							<td><input required type="text" style="<?php echo $democss; ?>" name="mo_oauth_server_demo_email" placeholder="Email for demo setup" value="<?php echo get_option("mo_oauth_admin_email"); ?>" /></td>
						</tr>
						<tr>
							<td><strong>Request a demo for : </strong></td>
							<td>
								<select required style="<?php echo $democss; ?>" name="mo_oauth_server_demo_plan" id="mo_oauth_server_demo_plan_id">
									<option disabled selected>------------------ Select ------------------</option>
									<option value="Lite Plan">Lite Plan</option>
									<option value="WP OAuth Server Premium Plugin">WP OAuth Server Premium Plugin</option>
									<option value="All Inclusive Plan">All-Inclusive Plan</option>
									<option value="Not Sure">Not Sure</option>
								</select>
							</td>
					  	</tr>
					  
			    	</table>
			    	<p align="center">
			    		<input type="submit" name="submit" value="Submit Demo Request" class="button button-primary button-large" />
			    	</p>
			    
			</form>
			</div>
			
		<?php
		}

function rocketchat_oauth_server_sign_in_settings(){
	?>

	<div class="mo_table_layout">
		<h2>Sign in options</h2>

		<h4>Option 1: Use a Widget</h4>
		<ol>
			<li>Go to Appearances > Widgets.</li>
			<li>Select <b>"miniOrange OAuth"</b>. Drag and drop to your favourite location and save.</li>
		</ol>

		<h4>Option 2: Use a Shortcode</h4>
		<ul>
			<li>Place shortcode <b>[mo_oauth_login]</b> in wordpress pages or posts.</li>
		</ul>
	</div>
	<?php
}

function rocketchat_oauth_server_app_howtosetup(){
	?>
	<style>
		.tableborder {border-collapse: collapse;width: 100%;border-color:#eee;}
		.tableborder th, .tableborder td {text-align: left;padding: 8px;border-color:#eee;}
		.tableborder tr:nth-child(even){background-color: #f2f2f2}
		#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
	</style>

	<?php

	  
	$MoRocketchatServerDb = new MoRocketchatServerDb();
	$clientlist = $MoRocketchatServerDb->get_clients();
	?>
	

	<div class="mo_table_layout">
		<h2>Add Redirect URL (do not include a trailing “/”)</h2>

		<form action="#" method="post">
		<?php wp_nonce_field('mo_oauth_client_redirect_url_form','mo_oauth_client_redirect_url_form_field'); ?>
			<input type="hidden" name="option" value="redirectUrlSave">
		<table class="mo_settings_table">
			<tr><td><b><strong>Client Name</b> : </strong></td><td><?php echo "Rocket Chat"?><!-- <?php echo $clientlist[0]->client_name; ?> --></td></tr>
			<tr></tr>
			<tr><td><b><strong>Setup Guide</b> :</strong></td>  <td><a href="https://plugins.miniorange.com/step-by-step-guide-to-setup-login-into-rocket-chat-with-wordpress" target="_blank">Click here for step by step setup guide</a></td></tr>

			<tr></tr>
			<tr></tr>
			<tr></tr>
		<tr id="redirect_uri">
				<td><strong><font color="#FF0000">*</font>Redirect URL :</strong></td>
				<td><input class="mo_table_textbox" required="" pattern="https?://.+" type="url" name="mo_oauth_client_redirect_url" value="<?php if(!empty($clientlist[0]->redirect_uri)){echo $clientlist[0]->redirect_uri;} ?>"></td>

			</tr>
			<tr>
				<td><input id="client_save" type="submit" name="submit" value="Save Client"
					class="button button-primary button-large" /></td>
			</tr>

		</table>
		</form>

	</div>

	<div id="how_to_setup" class="mo_table_layout">
	<h2>Client Credentials</h2>
	<p>You can configure below Client ID & Client Secret in your OAuth client.<p>
	<hr>
	<table class="tableborder">
	<?php
		$plugin_folder_name =basename(dirname(__FILE__));
		$base_url_endpoints = site_url()."/";
		if(is_multisite()){
			$base_url_endpoints = network_site_url();
		}
	?>

	<form id="form-common" name="form-common" method="post" action="admin.php?page=mo_oauth_server_settings&tab=config">
    <?php wp_nonce_field('mo_oauth_add_client_form','mo_oauth_add_client_form_field'); ?>
    <br><a href='admin.php?page=mo_oauth_server_settings&tab=config&action=add'><button class="button button-primary button-large" style='float:right' id="add_client"  . $disabled . >Regenerate</button></a>
	<input type="hidden" name="option" value="mo_oauth_add_client" />
    <tr><td><b>Client ID </b> : </td><td><?php echo $clientlist[0]->client_id; ?></td></tr>
    <tr><td><b>Client Secret </b> : </td><td><?php echo $clientlist[0]->client_secret; ?></td></tr>
</form>
</table></div>

    <div id="how_to_setup" class="mo_table_layout">
     <h2>Endpoints</h2>
     <p>You can configure below endpoints in your OAuth client.<p>
     <hr>
	<table class="tableborder">
	<tr><td><b>Token Sent Via</b> : </td><td>Header</td></tr>
    <tr><td><b>Identity Token Sent Via</b> : </td><td>Payload</td></tr>
	<tr><td><b>Token Path </b> : </td><td><?php echo $base_url_endpoints;?>wp-json/moserver/token</td></tr>
	<tr><td><b>Identity Path </b> : </td><td><?php echo $base_url_endpoints;?>wp-json/moserver/resource</td></tr>
	<tr><td><b>Authorize Path </b> : </td><td><?php echo $base_url_endpoints;?>wp-json/moserver/authorize</td></tr>
	<tr><td><b>Supported scopes</b> : </td><td>profile openid</td></tr>	
	</table>
	</div>


	<?php
}

function mo_oauth_server_requestforquote() {
	$email 			= get_option("mo_oauth_admin_email");
	$phone 			= get_option("mo_oauth_admin_phone");

	if( array_key_exists('plan_name', $_REQUEST) )
	{
		if($_REQUEST['plan_name'] == 'lite_monthly')
		{
			$plan = "mo_lite_monthly";
			$plan_desc = "LITE PLAN - Monthly";
			$users = "5000+";
		}
		else if($_REQUEST['plan_name'] == 'lite_yearly')
		{
			$plan = "mo_lite_yearly";
			$plan_desc = "LITE PLAN - Yearly";
			$users = "5000+";
		}
		else if($_REQUEST['plan_name'] == 'wp_yearly')
		{
			$plan = "mo_wp_yearly";
			$plan_desc = "PREMIUM PLAN - Yearly";
			if($_REQUEST['plan_users'] == '5K')
				$users = "5000+";
			else
				$users = "Unlimited";
		}
		else if($_REQUEST['plan_name'] == 'all_inclusive')
		{
			$plan = "mo_all_inclusive";
			$plan_desc = "All Inclusive Plan";
			$users = "";
		}	
	}

	if(isset($plan) || isset($users))
	{
		$request_quote = "Any Special Requirements: ";
	}
	else
	{
		$request_quote = "";
	}

	echo '

	<div class="mo_idp_divided_layout mo-idp-full">
        <div class="mo_idp_table_layout mo-idp-center">
            <h2>SUPPORT</h2><hr>
            <p>Need any help? Just send us a query so we can help you.</p>
            <form method="post" action="">
                <input type="hidden" name="option" value="mo_oauth_contact_us_query_option" />
                <table class="mo_idp_settings_table">
                    <tr>
                        <td colspan=4>
                            <input  type="email" 
                                    class="mo_idp_table_contact" required 
                                    placeholder="Enter your Email" 
                                    name="mo_oauth_contact_us_email" 
                                    value="'.$email.'">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=4>
                            <input  type="tel" 
                                    id="contact_us_phone" 
                                    pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" 
                                    placeholder="Enter your phone number with country code (+1)" 
                                    class="mo_idp_table_contact" 
                                    name="mo_oauth_contact_us_phone" 
                                    value="'.$phone.'">
                        </td>
                    </tr>';
    if (!empty($plan))
    {
        echo '      <tr>
                        <td style="padding:10px; width: auto;">
                            <label for="plan-name-dd">Choose a plan:</label>
                        </td>
                        <td style="padding:10px; width: auto;">    
                            <select name="mo_idp_upgrade_plan_name" id="plan-name-dd">
                                <option value="lite_monthly"
                                '.(!empty($plan) && strpos($plan,'lite_monthly') ? 'selected' : '').'>
                                    Cloud IDP Lite - Monthly Plan
                                </option>
                                <option value="lite_yearly"
                                '.(!empty($plan) && strpos($plan,'lite_yearly') ? 'selected' : '').'>
                                    Cloud IDP Lite - Yearly Plan
                                </option>
                                <option value="wp_yearly"
                                '.(!empty($plan) && strpos($plan,'wp_yearly') ? 'selected' : '').'>
                                    WordPress Premium - Yearly Plan
                                </option>
                                <option value="all_inclusive"
                                '.(!empty($plan) && strpos($plan,'all_inclusive') ? 'selected' : '').'>
                                    All Inclusive Plan
                                </option>
                            </select>
                        </td>
                        <td style="padding:10px; width: auto;">
                            Number of users: 
                        </td>
                        <td style="padding:10px; width: auto;">    
                            <input  type="text"
                                    name="mo_idp_upgrade_plan_users"
                                    value="'.(!empty($users)? $users : '').'">
                        </td>
                    </tr>';
    }
    echo '          <tr>
                        <td colspan=4>
                            <textarea   class="mo_idp_table_contact" 
                                        onkeypress="mo_idp_valid_query(this)" 
                                        onkeyup="mo_idp_valid_query(this)" 
                                        placeholder="Write your query here" 
                                        onblur="mo_idp_valid_query(this)" required 
                                        name="mo_oauth_contact_us_query" 
                                        rows="4" 
                                        style="resize: vertical;">'.$request_quote.'</textarea>
                        </td>
                    </tr>
                </table>
                <br>
                <input  type="submit" 
                        name="submit" 
                        value="Submit Query" 
                        style="width:110px;" 
                        class="button button-primary button-large" />
    
            </form>
            <p>
                If you want custom features in the plugin, just drop an email to 
                <a href="mailto:info@xecurify.com">info@xecurify.com</a>.
            </p>
        </div>
    </div>
    
        <script>
            function moSharingSizeValidate(e){
                var t=parseInt(e.value.trim());t>60?e.value=60:10>t&&(e.value=10)
            }
            function moSharingSpaceValidate(e){
                var t=parseInt(e.value.trim());t>50?e.value=50:0>t&&(e.value=0)
            }
            function moLoginSizeValidate(e){
                var t=parseInt(e.value.trim());t>60?e.value=60:20>t&&(e.value=20)
            }
            function moLoginSpaceValidate(e){
                var t=parseInt(e.value.trim());t>60?e.value=60:0>t&&(e.value=0)
            }
            function moLoginWidthValidate(e){
                var t=parseInt(e.value.trim());t>1000?e.value=1000:140>t&&(e.value=140)
            }
            function moLoginHeightValidate(e){
                var t=parseInt(e.value.trim());t>50?e.value=50:35>t&&(e.value=35)
            }
        </script>';
?>
    <?php
}

function rocketchat_oauth_server_advanced_settings() {
    $enable_oidc = (bool) get_option('mo_oauth_server_enable_oidc') ? get_option('mo_oauth_server_enable_oidc') : 'on';
    echo '<div id="enable_oidc" class="mo_table_layout">';
	echo '
	<form name="f" method="post" action="" style="padding: 10px;">
		<div id="toggle3" class="panel_toggle">
			<table style="width:100%">
				<tr>
					<td><h3>OpenID Connect</h3></td>
					<td align="right" style="margin-left:0">[<a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/openid-support"><b>Click here</b></a> to know how this is useful]</td></tr>
			</table>
		</div>
		<div id="mo_server_enable_oidc">
			Enable or Disable the support for OpenID Connect Protocol.<br><br>
			<label class="mo_switch">
                <input autocomplete="off" onclick="turnOff(this, \'mo_server_oidc_toggle\')"';
                if($enable_oidc === 'on') { echo "checked"; }
                echo ' type="checkbox" name="mo_server_enable_oidc">
				<span id="mo_server_oidc_toggle" class="mo_slider mo_round with_on_text">'; if($enable_oidc === 'on') { echo "ON"; } echo'</span>
			</label>&emsp;<strong>Enable OpenID Connect Support</strong>

			<br><br>
        </div>
        <input type="hidden" name="option" value="mo_oauth_server_enable_oidc" />
		<input type="submit" name="submit" value="Save Settings" class="button button-primary button-large" />
	</form>
</div>';

echo '
<div id="advancedsettings" class="mo_table_layout">
    <form disabled name="f" method="post" action="" style="padding: 10px;">
        <div id="toggle2" class="panel_toggle">
            <h3>Advanced Settings</h3>
        </div>
        <div id="advanced_panel">';
            echo '
			<table style="width:100%">
				<tr>
					<td><h4>Select Grant Type</h4></td>
					<td align="right" style="margin-left:0">[<a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/oauth-api-document"><b>Click here</b></a> to know how this is useful]</td></tr>
			</table>
            <input onclick="showToast(this, true, \'grant_premium\')" type="checkbox" checked/>&nbsp;Authorization Code&nbsp;&nbsp;
            <input onclick="showToast(this, false, \'grant_premium\')" type="checkbox"/> Password&nbsp;&nbsp;
            <input onclick="showToast(this, false, \'grant_premium\')" type="checkbox"/> Client Credentials&nbsp;&nbsp;
            <input onclick="showToast(this, true, \'grant_premium\')" type="checkbox" checked/> Implicit Grant&nbsp;&nbsp;
            <input onclick="showToast(this, false, \'grant_premium\')" type="checkbox"/> Refresh Token Grant&nbsp;&nbsp;
            <br><div id="grant_premium" class="mo_premium_text">This is a premium feature. Check our licensing page for more info.</div><br><hr><br>
            <label class="mo_switch">
                <input disabled checked type="checkbox">
                <span class="mo_slider mo_round with_on_text">ON</span>
            </label>&emsp;<strong>Enable JWT Support</strong>&emsp;&emsp;<small>(Enabled only for OpenID Connect)</small><br><br>[<a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/jwt-support"><b>Click here</b></a> to know how this is useful]<br/><br/>
            <b>Note : </b>Enable only if JWT is supported by your OAuth/OpenID client.&emsp;<small><font color="red">[PREMIUM]</font></small><br>
            <br><hr>
            <h4>Signing Algorithms</h4>
            <table>
            <tr><td><input type="radio" checked disabled/>&nbsp;HS256&nbsp;&nbsp;
            <input type="radio" disabled/> HS384&nbsp;&nbsp;
            <input type="radio" disabled/> HS512&nbsp;&nbsp;
            <input type="radio" disabled/> RS256&nbsp;&nbsp;
            <input type="radio" disabled/> RS384&nbsp;&nbsp;
            <input type="radio" disabled/> RS512</td>
            <td>&emsp;&emsp;</td>
            <td>&emsp;&emsp;</td>
            </table>
            <br>Determines Algorithm used for signing JWT.&emsp;<small><font color="red">[PREMIUM]</font></small><br>
            <br><hr>
            <h4>Signing Certificate:</h4>
            <input disabled type="button" class="button button-primary button-large" value="Download Signing Certificate">
            <br/><br/>In case of RSA, you will need to provide the signing certificate (public key) to your client.&emsp;<small><font color="red">[PREMIUM]</font></small><br><br><br>
            <input disabled type="submit" name="submit" value="Save Settings" class="button button-primary button-large" />
        </div>
    </form>
</div>
';
echo '
	<script>
		function showToast(element, checked, id) {
			element.checked = checked;
			var x = document.getElementById(id);
			x.classList.add("show");
			setTimeout(function(){ x.classList.remove("show") }, 6000);
        }

        function turnOff(element, id) {
            var sp = document.getElementById(id);
            if(element.checked != true) {
                sp.innerHTML = "";
            } else if(element.checked == true) {
                sp.innerHTML = "ON";
            }
        }
	</script>
	';
}

function rocketchat_oauth_server_general_settings() {
    $master_switch = (bool) get_option('mo_oauth_server_master_switch') ? get_option('mo_oauth_server_master_switch') : 'on';
    $enforce_state = (bool) get_option('mo_oauth_server_enforce_state') ? get_option('mo_oauth_server_enforce_state') : 'on';
    $token_length = (bool) get_option('mo_oauth_server_token_length') ? (int) get_option('mo_oauth_server_token_length') : (int) 64;
	$expiry_time = (bool) get_option('mo_oauth_expiry_time') ? (int) get_option('mo_oauth_expiry_time') : 600;
	$refresh_expiry_time = get_option('mo_oauth_refresh_expiry_time')?get_option('mo_oauth_refresh_expiry_time'):86400;

echo '
<div id="generalsettings" class="mo_table_layout">
<form name="f" method="post" action="">
    <input type="hidden" name="option" value="mo_oauth_general_settings" />
    <div>
        <div id="toggle1" class="panel_toggle">
			<table style="width:100%">
				<tr>
					<td><h3>General Settings</h3></td>
					<td align="right" style="margin-left:0">[<a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/token-settings"><b>Click here</b></a> to know how this is useful]</td></tr>
			</table>
        </div>
        <div id="panel1">
            <table class="mo_settings_table">
                <tr>
                    <td><b><font color="#FF0000">*</font>Access Token Expiry Time :<br> ( In seconds )<span style="color:#FF0000">  [PREMIUM]</span></b></td>
                    <td><input class="mo_table_textbox" type="text" name="expiry_time"
                        required  value="'.$expiry_time.'" disabled/>
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td><b><font color="#FF0000">*</font>Refresh Token Expiry Time :<br> ( In seconds )<span style="color:#FF0000">  [PREMIUM]</span></b></td>
                    <td><input class="mo_table_textbox" type="text" name="refresh_expiry_time"
                        required  value="'.$refresh_expiry_time.'" disabled/>
                    </td>
                </tr>
                <tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td><b>Token Length :</b></td>
                    <td><input class="mo_table_textbox" type="number" min="64" max="255" name="mo_server_token_length"
                        required value="'; echo $token_length; echo '" />
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="Save Settings"
                        class="button button-primary button-large" /></td>
                </tr>
            </table>
        </div>
    </div>
</form>
</div>';
echo '<div id="enforce_state" class="mo_table_layout">';
	echo '
	<form name="f" method="post" action="" style="padding: 0px 5px;">
		<div id="toggle3" class="panel_toggle">
			<table style="width:100%">
				<tr>
					<td><h3>State Parameter</h3></td>
					<td align="right" style="margin-left:0">[<a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/enforce-state-parameters"><b>Click here</b></a> to know how this is useful]</td></tr>
			</table>
		</div>
		<div id="mo_server_enforce_state">
			Enable or Disable the State Parameter Validation.<br><br>
			<label class="mo_switch">
                <input checked onclick="showToast(this, true, \'mo_prompt_premium_text_state\')"  type="checkbox">
				<span id="mo_server_enforcestate_indicator" class="mo_slider mo_round with_on_text">ON</span>
			</label>&emsp;<strong>Enforce State Parameter</strong>
			<div id="mo_prompt_premium_text_state" class="mo_premium_text">This is a premium feature. Check our licensing page for more info.</div>

			<p>
			When enabled, the authorization request will fail if state parameter is not provided or is incorrect.</p>
        </div>
        <input type="hidden" name="option" value="mo_oauth_server_enforce_state" />
		<input disabled type="submit" name="submit" value="Save Settings" class="button button-primary button-large" />
	</form>
</div>';
	echo '<div id="redirect_validation" class="mo_table_layout">';
	echo '
	<form name="f" method="post" action="" style="margin: 0px 5px;">
		<div id="toggle2" class="panel_toggle">
			<table style="width:100%">
				<tr>
					<td><h3>Redirect/Callback URI Validation</h3></td>
					<td align="right" style="margin-left:0">[<a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/redirect-uri-validation"><b>Click here</b></a> to know how this is useful]</td></tr>
			</table>
		</div>
		<div id="callback_validation">
			<strong>Note :</strong> Use in case of Dynamic or Conditional Callback/Redirect URIs.<br><br>
			<label class="mo_switch">
				<input disabled type="checkbox">
				<span onclick="showToast(this, true, \'mo_premium_text\')" class="mo_slider mo_round with_on_text"></span>
			</label>&emsp;<strong>Validate Redirect/Callback URIs</strong><div id="mo_premium_text" class="mo_premium_text">This is a premium feature. Check our licensing page for more info.</div>

			<strong>How to use this feature?</strong><br><br>
			By default, server is configured with default redirect URL. <br><br>
			Disable this feature, in case if your client wants to redirect to a different page for certain conditions, such as, pre-registered users and guest users.

		</div>
		<br><br>
		<input disabled type="submit" name="submit" value="Save Settings" class="button button-primary button-large" />
	</form>
</div>';
	echo '
	<script>
		function showToast(element, checked, id) {
			element.checked = checked;
			var x = document.getElementById(id);
			x.classList.add("show");
			setTimeout(function(){ x.classList.remove("show") }, 6000);
        }

        function turnOff(element, id) {
            var sp = document.getElementById(id);
            if(element.checked != true) {
                if(id === "mo_server_mswitch_indicator") {
                    setTimeout(function(){ document.getElementById("mswitch_warning").classList.add("show") }, 200);
                }
                sp.innerHTML = "";
            } else if(element.checked == true) {
                if(id === "mo_server_mswitch_indicator") {
                    setTimeout(function(){ document.getElementById("mswitch_warning").classList.remove("show") }, 700);
                }
                sp.innerHTML = "ON";
            }
        }

	</script>
	';
}

function rocketchat_generate_dropdown_from_array($array) {
	echo '<select>';
	foreach($array as $item => $selected) {
		echo '<option value="'.$item.'">'.$item.'</option>';
	}
	echo '</select>';
}

function rocketchat_oauth_server_attribute_mapping() {
	echo '
	<style>
		.tableborder {border-collapse: collapse;width: 100%;border-color:#eee;}
		.tableborder th, .tableborder td {text-align: left;padding: 8px;border-color:#eee;}
		.tableborder tr:nth-child(even){background-color: #f2f2f2}
	</style>
	';
	$server_response = array();
	$attrs = array(
		'username' => 'user_login',
		'email' => 'email',
		'first_name' => 'first_name',
		'last_name' => 'last_name',
		'display_name' => 'display_name',
		'nickname' => 'nickname',
	);

	$user_info = get_userdata(wp_get_current_user()->ID);
	$attr_value = array();
	foreach($user_info->data as $key => $value) {
		if($key !== 'user_pass' && $key !== 'user_activation_key' && $key !== 'user_status')
		array_push($attr_value, $key);
	}
	array_push($attr_value, 'user_firstname');
	echo '
		<div id="basicattributemapping" class="mo_table_layout">
			<h3>Advanced Attribute Mapping &emsp;<small class="mo_oauth_server_premium_feature"> [STANDARD FEATURE]</small></h3>
			You can customize and send below attriutes in response to your OAuth Client\'s Get User Information request.<br><br>
			<table class="mo_settings_table tableborder">
				<tr><td><b>Attribute Name</b></td><td><b>Attribute Value</b></td></tr>';
				foreach($attrs as $attr_name => $attr_value) {
					echo '<tr><td><input disabled type="text" placeholder="'.$attr_name.'" /></td>
					<td><select style="width: 150px;" disabled><option selected value="'.$attr_name.'">'.$attr_value.'</option></td></tr>';
				}
			echo '</table>
			<br><br><input disabled type="submit" name="submit" value="Save settings"
			class="button button-primary button-large" />
		</div>
	';
	echo '
	<div id="attributemapping" class="mo_table_layout">
	<form name="form-common" method="post" action="admin.php?page=mo_oauth_server_settings&tab=settings">
	<table class="mo_settings_table">';

		echo '
		  <tr><td colspan="2">
		<h3>Map Custom Attributes &emsp;<small class="mo_oauth_server_premium_feature"> [PREMIUM FEATURE]</small></h3>Map extra User attributes which you wish to be included in the OAuth response. <br/>
		<b>Note : </b>Enter the name you want to send as attribute name under Attribute Name text field and meta field name under the Attribute Value text field. <br>

		</td><td><input disabled type="button" value="+" class="button button-primary"  /></td>
						<td><input disabled type="button" value="-" class="button button-primary" /></td></tr><br/>
						<tr><td>
		<b><u>Example</u> : </b></td></tr><tr>
		<tr><td><b>Attribute Name</b></td><td><b>Attribute Value</b></td></tr>
		<td><input disabled value="Given Name" /></td>
		<td><input disabled value="first_name" /></td></tr>
						<tr><td>&nbsp;</td></tr>';

		echo '<tr id="save_config_element">
			<td><input disabled type="submit" name="submit" value="Save settings"
			class="button button-primary button-large" /></td>
			<td>&nbsp;</td>
		</tr>
		</table>
	</form>

		</div>';
}

function mo_oauth_server_app_licensing() {
	$registered = mo_oauth_server_is_customer_registered();
	$login_url = get_option( 'host_name' ) . '/moas/login';
	$username = get_option( 'mo_oauth_admin_email' );
	$payment_url = get_option( 'host_name' ) . '/moas/initializepayment';

    echo'<div class="mo_idp_divided_layout mo-idp-full">';
    if(!$registered) {
		echo '<div style="display:block;margin-top:10px;color:red;width: 99%;
                            background-color:rgba(251, 232, 0, 0.15);
                            padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
		        You have to <a href="admin.php?page=mo_oauth_server_settings&tab=login">
		        Register or Login with miniOrange</a> in order to be able to Upgrade.
		      </div>';
	}
	echo'   <form style="display:none;" id="mo_idp_request_quote_form" action="admin.php?page=mo_oauth_server_settings&tab=requestforquote" method="post">
                <input type="text" name="plan_name" id="plan-name" value="" />
                <input type="text" name="plan_users" id="plan-users" value="" />
            </form>
            
            <form style="display:none;" id="mocf_loginform" action="'.$login_url.'" target="_blank" method="post">
				<input type="email" name="username" value="'.$username.'" />
				<input type="text" name="redirectUrl" value="'.$payment_url.'" />
				<input type="text" name="requestOrigin" id="requestOrigin"  />
			</form>
            
            <div class="mo_idp_pricing_layout mo-idp-center">
                <h2>LICENSING PLANS<span style="float:right; font-size:13px">Need guidance with pricing? Please drop us an email at <a href="mailto:info@xecurify.com">info@xecurify.com</a></span>
                </h2>
                <hr>  
                <br>
                    <table class="mo_idp_license_plan mo_idp_license_table">
                        <tr>
                            <td class="license_plan_points" style="border-radius:12px 12px 0 0; width: 13%;"><b>Licensing Plan Name</b></td>
                            <td colspan=2 class="license_plan_title" style="width: 25%;"><span class="license_plan_name">LITE PLAN</span><br><p style="font-size:20px;">(Users hosted in miniOrange Cloud)</p></td>
                            <td class="license_plan_title" style="width: 25%;"><span class="license_plan_name">PREMIUM PLAN</span><br><p style="font-size:20px;">(Users stored in your own WordPress Database)</p></td>
                            <td class="license_plan_title"><span class="license_plan_name">ALL-INCLUSIVE PLAN</span><br><p style="font-size:20px;">(Users hosted in miniOrange or Enterprise Directory like Azure AD, Active Directory, LDAP, Office365, Google Apps or any 3rd party providers using SAML, OAuth, Database, APIs etc)</p></td>
                        </tr>
                        <tr style="background-color:#95d5ba;">
                            <td class="license_plan_points" rowspan=2><b>User Slabs / Pricing</b></td>
                            <td><b>Monthly Pricing</b></td>
                            <td><b>Yearly Pricing</b></td>
                            <td style="padding: 20px; line-height: 1.8;">
                                <b>Yearly Pricing <span class="dashicons dashicons-info mo-info-icon"><span class="mo-info-text">Number of users indicate any user that authenticated during a given <b><u>month</u></b></span></span>
                                <br><span style="color: red;">(50% from 2nd year onwards)</span></b>
                            </td>
                            <td><b>Monthly / Yearly Pricing</b></td>
                        </tr>
                        <tr>
                            <td class="mo_license_upgrade_button"><a onclick="mo2f_upgradeform(\'mo_idp_lite_monthly_plan\')" style="display: block; width: 100%; text-decoration: none; color:white;"><b style="font-weight:700; letter-spacing:2px;">UPGRADE NOW</b></a></td>
                            <td class="mo_license_upgrade_button"><a onclick="mo2f_upgradeform(\'mo_idp_lite_yearly_plan\')" style="display: block; width: 100%; text-decoration: none; color:white;"><b style="font-weight:700; letter-spacing:2px;">UPGRADE NOW</b></a></td>
                            <td class="mo_license_upgrade_button"><a onclick="mo2f_upgradeform(\'wp_oauth_server_enterprise_plan\')" style="display: block; width: 100%; text-decoration: none; color:white;"><b style="font-weight:700; letter-spacing:2px;">UPGRADE NOW</b></a></td>
                            <td class="mo_license_upgrade_button"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="display: block; width: 100%; text-decoration: none; color:white;"><b style="font-weight:700; letter-spacing:2px;">REQUEST A QUOTE</b></a></td>
                        </tr>
                        <tr>
                            <td class="license_plan_points">
                                <select id="mo_idp_users_dd" style="text-align: center; font-size:20px; color: #0071a1; border-color: #0071a1;">
                                    <option value="100" selected>1 - 100</option>
                                    <option value="200">101 - 200</option>
                                    <option value="300">201 - 300</option>
                                    <option value="400">301 - 400</option>
                                    <option value="500">401 - 500</option>
                                    <option value="1000">501 - 1000</option>
                                    <option value="2000">1001 - 2000</option>
                                    <option value="3000">2001 - 3000</option>
                                    <option value="4000">3001 - 4000</option>
                                    <option value="5000">4001 - 5000</option>
                                    <option value="5000+">5000+</option>
                                    <option value="UL">Unlimited</option>
                            </td>
                            <td class="mo_idp_price_row mo_idp_price_slab_100" style="display: table-cell;"><b>$</b>15<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_100" style="display: table-cell;"><b>$</b>165<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_100" style="display: table-cell;"><b>$</b>450<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$225</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_100" style="display: table-cell;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>
                        
                            <td class="mo_idp_price_row mo_idp_price_slab_200" style="display: none;"><b>$</b>16<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_200" style="display: none;"><b>$</b>176<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_200" style="display: none;"><b>$</b>550<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$275</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_200" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_300" style="display: none;"><b>$</b>17<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_300" style="display: none;"><b>$</b>187<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_300" style="display: none;"><b>$</b>650<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$325</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_300" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_400" style="display: none;"><b>$</b>18<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_400" style="display: none;"><b>$</b>198<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_400" style="display: none;"><b>$</b>750<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$375</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_400" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_500" style="display: none;"><b>$</b>19<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_500" style="display: none;"><b>$</b>209<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_500" style="display: none;"><b>$</b>850<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$425</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_500" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_1000" style="display: none;"><b>$</b>22<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_1000" style="display: none;"><b>$</b>242<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_1000" style="display: none;"><b>$</b>1250<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$625</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_1000" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_2000" style="display: none;"><b>$</b>44<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_2000" style="display: none;"><b>$</b>484<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_2000" style="display: none;"><b>$</b>1600<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$800</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_2000" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_3000" style="display: none;"><b>$</b>66<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_3000" style="display: none;"><b>$</b>726<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_3000" style="display: none;"><b>$</b>1900<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$950</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_3000" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_4000" style="display: none;"><b>$</b>88<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_4000" style="display: none;"><b>$</b>968<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_4000" style="display: none;"><b>$</b>2150<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$1075</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_4000" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_5000" style="display: none;"><b>$</b>110<span style="font-size: 15px;"> / month</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_5000" style="display: none;"><b>$</b>1155<span style="font-size: 15px;"> / year</span></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_5000" style="display: none;"><b>$</b>2400<span style="font-size: 15px;"> for the 1<sup>st</sup> year<br><b style="font-size: 18px;">$1200</b> from 2<sup>nd</sup> year onwards</span></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_5000" style="display: none;"><span style="">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_5000p" style="display: none;"><u style="color:orange; font-size: 21px;"><a id="lite_monthly_5K" onclick="gatherplaninfo(\'lite_monthly\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_5000p" style="display: none;"><u style="color:orange; font-size: 21px;"><a id="lite_yearly_5K" onclick="gatherplaninfo(\'lite_yearly\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>
                            <td class="mo_idp_price_row mo_idp_price_slab_5000p" style="display: none;"><u style="color:orange; font-size: 21px;"><a id="wp_yearly_5K" onclick="gatherplaninfo(\'wp_yearly\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_5000p" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>

                            <td class="mo_idp_price_row mo_idp_price_slab_ul" style="display: none;">N/A</td>
                            <td class="mo_idp_price_row mo_idp_price_slab_ul" style="display: none;">N/A</td>
                            <td class="mo_idp_price_row mo_idp_price_slab_ul" style="display: none;"><u style="color:orange; font-size: 21px;"><a id="wp_yearly_UL" onclick="gatherplaninfo(\'wp_yearly\',\'UL\')" style="color:orange;"><b>Request a Quote</b></a></u></td>
                            <td class="mo_idp_all_inc_cell mo_idp_price_slab_ul" style="display: none;">Starts from <b>$</b>0.5/user/month<br><u style="color:orange; font-size: 21px;"><a onclick="gatherplaninfo(\'all_inclusive\',\'5K\')" style="color:orange;"><b>Request a Quote</b></a></u></td>
                        </tr>
 
                        <tr>
                            <td class="license_plan_points"><b>User Storage Location</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Keep Users in miniOrange Database</td>
                            <td class="license_plan_miniorange">Keep Users in WordPress Database</td>
                            <td class="license_plan_miniorange">Keep Users in miniOrange Database or Enterprise Directory like Azure AD, Active Directory, LDAP, Office 365, Google Apps  or any 3rd party providers using SAML, OAuth, Database, APIs etc.</td>
                        </tr>
                        <tr>
                            <td class="license_plan_points"><b>Password Management</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Passwords will be hosted in miniOrange</td>
                            <td class="license_plan_miniorange">Passwords will be stored in your WordPress Database</td>
                            <td class="license_plan_miniorange">Passwords can be managed by miniOrange or by the 3rd party Identity Provider</td>
                        </tr>
                        <tr>
                            <td class="license_plan_points"><b>SSO Support</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Cross-Protocol SSO Support<br>SAML<br>OAuth<br>OpenID Connect<br>JWT</td>
                            <td class="license_plan_miniorange">Single-Protocol SSO Support<br>&nbsp;<br>OAuth<br>&nbsp;<br>&nbsp;</td>
                            <td class="license_plan_miniorange">Cross-Protocol SSO Support<br>SAML<br>OAuth<br>OpenID Connect<br>JWT</td>
                        </tr>
                        <tr>
                            <td class="license_plan_points"><b>User Registration</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Sign-up via miniOrange Login Page</td>
                            <td class="license_plan_miniorange">Use your own existing WordPress Sign-up form</td>
                            <td class="license_plan_miniorange">Sign-up via miniOrange Login Page</td>
                        </tr> 
                        <tr>
                            <td class="license_plan_points"><b>Login Page</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Embed miniOrange Login Widget on your WordPress Site<br>OR<br>Use Login Page hosted on miniOrange</td>
                            <td class="license_plan_miniorange">Use your own existing WordPress Login Page</td>
                            <td class="license_plan_miniorange">Fully customizable miniOrange Login Page</td>
                        </tr>
                        <tr>
                            <td class="license_plan_points"><b>Custom Domains</b></td>
                            <td colspan=2 class="license_plan_wp_premium">miniOrange sub-domain will be provided</td>
                            <td class="license_plan_miniorange">Use your own WordPress domain</td>
                            <td class="license_plan_miniorange">Fully Custom Domain is provided</td>
                        </tr>
                        <tr>
                            <td class="license_plan_points"><b>Social Providers</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Included<br>(Facebook, Twitter, Google+, etc)</td>
                            <td class="license_plan_miniorange"><a href="https://plugins.miniorange.com/social-login-social-sharing#pricing" target="_blank" style="color:orange;">Click here</a> to purchase Social Login Plugin seperately</td>
                            <td class="license_plan_miniorange">Included<br>(Facebook, Twitter, Google+, etc)</td>
                        </tr>                                            
                        <tr>
                            <td class="license_plan_points"><b>Multi-Factor Authentication</b></td>
                            <td colspan=2 class="license_plan_wp_premium">Not Included</td>
                            <td class="license_plan_miniorange"><a href="https://plugins.miniorange.com/2-factor-authentication-for-wordpress#pricing" target="_blank" style="color:orange;">Click here</a> to purchase Multi-Factor Plugin seperately</td>
                            <td class="license_plan_miniorange">Included</td>
                        </tr>                          
                        <tr>
                            <td class="license_plan_points" style="border-radius:0 0 12px 12px;"><b>User Provisioning</b></td>
                            <td colspan=2 class="license_plan_wp_premium" style="border-radius:0 0 12px 12px;">Not Included</td>
                            <td class="license_plan_miniorange" style="border-radius:0 0 12px 12px;">Not Included</td>
                            <td class="license_plan_miniorange" style="border-radius:0 0 12px 12px;">Included</td>
                        </tr>
                    
                    </table>
<!--
                    <table class="mo_idp_pricing_table" style="margin:auto;">
                        <tr>
                            <td><h2>Choose your Plan : </h2></td>
                            <td>
                                <select style="width:85%">
                                    <option>WordPress Premium Plan</option>
                                    <option>miniOrange Lite Plan</option>
                                    <option>miniOrange All Inclusive Plan</option>
                                </select>
                            </td>
                            <td>
                                <select style="width:75%">
                                    <option>Pay Monthly</option>
                                    <option>Pay Yearly</option>
                                </select>
                            </td>
                            <td>
                                <a href="https://www.google.com" target="_blank">Proceed to Payment Page</a> <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </td>
                        </tr>
                    </table>
-->
            </div>
            <div id="disclamer" class="mo_idp_pricing_layout mo-idp-center">
                <h3>* Steps to Upgrade to Premium Plugin -</h3>
                <p>
                    1. You will be redirected to miniOrange Login Console. 
                    Enter your password with which you created an account with us. 
                    After that you will be redirected to payment page.
                </p>
                <p>
                    2. Enter you card details and complete the payment. 
                    On successful payment completion, you will see the link to download the premium plugin.
                </p>
                <p>
                    3. Once you download the premium plugin, just unzip it and replace the folder with existing plugin. <br>
                    <b>Note: Do not first delete and upload again from wordpress admin panel as your already saved settings will get lost.</b></p>
                    <p>4. From this point on, do not update the plugin from the Wordpress store.</p>
                    <h3>** End to End Integration - </h3>
                    <p> 
                        We will setup a Conference Call / Gotomeeting and do end to end configuration for you. 
                        We provide services to do the configuration on your behalf. 
                    </p>
                    If you have any doubts regarding the licensing plans, you can mail us at 
                    <a href="mailto:info@xecurify.com"><i>info@xecurify.com</i></a> 
                    or submit a query using the <b>support form</b>.
                </p>
            </div>
            <div class="mo_idp_pricing_layout mo-idp-center">
                <p>At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you\'ve attempted to resolve any issues with our support team, which couldn\'t get resolved, please email us at <a href="mailto:info@xecurify.com" target="_blank">info@xecurify.com</a> for any queries regarding the return policy.</p>
            </div>
        </div>';
}


function rocketchat_oauth_server_apps_config() {
	?>
	<style>
		.tableborder {border-collapse: collapse;width: 100%;border-color:#eee;}
		.tableborder th, .tableborder td {text-align: left;padding: 8px;border-color:#eee;}
		.tableborder tr:nth-child(even){background-color: #f2f2f2}
	</style><?php rocketchat_oauth_server_app_howtosetup();
}
	
function rocketchat_oauth_server_miniorange_support(){
?>
	<div id="mo_support_layout" class="mo_support_layout">
		<div>
			<h3>Contact Us</h3>
			<form method="post" action="">
				<input type="hidden" name="option" value="mo_oauth_contact_us_query_option" />
				<table class="mo_settings_table">
					<tr>
						<td><input type="email" class="mo_table_textbox" required style="width:90%" name="mo_oauth_contact_us_email" placeholder="Enter email here" value="<?php echo get_option("mo_oauth_admin_email"); ?>"></td>
					</tr>
					<tr>
						<td><input type="tel" id="contact_us_phone" style="width:90%" pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" placeholder="Enter phone here" class="mo_table_textbox" name="mo_oauth_contact_us_phone" value="<?php echo get_option('mo_oauth_admin_phone');?>"></td>
					</tr>
					<tr>
						<td><textarea class="mo_table_textbox" style="width:90%" onkeypress="mo_oauth_valid_query(this)" placeholder="Enter your query here" onkeyup="mo_oauth_valid_query(this)" onblur="mo_oauth_valid_query(this)" required name="mo_oauth_contact_us_query" rows="4" style="resize: vertical;"></textarea></td>
					</tr>
				</table>
				<div style="text-align:center;">
					<input type="submit" name="submit" style="margin:15px; width:100px;" class="button button-primary button-large" />
				</div>
			</form>
		</div>
    </div>

	<script>
		jQuery("#contact_us_phone").intlTelInput();
		function mo_oauth_valid_query(f) {
			!(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
					/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
		}
	</script>
    <br/>
		<div class="mo_support_layout">
			<div>
				<p><b>Looking for user provisioning? </b><a href="https://www.miniorange.com/wordpress-miniorange-scim-user-provisioner-with-onelogin">Click here </a> to know more about miniOrange SCIM User Provisioner Add-On.<br></p>
			</div>
		</div>
		<br/>
    
<?php
}

function rocketchat_oauth_server_show_otp_verification(){
	?>
		<!-- Enter otp -->
		<div class="mo_table_layout">
		<form name="f" method="post" id="otp_form" action="">
		<?php wp_nonce_field('mo_oauth_validate_otp_form','mo_oauth_validate_otp_form_field'); ?>
			<input type="hidden" name="option" value="mo_oauth_validate_otp" />
					<div id="panel5">
						<table class="mo_settings_table">
							<h3>Verify Your Email</h3>
							<tr>
								<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
								<td><input class="mo_table_textbox" autofocus="true" type="text" name="mo_oauth_otp_token" required placeholder="Enter OTP" style="width:61%;" pattern="[0-9]{6,8}"/>
								 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('mo_oauth_resend_otp_form').submit();">Resend OTP</a></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><br /><input type="submit" name="submit" value="Validate OTP" class="button button-primary button-large" />
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="button" name="back-button" id="mo_oauth_back_button" onclick="document.getElementById('mo_oauth_change_email_form').submit();" value="Back" class="button button-primary button-large" />
								</td>
							</tr>
						</table>
					</div>
		</form>
		<br>
		<form id="mo_oauth_register_with_phone_form" method="post" action="">
					<input type="hidden" name="option" value="mo_oauth_register_with_phone_option" />
					 If you can't see the email from miniOrange in your mails, please check your <b>SPAM</b> folder. If you don't see an email even in the SPAM folder, verify your identity with our alternate method.
					 <br><br>
						<b>Enter your valid phone number here and verify your identity using one time passcode sent to your phone.</b><br><br>
						<input class="mo_oauth_table_textbox" type="tel" id="phone_contact" style="width:40%;"
								pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" class="mo_oauth_table_textbox" name="phone"
								title="Phone with country code eg. +1xxxxxxxxxx" required
								placeholder="Phone with country code eg. +1xxxxxxxxxx"
								value="<?php echo get_option('mo_oauth_admin_phone');?>" />
						<br /><br /><input type="submit" value="Send OTP" class="button button-primary button-large" />
		</form>
		<form name="f" id="mo_oauth_resend_otp_form" method="post" action="">
			<input type="hidden" name="option" value="mo_oauth_resend_otp"/>
		</form>
		<form id="mo_oauth_change_email_form" method="post" action="">
			<input type="hidden" name="option" value="mo_oauth_change_email" />
		</form>
		<form>
			<h3>Unable To Register?</h3>
			<p><b>Getting Invalid one time passcode? Please, <a href="https://www.miniorange.com/businessfreetrial" target="_blank">click here</a> to Sign Up.</b></p><p>We will appreciate if you write us on <b>info@xecurify.com</b> about this issue. </p>
		</form>
			</div>
<?php
}

function rocketchat_oauth_server_show_otp_verification_phone() {
	?>
	<div class="mo_table_layout">
	<form name="f" method="post" id="otp_form" action="">
		<input type="hidden" name="option" value="mo_oauth_validate_otp" />
				<div id="panel5">
					<table class="mo_settings_table">
						<h3>Verify Your Phone</h3>
						<tr>
							<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
							<td><input class="mo_table_textbox" autofocus="true" type="text" name="mo_oauth_otp_token" required placeholder="Enter OTP" style="width:61%;" pattern="[0-9]{6,8}"/>
							 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('mo_oauth_resend_otp_form').submit();">Resend OTP</a></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><br /><input type="submit" name="submit" value="Validate OTP" class="button button-primary button-large" />
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="button" name="back-button" id="mo_oauth_back_button" onclick="document.getElementById('mo_oauth_change_phone_form').submit();" value="Back" class="button button-primary button-large" />
							</td>
						</tr>
					</table>
				</div>
	</form>
	<form name="f" id="mo_oauth_resend_otp_form" method="post" action="">
		<input type="hidden" name="option" value="mo_oauth_resend_otp"/>
	</form>
	<form id="mo_oauth_change_phone_form" method="post" action="">
		<input type="hidden" name="option" value="mo_oauth_change_phone" />
	</form>
</div>
	<?php
}
?>
