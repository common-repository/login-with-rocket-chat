<?php

class MoRocketchatServerDb
{

	function mo_plugin_activate()
	{
		global $wpdb;
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_clients (client_name VARCHAR(255), client_id VARCHAR(255), client_secret VARCHAR(255), redirect_uri VARCHAR(255), active_oauth_server_id INT);";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_access_tokens (access_token VARCHAR(255), client_id VARCHAR(255), user_id INT, expires TIMESTAMP, scope VARCHAR(255));";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_authorization_codes (authorization_code VARCHAR(255), client_id VARCHAR(255), user_id INT, redirect_uri VARCHAR(255), expires TIMESTAMP, scope VARCHAR(255), id_token VARCHAR(255));";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_refresh_tokens (refresh_token VARCHAR(255), client_id VARCHAR(255), user_id INT, expires TIMESTAMP, scope VARCHAR(255));";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_scopes (scope varchar(100), is_default BOOLEAN, UNIQUE (scope));";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_users (username VARCHAR(255) NOT NULL, password VARCHAR(2000), first_name VARCHAR(255), last_name VARCHAR(255), CONSTRAINT username_pk PRIMARY KEY (username));";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_public_keys (client_id VARCHAR(80), public_key VARCHAR(8000), private_key VARCHAR(8000), encryption_algorithm VARCHAR(80) DEFAULT 'RS256');";
		$wpdb->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."moos_oauth_authorized_apps (client_id TEXT, user_id INT);";
		$wpdb->query($sql);
		$wpdb->query("INSERT IGNORE INTO ".$wpdb->base_prefix."moos_oauth_scopes values('email', 1), ('profile', 0);");

		// check if the table moos_oauth_clients is already exist
		$table_name = $wpdb->base_prefix."moos_oauth_clients";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '" . DB_NAME ."' AND table_name = '". $table_name . "' AND column_name ='active_oauth_server_id'", ARRAY_A);
			if ( empty($row) ) {
				$sql = "ALTER TABLE ".$wpdb->base_prefix."moos_oauth_clients ADD active_oauth_server_id INT DEFAULT ".get_current_blog_id();
				$wpdb->query($sql);
			}
		}
	}


    
	function add_client($client_name, $client_secret, $redirect_url,$active_oauth_server_id)
	{
		global $wpdb;
		$client_name="Rocket Chat";
		$wpdb->query("INSERT INTO ".$wpdb->base_prefix."moos_oauth_clients (client_name, client_id, client_secret, redirect_uri,active_oauth_server_id ) VALUES ('".$client_name."', '".moosGenerateRandomString(30)."', '".$client_secret."', '".$redirect_url."','".$active_oauth_server_id."')");
	}

	function update_client($client_name, $redirect_url)
	{
		global $wpdb;
		$wpdb->query("UPDATE ".$wpdb->base_prefix."moos_oauth_clients SET redirect_uri = '".$redirect_url."' WHERE client_name = '".$client_name."' and active_oauth_server_id=".get_current_blog_id());
	}

    function update_db($client_id, $client_secret)
    {
   		global $wpdb;
   		$client_name="Rocket Chat";
		$wpdb->query("UPDATE ".$wpdb->base_prefix."moos_oauth_clients SET client_id = '".$client_id."',client_secret = '".$client_secret."' WHERE client_name = '".$client_name."' ");
    }

    function update_redirect_url($redirect_url)
    {
    	global $wpdb;
    	$client_name="Rocket Chat";
    	$wpdb->query("UPDATE ".$wpdb->base_prefix."moos_oauth_clients SET redirect_uri='".$redirect_url."' WHERE client_name ='".$client_name."' ");
    }

	function get_clients()
	{
		global $wpdb;
		$myrows = $wpdb->get_results("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_clients");
		return $myrows;
	}

	function delete_client($client_name)
	{
		global $wpdb;
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."moos_oauth_clients WHERE client_name = '".$client_name."' and active_oauth_server_id=".get_current_blog_id());
	}


	function delete_on_deactivate()
	{
		global $wpdb;
		$client_name="Rocket Chat";
		$wpdb->query("DELETE FROM " .$wpdb->base_prefix."moos_oauth_clients WHERE client_name = '".$client_name."'");
	}
}