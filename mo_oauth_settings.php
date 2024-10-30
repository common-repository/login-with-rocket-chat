<?php
/**
* Plugin Name: Login with Rocket Chat
* Plugin URI: http://miniorange.com
* Description: Setup your site as Identity Server to allow Login with WordPress or WordPress Login to other client application /site using OAuth / OpenID Connect protocols.
* Version: 2.2.4
* Author: miniOrange
* Author URI: https://www.miniorange.com
* License: MIT/Expat
* License URI: https://docs.miniorange.com/mit-license
*/

include_once dirname( __FILE__ ).'/class-mo-oauth-widget.php';
require('class-customer.php');
require('mo_oauth_settings_page.php');
require('mo_oauth_db_handler.php');
require('feedback_form.php');
require( 'endpoints/registry.php' );

class mo_oauth_server_rocketchat {

	function __construct() {

		add_action( 'admin_menu', array( $this, 'miniorange_menu' ) );
		add_action( 'admin_init',  array( $this, 'miniorange_oauth_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'mo_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_oauth_deactivate'));
		register_activation_hook(__FILE__, array( $this, 'mo_oauth_activate'));
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'tutorial' ) );
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
		add_shortcode('mo_oauth_login', array( $this,'mo_oauth_shortcode_login'));
		add_action( 'admin_footer', array( $this, 'mo_oauth_server_feedback_request' ) );
	}

	function tutorial($page) {
		$file = plugin_dir_path( __FILE__ ) . 'pointers.php';
		// Arguments: pointers php file, version (dots will be replaced), prefix
		$manager = new MORocketchatServerPointersManager( $file, '2.5.1', 'custom_admin_pointers' );
		$manager->parse();
		$pointers = $manager->filter( $page );
		if ( empty( $pointers ) ) { // nothing to do if no pointers pass the filter
			return;
		}
		wp_enqueue_style( 'wp-pointer' );
		$js_url = plugins_url( 'cards.js', __FILE__ );
		wp_enqueue_script( 'custom_admin_pointers', $js_url, array('wp-pointer'), NULL, TRUE );
		// data to pass to javascript
		$data = array(
			'next_label' => __( 'Next' ),
			'close_label' => __('Close'),
			'pointers' => $pointers
		);

		wp_localize_script( 'custom_admin_pointers', 'MyAdminPointers', $data );

	}

