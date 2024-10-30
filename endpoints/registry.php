<?php

require_once __DIR__.'/../vendor/autoload.php';

use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\Server as OAuth2Server;
use OAuth2\Storage\Pdo;
use OAuth2\Storage\Memory;
use OAuth2\OpenID\GrantType\AuthorizationCode;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\Response;
use OAuth2\Request;

/**
 * Function to register all routes with WordPress.
 */
function rocketchat_register_endpoints() {
    $default_routes = get_rocketchat_default_auth_routes();
    $new_routes = apply_filters( 'mo_oauth_server_define_routes', $default_routes );
    if ( ! empty( $new_routes ) ) {
        $default_routes = array_merge( $new_routes, $default_routes );
    }
    foreach( $default_routes as $route => $args ) {
        register_rest_route( 'moserver', $route, $args );
    }
}

/**
 * Function to get default routes or OAuth Endpoints.
 */
function get_rocketchat_default_auth_routes() {
    $default_routes = [
        'authorize' => [
            'methods'  => 'GET,POST',
            'callback' => 'rocketchat_authorize',
        ],
        'token' => [
            'methods'  => 'POST',
            'callback' => 'rocketchat_token'
        ],
        'resource' => [
            'methods'  => 'GET',
            'callback' => 'rocketchat_resource'
        ]
    ];
    return $default_routes;
}


function rocketchat_init_oauth_server() {
    $master_switch = (bool) get_option('mo_oauth_server_master_switch') ? get_option('mo_oauth_server_master_switch') : 'on';
    if($master_switch === 'off') {
        wp_die("Currently your OAuth Server is not responding to any API request, please contact your site administrator.<br><b>ERROR:</b> ERR_MSWITCH");
    }

    if (!file_exists($sqliteFile = __DIR__.'/../data/oauth.sqlite')) {
        include_once( dirname( dirname( dirname( __DIR__ ) ) ) . '/data/rebuild_db.php' );
    }

    $storage = new Pdo(array('dsn' => 'sqlite:'.$sqliteFile));

    // create array of supported grant types
    $grantTypes = array(
        'authorization_code' => new AuthorizationCode($storage),
        'user_credentials'   => new UserCredentials($storage),
        'refresh_token'      => new RefreshToken($storage, array(
            'always_issue_new_refresh_token' => true,
        )),
    );

    $enforce_state = (bool) get_option('mo_oauth_server_enforce_state') ? get_option('mo_oauth_server_enforce_state') : 'off';
    $enable_oidc = (bool) get_option('mo_oauth_server_enable_oidc') ? get_option('mo_oauth_server_enable_oidc') : 'on';
    // instantiate the oauth server
    $config = [
        'enforce_state' => ($enforce_state !== 'off'),
        'allow_implicit' => true,
        'use_openid_connect' => ($enable_oidc === 'on'),
        'access_lifetime'        => get_option('mo_oauth_expiry_time')?get_option('mo_oauth_expiry_time'):3600,
        'refresh_token_lifetime' => get_option('mo_oauth_refresh_expiry_time')?get_option('mo_oauth_refresh_expiry_time'):1209600,
        'issuer' => site_url() . '/wp-json/moserver',
    ];
    $server = new OAuth2Server($storage, $config, $grantTypes);
    return $server;
}


function rocketchat_authorize() {
    if ( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'])) === 'POST' ) {
        rocketchat_validate_authorize_consent();
        exit();
    }
    $request = Request::createFromGlobals();
    $response = new Response();
    $server = rocketchat_init_oauth_server();

    if ( ! $server->validateAuthorizeRequest( $request, $response ) ) {
		$response->send();
		exit;
    }
    $prompt = $request->query( 'prompt' ) ? $request->query( 'prompt' ) : 'consent';
    if ( ! $request->query( 'ignore_prompt' ) && $prompt ) {
        if ( $prompt == 'login' ) {
            wp_logout();
            $actual_link = rocketchat_get_current_page_url();
            wp_redirect(site_url()."/wp-login.php?redirect_to=".urlencode(str_replace( 'prompt=login', 'prompt=consent', $actual_link )));
            exit();
        }
    }
    $current_user = rocketchat_check_user_login( $request->query( 'client_id' ) );
    if ( ! $current_user ) {
        $actual_link = rocketchat_get_current_page_url();
        wp_redirect(site_url()."/wp-login.php?redirect_to=".urlencode($actual_link));
        exit;
    }

    $prompt_grant = (bool) get_option( 'mo_oauth_server_prompt_grant' ) ? get_option( 'mo_oauth_server_prompt_grant' ) : 'on';
    $is_authorized = true;
    if ( 'on' === $prompt_grant ) {
        $client_id = $request->query( 'client_id' );
        $grant_status = is_null( $client_id ) ? false : get_user_meta( $current_user->ID, 'mo_oauth_server_granted_' . $client_id, true );
        $prompt       = ( 'allow' === $grant_status && $request->query( 'prompt' ) !== 'consent' ) || ( 'deny' === $grant_status && 'allow' === $prompt ) ? 'allow' : 'consent';
        if ( 'allow' === $prompt ) {
            $grant_status = 'allow';
        }
        if ( $grant_status == 'allow' && $prompt !== 'consent' ) {
			$is_authorized = true;
		} elseif ( $grant_status == 'deny' && $prompt !== 'consent' ) {
            $is_authorized = false;
        } elseif ( $grant_status === false || $prompt === 'consent' ) {
            $client_credentials = $server->getStorage( 'client_credentials' )->getClientDetails( $request->query( 'client_id' ) );
            rocketchat_oauth_server_render_consent_screen( $client_credentials );
            exit();
        }
    }
    $server->handleAuthorizeRequest($request, $response, $is_authorized, $current_user->ID);
    $response->send();
    exit();
}

