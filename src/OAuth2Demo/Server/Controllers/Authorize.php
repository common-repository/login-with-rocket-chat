<?php

namespace OAuth2Demo\Server\Controllers;

use Silex\Application;

class RocketchatAuthorize
{
    // Connects the routes in Silex
    public static function addRoutes($routing)
    {
        $routing->get('/authorize', array(new self(), 'authorize'))->bind('authorize');
        $routing->post('/authorize', array(new self(), 'authorizeFormSubmit'))->bind('authorize_post');
    }

    /**
     * The user is directed here by the client in order to authorize the client app
     * to access his/her data
     */
    public function authorize(Application $app)
    {
        rocketchat_authorize();
		//MO
		if ( !is_user_logged_in() ) {
			$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			wp_redirect(site_url()."/wp-login.php?redirect_to=".urlencode($actual_link));
			exit;
        }
        
        // for multisite env
        global $wpdb;
        $server_destails = $wpdb->get_results("SELECT active_oauth_server_id from ".$wpdb->base_prefix."moos_oauth_clients where client_id='".sanitize_text_field($_GET['client_id'])."'");
        if($server_destails == NULL){
            wp_die("Your client id is invalid. Please contact to your administrator.");
            exit();
        }
        $user_array = get_users(array( 'blog_id' => $server_destails[0]->active_oauth_server_id ));
        $check_user = false;
        foreach($user_array as $user){
            if($user->ID === get_current_user_id()){
                $check_user = true;
            }
        }
        // check user is belong to right oauth server
        if($check_user !== true){
            wp_logout();
            wp_die("Invalid credentials. Please contact to your administrator.");
            exit();
        }
        

        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

         // get the oauth response (configured in src/OAuth2Demo/Server/Server.php)
        $response = $app['oauth_response'];
        
        // validate the authorize request.  if it is invalid, redirect back to the client with the errors in tow
		$validateAuthorizeResponse = $server->validateAuthorizeRequest($app['request'], $response);
        
		if($validateAuthorizeResponse == "ALREADY_AUTHORIZED"){
			$user_id = get_current_user_id();
			return $server->handleAuthorizeRequest($app['request'], $response, true, $user_id);
		} else if (!$validateAuthorizeResponse) {
            return $server->getResponse();
        }
		
		
		$client_id = $app['request']->query->get('client_id');
		
        // display the "do you want to authorize?" form
        return $app['twig']->render('server/authorize.twig', array(
            'client_id' => $client_id,
            'response_type' => $app['request']->query->get('response_type'),
			'site_name' => get_bloginfo()
        ));
    }

    /**
     * This is called once the user decides to authorize or cancel the client app's
     * authorization request
     */
    public function authorizeFormSubmit(Application $app)
    {
		
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

         // get the oauth response (configured in src/OAuth2Demo/Server/Server.php)
        $response = $app['oauth_response'];

        // check the form data to see if the user authorized the request
        $authorized = (bool) $app['request']->request->get('authorize');

		//MO
        // call the oauth server and return the response
		
		$user_id = get_current_user_id();
		
        return $server->handleAuthorizeRequest($app['request'], $response, $authorized, $user_id);
    }
}
