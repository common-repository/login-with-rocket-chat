<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

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
update_user_meta(get_current_user_id(),'dismissed_wp_pointers','');
?>