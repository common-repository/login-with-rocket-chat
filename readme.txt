=== Login with Rocket Chat ===
Contributors: cyberlord92,oauth
Tags: rocket chat, rocketchat wordpress, rocketchat login, rocketchat sso, rocketchat plugins
Requires at least: 3.0.2
Tested up to: 5.9
Stable tag: 2.2.4
License: MIT/Expat
License URI: https://docs.miniorange.com/mit-license

WordPress Login with Rocket Chat plugin allows Login(Single Sign-On) to Rocket.Chat using WordPress account credentials.

You can setup your site as Identity Server to allow Login with WordPress or WordPress Login to other client application /site using OAuth / OpenID Connect protocols.

== Description ==

This plugin is used to setup any WordPress site as Identity Server to allow Users to Single Sign-On / Login into Rocket Chat client site / application with WordPress Login using OAuth / OpenID Connect protocol flows. Login with Rocket Chat plugin allows you to use WordPress as your OAuth Server (Identity Server) and access OAuth API’s.

The primary goal of this plugin is to allow users to interact with WordPress and Jetpack sites without requiring them to store sensitive credentials.

You can easily configure an OAuth server to protect your API with access tokens, or allow Rocket Chat clients to request new access tokens and refresh them.

= Features =

* Supports WordPress Login / Login with WordPress for RocketChat
* Attribute and Role mapping
* Block Unauthenticated Request To The REST API
* Token Length - Allows you to change the token length
* Redirect/Callback URI Validation - You can Enable/disable this feature, based on dynamic redirect to a different pages for certain conditions.
* OIDC Support - Supports OpenID Connect protocol
* Token Lifetime - Allow you to decide the token expiry time
* JWT Support
* Error Logging
* Enforce State Parameter - Based on client configuration, you can enable or disable state parameter
* Supports All Grant Types : Authorization Code Grant, Implicit Grant, Password Grant, Client Credentials Grant, Refresh Token Grant
* Server Response - Allows you to customize the attributes need to be sent in server response
* Extended OAuth API Support
* Multi-Site Support
* JWT Signing Algorithm - Supports
* Support for Introspection Endpoint


A grant is a method of acquiring an access token. Deciding which grants to implement depends on the type of client the end user will be using, and the experience you want for your users.

= We support following grants: =

* Authorization code grant: This code grant is used when there is a need to access the protected resources on behalf of another third party application.

* Implicit grant: This grant relies on resource owner and registration of redirect uri. In authorization code grant user needs to ask for authorization and access token each time, but here access token is granted for a particular redirect uri provided by client using a particular browser.

* Client credential grant: This grant type heads towards specific clients, where access token is obtained by client by only providing client credentials. This grant type is quiet confidential.

* Resource owner password credentials grant: This type of grant is used where resource owner has trust relationship with the client. Just by using username and password, provided by resource owner authorization and authentication can be achieve

* Refresh token grant: Access tokens obtained in OAuth flow eventually expire. In this grant type client can refresh his or her access token.


= REST API Authentication =
Rest API are very much open to interact. Creating posts, getting information of users and much more is readily available.
It secures the unauthorized access to your WordPress sites/pages using our <a href="https://wordpress.org/plugins/wp-rest-api-authentication/" target="_blank">WordPress REST API Authentication</a> plugin.

Click <a href="https://plugins.miniorange.com/step-by-step-guide-to-setup-login-into-rocket-chat-with-wordpress" target="_blank">here</a> to view step by step setup guide to configure the plugin.


== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`
2. Search for `Login with Rocket Chat`. Find and Install `Login with Rocket Chat`
3. Activate the plugin from your Plugins page

= From WordPress.org =
1. Download Login with Rocket Chat.
2. Unzip and upload the `miniorange-login-with-rocket-chat` directory to your `/wp-content/plugins/` directory.
3. Activate Login with Rocket Chat from your Plugins page.

== Frequently Asked Questions ==
= I need to customize the plugin or I need support and help? =
Please email us at info@xecurify.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.

== Screenshots ==
1. Add Redirect URL
2. Get Client ID and Client Secret
3. Server Response
4. Other configurations


== Changelog ==

= 2.2.4 =
* Compatibility with WordPress 5.9

= 1.2.0 =
* Bug fixes

= 1.1.2 =
* Bug fixes and readme update

= 1.1.1 =
* First version for Login with Rocket Chat.


== Upgrade Notice ==
= 1.1.1 =
* Initial Version