	function mo_oauth_success_message() {
		$class = "error";
		$message = get_option('message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function mo_oauth_server_feedback_request() {
		rocketchat_oauth_server_display_feedback_form();
	}

	function mo_oauth_error_message() {
		$class = "updated";
		$message = get_option('message');
		echo "<div class='" . $class . "'><p>" . $message . "</p></div>";
	}

	public function mo_oauth_activate() {
		$MoRocketchatServerDb = new MoRocketchatServerDb();
		$MoRocketchatServerDb->mo_plugin_activate();
		$client_id=moosGenerateRandomString(30);
		$client_secret=moosGenerateRandomString(30);
		$client_name= "Rocket Chat";
		$redirect_url="";
		$active_oauth_server_id=get_current_blog_id();
		$MoRocketchatServerDb->add_client($client_name,$client_secret,$redirect_url,$active_oauth_server_id);
	}

	public function mo_oauth_deactivate() {
		//delete all stored key-value pairs
	
		delete_option('host_name');
		delete_option('new_registration');
		delete_option('mo_oauth_admin_phone');
		delete_option('verify_customer');
		delete_option('mo_oauth_admin_customer_key');
		delete_option('mo_oauth_admin_api_key');
		delete_option('mo_oauth_new_customer');
		delete_option('customer_token');
		delete_option('message');
		delete_option('mo_oauth_registration_status');
		delete_option('mo_oauth_show_mo_server_message');
		
	}

	private $settings = array(
		'mo_oauth_facebook_client_secret'	=> '',
		'mo_oauth_facebook_client_id' 		=> '',
		'mo_oauth_facebook_enabled' 		=> 0
	);

	function miniorange_menu() {

		//Add miniOrange plugin to the menu
		$page = add_menu_page( 'MO OAuth Settings ' . __( 'Configure OAuth', 'mo_oauth_server_settings' ), 'Login with Rocket Chat', 'administrator', 'mo_oauth_server_settings', array( $this, 'mo_oauth_login_options' ) ,plugin_dir_url(__FILE__) . 'images/miniorange.png');


	}

	function  mo_oauth_login_options () {
		global $wpdb;
		update_option( 'host_name', 'https://login.xecurify.com' );
		$customerRegistered = mo_oauth_server_is_customer_registered();
		if( $customerRegistered ) {
			rocketchat_oauth_server_page();
		} else {
			rocketchat_oauth_server_page();
		}
	}

	function plugin_settings_style() {
		wp_enqueue_style( 'mo_oauth_admin_settings_style', plugins_url( 'style_settings.css', __FILE__ ) );
		wp_enqueue_style( 'mo_oauth_admin_settings_phone_style', plugins_url( 'phone.css', __FILE__ ) );
		if(isset($_REQUEST['tab']) && sanitize_text_field($_REQUEST['tab']) == 'licensing'){
            wp_enqueue_style( 'mo_oauth_bootstrap_css', plugins_url( 'css/bootstrap/bootstrap.min.css', __FILE__ ) );
        }
	}

	function plugin_settings_script() {
		wp_enqueue_script( 'mo_oauth_admin_settings_script', plugins_url( 'settings.js', __FILE__ ) );
		wp_enqueue_script( 'mo_oauth_admin_settings_phone_script', plugins_url('phone.js', __FILE__ ) );
		if(isset($_REQUEST['tab']) && sanitize_text_field($_REQUEST['tab']) == 'licensing'){
            wp_enqueue_script( 'mo_oauth_modernizr_script', plugins_url( 'js/modernizr.js', __FILE__ ) );
            wp_enqueue_script( 'mo_oauth_popover_script', plugins_url( 'js/bootstrap/popper.min.js', __FILE__ ) );
            wp_enqueue_script( 'mo_oauth_bootstrap_script', plugins_url( 'js/bootstrap/bootstrap.min.js', __FILE__ ) );
        }
	}

	function mo_login_widget_text_domain() {
		load_plugin_textdomain( 'flw', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
		// to update the database on plugin update
		$this->plugin_update();
	}

	function plugin_update(){
		global $wpdb;
		// update the table moos_oauth_clients
		$table_name = $wpdb->base_prefix."moos_oauth_clients";
        $row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '" . DB_NAME ."' AND table_name = '". $table_name . "' AND column_name ='active_oauth_server_id'", ARRAY_A);
		if ( empty($row) ) {
			$sql = "ALTER TABLE ".$wpdb->base_prefix."moos_oauth_clients ADD active_oauth_server_id INT DEFAULT ".get_current_blog_id();
			$wpdb->query($sql);
		}
	}

	private function mo_oauth_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
	}

	private function mo_oauth_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
	}