function rocketchat_token() {
    if ( isset( $_POST['grant_type'] ) ) {
        $grant          = sanitize_text_field( $_POST['grant_type'] );
        $allowed_grants = [ 'authorization_code', 'implicit' ];
        if ( ! in_array( $grant, $allowed_grants ) ) {
            wp_send_json( [
                'error' => 'invalid_grant',
                'error_description' => 'The "grant_type" requested is unsupported or invalid',
            ], 400 );
        }
    }
    $request = Request::createFromGlobals();
    $server = rocketchat_init_oauth_server();
    $server->handleTokenRequest( $request )->send();
	exit;
}

function rocketchat_resource() {
    $request = Request::createFromGlobals();
    $response = new Response();
    $server = rocketchat_init_oauth_server();

    if (!$server->verifyResourceRequest($request, $response)) {
        $response = $server->getResponse();
        $response->send();
        exit();
    }
    $token = $server->getAccessTokenData($request, $response);
    $user_info = rocketchat_get_token_user_info( $token );
    if ( is_null( $user_info ) || empty( $user_info ) ) {
        wp_send_json(
            [
                'error' => 'invalid_token',
                'desc'  => 'access_token provided is either invalid or does not belong to a valid user.'
            ],
            403
        );
    }
    $api_response = [
        'ID' => $user_info->ID,
        'id' => $user_info->ID,
        'username' => $user_info->user_login
    ];
    return $api_response;
}

function rocketchat_get_logged_user_from_auth_cookie() {
    $auth_cookie = wp_parse_auth_cookie( '', 'logged_in' );
    if ( ! $auth_cookie || is_wp_error( $auth_cookie ) || ! $auth_cookie['token'] || ! $auth_cookie['username'] ) {
        return false;
    }
    return $auth_cookie;
}

function rocketchat_get_token_user_info( $token = null ) {
    if ( $token === null || ! isset( $token['user_id'] ) ) {
        return [];
    }
    $user_info = get_userdata( $token['user_id'] );
    if ( null === $user_info ) {
        return [];
    }
    return $user_info;
}

function rocketchat_check_user_login( $client_id ) {
    $current_user_cookie = rocketchat_get_logged_user_from_auth_cookie();
    if ( ! $current_user_cookie ) {
        return false;
    }
    global $wpdb;
    $server_details = $wpdb->get_results("SELECT active_oauth_server_id from ".$wpdb->base_prefix."moos_oauth_clients where client_id='".sanitize_text_field($_GET['client_id'])."'");
    if($server_details == NULL){
        wp_die("Your client id is invalid. Please contact to your administrator.");
        exit();
    }
    $user_array = get_users( array( 'blog_id' => $server_details[0]->active_oauth_server_id ) );
    $user_data = false;
    foreach( $user_array as $user ) {
        if($user->user_login === $current_user_cookie['username'] ) {
            $user_data = $user;
            break;
        }
    }
    if( $user_data === false ) {
        wp_logout();
        wp_die("Invalid credentials. Please contact to your administrator.");
    }

    return $user_data;
}

function rocketchat_oauth_server_render_consent_screen( $client_credentials ) {
    $authorize_dialog_template = __DIR__ . '/template/authorize_dialog.php';
    $authorize_dialog_template = apply_filters( 'mo_oauth_server_authorize_dialog_template_path', $authorize_dialog_template );
    header( 'Content-Type: text/html' );
    include $authorize_dialog_template;
    rocketchat_mo_server_emit_html( $client_credentials );
    exit();
}

function rocketchat_validate_authorize_consent() {
    $user = rocketchat_check_user_login( sanitize_text_field($_REQUEST['client_id'] ));
    if ( isset( $_POST['authorize-dialog'] ) ) {
        if ( wp_verify_nonce( $_POST['nonce'], 'mo-oauth-server-authorize-dialog' ) ) {
            $response = sanitize_text_field($_POST['authorize']);
            update_user_meta( $user->ID, 'mo_oauth_server_granted_' . sanitize_text_field($_REQUEST['client_id']), $response );
        }
        $current_url = explode( '?', rocketchat_get_current_page_url() )[0];
        $_GET['prompt'] = $response;
        wp_safe_redirect( $current_url . '?' . http_build_query( $_GET ) );
        exit();
    }
}

function rocketchat_get_current_page_url() {
    return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/********************************* */

add_action( 'rest_api_init', 'rocketchat_register_endpoints' );
