<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))).'/wp-load.php';

/** show all errors! */

$master_switch = (bool) get_option('mo_oauth_server_master_switch') ? get_option('mo_oauth_server_master_switch') : 'on';
if($master_switch === 'off') {
    wp_die("Currently your OAuth Server is not responding to any API request, please contact your site administrator.<br><b>ERROR:</b> ERR_MSWITCH");
}
/** set up the silex application object */
$app = new Silex\Application();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app['debug'] = true;
$app->mount('/moserver', new OAuth2Demo\Server\Server());

// create an http foundation request implementing OAuth2\RequestInterface
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
$app->run($request);