	public function mo_oauth_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	function miniorange_oauth_save_settings(){

		if( ! session_id() ) {
			session_start(['read_and_close' => true,]);
		}

		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_mo_server_message" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mo_oauth_mo_server_form_field'] ) ), 'mo_oauth_mo_server_form' )) {
			update_option( 'mo_oauth_show_mo_server_message', 1 );

			return;
		}

		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "clear_pointers" && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['mo_oauth_goto_login_form_field'] ) ), 'mo_oauth_goto_login_form')) {
			update_user_meta(get_current_user_id(),'dismissed_wp_pointers','');
			return;
		}

		if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "change_miniorange") {
			delete_option('host_name');
			delete_option('mo_oauth_admin_email');
			delete_option('mo_oauth_admin_phone');
			delete_option('verify_customer');
			delete_option('mo_oauth_admin_customer_key');
			delete_option('mo_oauth_admin_api_key');
			delete_option('customer_token');
			delete_option('mo_oauth_new_customer');
			delete_option('message');
			delete_option('mo_eve_api_key');
			delete_option('new_registration');
			delete_option('mo_oauth_registration_status');
			delete_option('mo_oauth_show_mo_server_message');
			return;
		}

		if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_register_customer"  && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_register_customer_form_field'] ) ), 'mo_oauth_register_customer_form' )) {	//register the admin to miniOrange
			//validation and sanitization
			$email = '';
			$phone = '';
			$password = '';
			$confirmPassword = '';
			$fname = '';
			$lname = '';
			$company = '';
			if( $this->mo_oauth_check_empty_or_null( sanitize_email($_POST['email'] ) )|| $this->mo_oauth_check_empty_or_null( $_POST['password'] ) || $this->mo_oauth_check_empty_or_null( $_POST['confirmPassword'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter valid entries.');
				$this->mo_oauth_show_error_message();
				return;
			} else if( strlen( sanitize_text_field($_POST['password'] )) < 8 || strlen( sanitize_text_field($_POST['confirmPassword'] )) < 8){
				update_option( 'message', 'Choose a password with minimum length 8.');
				$this->mo_oauth_show_error_message();
				return;
			} else{

				$email = sanitize_email( $_POST['email'] );
				$password = stripslashes( sanitize_text_field($_POST['password']));
				$confirmPassword = stripslashes(sanitize_text_field( $_POST['confirmPassword']));
				$fname = stripslashes(sanitize_text_field($_POST['fname']));
				$lname = stripslashes(sanitize_text_field( $_POST['lname']));
				$company = stripslashes(sanitize_text_field($_POST['company']));
			}

			update_option( 'mo_oauth_admin_email', $email );
			update_option( 'mo_oauth_admin_fname', $fname );
			update_option( 'mo_oauth_admin_lname', $lname );
			update_option( 'mo_oauth_admin_company', $company );

			if( mo_oauth_server_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}

			if( strcmp( $password, $confirmPassword) == 0 ) {
				$email = get_option('mo_oauth_admin_email');
				update_option( 'password', $password );
				$customer = new Mo_Rocketchat_Server_Customer();
				$content = json_decode($customer->check_customer(), true);

				if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ) {
					$response = json_decode($customer->create_customer(), true);
					if(strcasecmp($response['status'], 'SUCCESS') == 0) {
						$content = $customer->get_customer_key();
						$customerKey = json_decode( $content, true );
							if( json_last_error() == JSON_ERROR_NONE ) {
								update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
								update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
								update_option( 'customer_token', $customerKey['token'] );
								update_option( 'mo_oauth_admin_phone', $customerKey['phone'] );
								delete_option( 'password' );
								update_option( 'message', 'Customer created & retrieved successfully');
								delete_option( 'verify_customer' );
								$this->mo_oauth_show_success_message();
							}
						wp_redirect( admin_url( '/admin.php?page=mo_oauth_server_settings&tab=login' ), 301 );
						exit;
					} else {
						update_option( 'message', 'Failed to create customer. Try again.');
					}
					$this->mo_oauth_show_success_message();
				} elseif(strcasecmp( $content['status'], 'SUCCESS') == 0 ) {
					update_option( 'message', 'Account already exist. Please Login.');
				} else {
					update_option( 'message', $content['status'] );
				}
				$this->mo_oauth_show_success_message();

			} else {
				update_option( 'message', 'Passwords do not match.');
				delete_option('verify_customer');
				$this->mo_oauth_show_error_message();
			}
		}

		if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_register" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_register_customer_form_field'] ) ), 'mo_oauth_register_customer_form' )){
			update_option('goto_registration', true);
			return;
		}
		else if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_validate_otp" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_validate_otp_form_field'] ) ), 'mo_oauth_validate_otp_form' )){
			if( mo_oauth_server_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$otp_token = '';
			if( $this->mo_oauth_check_empty_or_null( sanitize_text_field($_POST['mo_oauth_otp_token'] ) ) ){
				update_option( 'message', 'Please enter a value in OTP field.');
				update_option('mo_oauth_registration_status','MO_OTP_VALIDATION_FAILURE');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$otp_token = stripslashes( sanitize_text_field($_POST['mo_oauth_otp_token'] ));
			}

			$customer = new Mo_Rocketchat_Server_Customer();
			$content = json_decode($customer->validate_otp_token($_SESSION['mo_oauth_transactionId'], $otp_token ),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				$this->create_customer();
			}else{
				update_option( 'message','Invalid one time passcode. Please enter a valid OTP.');
				update_option('mo_oauth_registration_status','MO_OTP_VALIDATION_FAILURE');
				$this->mo_oauth_show_error_message();
			}
		}

		if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_goto_login" ) {
			delete_option( 'new_registration' );
			update_option( 'verify_customer', 'true' );
		}

		if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_verify_customer" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_verify_password_form_field'] ) ), 'mo_oauth_verify_password_form' ) ) {	//register the admin to miniOrange

			if( mo_oauth_server_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$email = '';
			$password = '';
			if( $this->mo_oauth_check_empty_or_null( sanitize_email($_POST['email'] )) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter valid entries.');
				$this->mo_oauth_show_error_message();
				return;
			} else {
				$email = sanitize_email( $_POST['email'] );
				$password = stripslashes( $_POST['password'] );
			}

			update_option( 'mo_oauth_admin_email', $email );
			update_option( 'password', $password );
			$customer = new Mo_Rocketchat_Server_Customer();
			$content = $customer->get_customer_key();
			$customerKey = json_decode( $content, true );
			if( json_last_error() == JSON_ERROR_NONE ) {
				update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
				update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
				update_option( 'customer_token', $customerKey['token'] );
				update_option( 'mo_oauth_admin_phone', $customerKey['phone'] );
				delete_option( 'password' );
				update_option( 'message', 'Customer retrieved successfully');
				delete_option( 'verify_customer' );
				$this->mo_oauth_show_success_message();
			} else {
				update_option( 'message', 'Invalid username or password. Please try again.');
				$this->mo_oauth_show_error_message();
			}
		} else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_add_client" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_add_client_form_field'] ) ), 'mo_oauth_add_client_form' ) ) {
			$client_id=moosGenerateRandomString(30);
			$client_secret=moosGenerateRandomString(30);
			$MoRocketchatServerDb = new MoRocketchatServerDb();
            $MoRocketchatServerDb->update_db($client_id,$client_secret);

		}else if(isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'] ))== "redirectUrlSave" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_client_redirect_url_form_field'] ) ), 'mo_oauth_client_redirect_url_form' ) ){
			$redirect_url_save =sanitize_text_field($_POST['mo_oauth_client_redirect_url']);
			$MoRocketchatServerDb = new MoRocketchatServerDb();
			$MoRocketchatServerDb->update_redirect_url($redirect_url_save); 
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_update_client" ) {
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null(sanitize_text_field($_POST['mo_oauth_custom_client_name'])) || $this->mo_oauth_check_empty_or_null(sanitize_text_field($_POST['mo_oauth_client_redirect_url']))) {
				update_option( 'message', 'Please enter valid Client Name and Redirect URI.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$client_name = stripslashes( sanitize_text_field($_POST['mo_oauth_custom_client_name'] ));
				$redirect_url = stripslashes(sanitize_text_field( $_POST['mo_oauth_client_redirect_url'] ));
				$MoRocketchatServerDb = new MoRocketchatServerDb();
				$MoRocketchatServerDb->update_client($client_name, $redirect_url);
				update_option( 'message', 'Your settings are saved successfully.' );
				$this->mo_oauth_show_success_message();
			}
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_attribute_mapping" ) {
			$appname = stripslashes(sanitize_text_field($_POST['mo_oauth_app_name']));
			$email_attr = stripslashes(sanitize_email($_POST['mo_oauth_email_attr']));
			$name_attr = stripslashes(sanitize_text_field($_POST['mo_oauth_name_attr'] ));

			$appslist = get_option('mo_oauth_apps_list');
			foreach($appslist as $key => $currentapp){
				if($appname == $key){
					$currentapp['email_attr'] = $email_attr;
					$currentapp['name_attr'] = $name_attr;
					$appslist[$key] = $currentapp;
					break;
				}
			}
			update_option('mo_oauth_apps_list', $appslist);
			update_option( 'message', 'Your settings are saved successfully.' );
			$this->mo_oauth_show_success_message();
			wp_redirect('admin.php?page=mo_oauth_server_settings&tab=config&action=update&app='.urlencode($appname));
		} else if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_server_master_switch") {
			if(!isset($_POST['mo_server_master_switch'])) {
				update_option('mo_oauth_server_master_switch', 'off');
			} else {
				update_option('mo_oauth_server_master_switch', 'on');
			}
			update_option( 'message', 'Your settings are saved successfully.' );
			$this->mo_oauth_show_success_message();
			wp_redirect('admin.php?page=mo_oauth_server_settings&tab=general_settings');
		} else if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_general_settings") {
			if(isset($_POST['mo_server_token_length'])) {
				update_option('mo_oauth_server_token_length', (int) stripslashes(sanitize_text_field($_POST["mo_server_token_length"])));
			}

			if(isset($_POST['expiry_time'])) {
				update_option( 'mo_oauth_expiry_time', intval(sanitize_text_field($_POST['expiry_time'])));
			}

			if(isset($_POST['refresh_expiry_time'])) {
				update_option( 'mo_oauth_refresh_expiry_time', intval(sanitize_text_field($_POST['refresh_expiry_time'])));
			}

			update_option( 'message', 'Your settings are saved successfully.' );
			$this->mo_oauth_show_success_message();
			wp_redirect('admin.php?page=mo_oauth_server_settings&tab=general_settings');
		} else if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_server_enforce_state") {
			if(!isset($_POST['mo_server_enforce_state'])) {
				update_option('mo_oauth_server_enforce_state', 'off');
			} else {
				update_option('mo_oauth_server_enforce_state', 'on');
			}

			update_option( 'message', 'Your settings are saved successfully.' );
			$this->mo_oauth_show_success_message();
			wp_redirect('admin.php?page=mo_oauth_server_settings&tab=general_settings');
		} else if(isset($_POST['option']) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_server_enable_oidc") {
			if(!isset($_POST['mo_server_enable_oidc'])) {
				update_option('mo_oauth_server_enable_oidc', 'off');
			} else {
				update_option('mo_oauth_server_enable_oidc', 'on');
			}

			update_option( 'message', 'Your settings are saved successfully.' );
			$this->mo_oauth_show_success_message();
			wp_redirect('admin.php?page=mo_oauth_server_settings&tab=general_settings');	
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_contact_us_query_option" ) {
			if( mo_oauth_server_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			// Contact Us query
			$email = stripslashes(sanitize_email($_POST['mo_oauth_contact_us_email']));
			$phone = stripslashes(sanitize_text_field($_POST['mo_oauth_contact_us_phone']));
			$query = stripslashes(sanitize_textarea_field($_POST['mo_oauth_contact_us_query']));
			$customer = new Mo_Rocketchat_Server_Customer();
			if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $query ) ) {
				update_option('message', 'Please fill up Email and Query fields to submit your query.');
				$this->mo_oauth_show_error_message();
			} else {
				$submited = $customer->submit_contact_us( $email, $phone, $query );
				if ( $submited == false ) {
					update_option('message', 'Your query could not be submitted. Please try again.');
					$this->mo_oauth_show_error_message();
				} else {
					update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
					$this->mo_oauth_show_success_message();
				}
			}
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_resend_otp" ) {
			$email = get_option('mo_oauth_admin_email');
			if( mo_oauth_server_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			$customer = new Mo_Rocketchat_Server_Customer();
			$content = json_decode($customer->send_otp_token($email,''), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					update_option( 'message', ' A one time passcode is sent to ' . get_option('mo_oauth_admin_email') . ' again. Please check if you got the otp and enter it here.');
					$_SESSION['mo_oauth_transactionId'] = $content['txId'];
					update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_SUCCESS');
					$this->mo_oauth_show_success_message();
			}else{
					update_option('message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_FAILURE');
					$this->mo_oauth_show_error_message();
			}
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_change_email" && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mo_oauth_change_email_form_field'] ) ), 'mo_oauth_change_email_form' )) {
			update_option('verify_customer', '');
			update_option('mo_oauth_registration_status','');
			update_option('new_registration','true');
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_register_with_phone_option") {
			$phone = stripslashes(sanitize_text_field($_POST['phone']));
			$phone = str_replace(' ', '', $phone);
			$phone = str_replace('-', '', $phone);
			update_option('mo_oauth_admin_phone', $phone);
			$customer = new Mo_Rocketchat_Server_Customer();
			$content = json_decode($customer->send_otp_token('', $phone, FALSE, TRUE), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				update_option( 'message', ' A one time passcode is sent to ' . get_option('mo_oauth_admin_phone') . '. Please enter the otp here to verify your phone.');
				$_SESSION['mo_oauth_transactionId'] = $content['txId'];
				update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_SUCCESS_PHONE');
				$this->mo_oauth_show_success_message();
			} else {
				update_option('message','There was an error in sending SMS. Please click on Resend OTP to try again.');
				update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_FAILURE_PHONE');
				$this->mo_oauth_show_error_message();
			}
		} else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == "mo_oauth_change_phone" ) {
			update_option('mo_oauth_registration_status','');
		}
		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash($_POST['option'])) == 'mo_oauth_server_skip_feedback' && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['mo_oauth_mo_feedback_close_form_field'] ) ), 'mo_oauth_mo_feedback_close_form')) {
			deactivate_plugins( __FILE__ );
			update_option( 'message', 'Plugin deactivated successfully' );
			$this->mo_oauth_show_success_message();
		} else if ( isset( $_POST['mo_oauth_server_feedback'] ) and sanitize_text_field( wp_unslash($_POST['mo_oauth_server_feedback'])) == 'true' ) {
			$user = wp_get_current_user();
			$message = 'Plugin Deactivated:';
			$deactivate_reason         = array_key_exists( 'deactivate_reason_radio', $_POST ) ? sanitize_text_field($_POST['deactivate_reason_radio']) : false;
			$deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? sanitize_text_field($_POST['query_feedback']) : false;
			if ( $deactivate_reason ) {
				$message .= $deactivate_reason;
				if ( isset( $deactivate_reason_message ) ) {
					$message .= ':' . $deactivate_reason_message;
				}
				$email = get_option( "mo_oauth_admin_email" );
				if ( $email == '' ) {
					$email = $user->user_email;
				}
				$phone = get_option( 'mo_oauth_admin_phone' );
				//only reason
				$feedback_reasons = new Mo_Rocketchat_Server_Customer();
				$submited = json_decode( $feedback_reasons->mo_oauth_send_email_alert( $email, $phone, $message ), true );
				deactivate_plugins( __FILE__ );
				update_option( 'message', 'Thank you for the feedback.' );
				$this->mo_oauth_show_success_message();
			} else {
				update_option( 'message', 'Please Select one of the reasons ,if your reason is not mentioned please select Other Reasons' );
				$this->mo_oauth_show_error_message();
			}
		}
		// Request for demo functionality
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_server_demo_request_form") {
			if( mo_oauth_server_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			// Demo Request
			$email = sanitize_email($_POST['mo_oauth_server_demo_email']);
			$demo_plan = sanitize_text_field($_POST['mo_oauth_server_demo_plan']);
			$query = sanitize_textarea_field($_POST['mo_oauth_server_demo_usecase']);

			$customer = new Mo_Rocketchat_Server_Customer();
			if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $demo_plan ) || $this->mo_oauth_check_empty_or_null($query) ) {
				update_option('message', 'Please fill up Email field to submit your query.');
				$this->mo_oauth_show_error_message();
			} else {
				$submited = json_decode( $customer->mo_oauth_send_demo_alert( $email, $demo_plan, $query, "WP Login with RocketChat Demo Request - ".$email ), true );
				update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
				$this->mo_oauth_show_success_message();
			}
		}
	}

	function mo_oauth_get_current_Mo_Rocketchat_Server_Customer(){
		$customer = new Mo_Rocketchat_Server_Customer();
		$content = $customer->get_customer_key();
		$customerKey = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
			update_option( 'customer_token', $customerKey['token'] );
			update_option( 'password', '' );
			update_option( 'message', 'Customer retrieved successfully' );
			update_option('mo_oauth_registration_status','MO_OAUTH_CUSTOMER_RETRIEVED');
			delete_option('verify_customer');
			delete_option('new_registration');
			$this->mo_oauth_show_success_message();
			//rocketchat_oauth_server_page();
		} else {
			update_option( 'message', 'You already have an account with miniOrange. Please enter a valid password.');
			update_option('verify_customer', 'true');
			delete_option('new_registration');
			//rocketchat_oauth_server_page();
			$this->mo_oauth_show_error_message();

		}
	}

	function create_customer(){
		$customer = new Mo_Rocketchat_Server_Customer();
		$customerKey = json_decode( $customer->create_customer(), true );
		if( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
			$this->mo_oauth_get_current_Mo_Rocketchat_Server_Customer();
			delete_option('mo_oauth_new_customer');
		} else if( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
			update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
			update_option( 'customer_token', $customerKey['token'] );
			update_option( 'password', '');
			update_option( 'message', 'Registered successfully.');
			update_option('mo_oauth_registration_status','MO_OAUTH_REGISTRATION_COMPLETE');
			update_option('mo_oauth_new_customer',1);
			delete_option('verify_customer');
			delete_option('new_registration');
			$this->mo_oauth_show_success_message();
		}
	}

	function mo_oauth_show_curl_error() {
		if( mo_oauth_server_is_curl_installed() == 0 ) {
			update_option( 'message', '<a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled. Please enable it to continue.');
			$this->mo_oauth_show_error_message();
			return;
		}
	}

	function mo_oauth_shortcode_login(){
		$mowidget = new Mo_Oauth_Widget;
		$mowidget->mo_oauth_login_form();
	}

}


	function mo_oauth_server_is_customer_registered() {
		$email 			= get_option('mo_oauth_admin_email');
		$customerKey 	= get_option('mo_oauth_admin_customer_key');
		if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}

	function mo_oauth_server_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else {
			return 0;
		}
	}

	function moosGenerateRandomString($length = 10) {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

new mo_oauth_server_rocketchat;

