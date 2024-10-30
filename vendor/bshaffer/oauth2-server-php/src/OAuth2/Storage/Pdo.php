<?php

namespace OAuth2\Storage;

use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;

/**
 * Simple PDO storage for all storage types
 *
 * NOTE: This class is meant to get users started
 * quickly. If your application requires further
 * customization, extend this class or create your own.
 *
 * NOTE: Passwords are stored in plaintext, which is never
 * a good idea.  Be sure to override this for your application
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class Pdo implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    JwtBearerInterface,
    ScopeInterface,
    PublicKeyInterface,
    UserClaimsInterface,
    OpenIDAuthorizationCodeInterface
{
    protected $db;
    protected $config;

    public function __construct($connection, $config = array())
    {

    }

    /* OAuth2\Storage\ClientCredentialsInterface */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_clients where client_id = '".$client_id."';", ARRAY_A);

        // make this extensible
        return $result && $result['client_secret'] == $client_secret;
    }

    public function isPublicClient($client_id)
    {
        global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_clients where client_id = '".$client_id."';", ARRAY_A);
		if(!$result)
            return false;
        return empty($result['client_secret']);
    }

    /* OAuth2\Storage\ClientInterface */
    public function getClientDetails($client_id)
    {
        global $wpdb;
		$row = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_clients where client_id = '".$client_id."';", ARRAY_A);
		return $row;
    }

    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null)
    {
		global $wpdb;
		// if it exists, update it.
		if ($this->getClientDetails($client_id)) {
		   $wpdb->query($sql = sprintf("UPDATE ".$wpdb->base_prefix."moos_oauth_clients SET client_secret='".$client_secret."', redirect_uri='".$redirect_uri."', grant_types='".$grant_types."',  scope='".$scope."', user_id=".$user_id."  where client_id='".$client_id."'"));
        } else {
			return  $wpdb->query(sprintf("INSERT INTO ".$wpdb->base_prefix."moos_oauth_clients (client_id, client_secret, redirect_uri, grant_types, scope, user_id) VALUES ('".$client_id."', '".$client_secret."', '".$redirect_uri."', '".$grant_types."', '".$scope."', ".$user_id.")"));
        }
    }

	public function checkIfAlreadyAuthorized($client_id, $user_id)
    {
        global $wpdb;
		$result = $wpdb->get_row("SELECT count(*) as count FROM ".$wpdb->base_prefix."moos_oauth_authorized_apps where client_id = '".$client_id."' AND user_id='".$user_id."';", ARRAY_A);

        if ($result) {
			return $result['count'] > 0;
        }
        return false;
    }

	public function authorizeClient($client_id, $user_id)
    {
		global $wpdb;
		return $wpdb->query(sprintf("INSERT INTO ".$wpdb->base_prefix."moos_oauth_authorized_apps (client_id, user_id) VALUES ('".$client_id."', ".$user_id.")"));
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /* OAuth2\Storage\AccessTokenInterface */
    public function getAccessToken($access_token)
    {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_access_tokens where access_token = '".$access_token."';", ARRAY_A);
		if($result)
			$result['expires'] = strtotime($result['expires']);
		//error_log("result : ".print_r($result,true));
        return $result;
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {

		$expires = date('Y-m-d H:i:s', $expires);
		global $wpdb;
		if ($this->getAccessToken($access_token)) {
		   $wpdb->query($sql = sprintf("UPDATE ".$wpdb->base_prefix."moos_oauth_access_tokens SET client_id='".$client_id."', expires='".$expires."', user_id=".$user_id.",  scope='".$scope."'  where access_token='".$access_token."'"));
        } else {
			return  $wpdb->query(sprintf("INSERT INTO ".$wpdb->base_prefix."moos_oauth_access_tokens (access_token, client_id, expires, user_id, scope) VALUES ('".$access_token."', '".$client_id."', '".$expires."', ".$user_id.", '".$scope."')"));
        }

    }

    public function unsetAccessToken($access_token)
    {
		global $wpdb;
		return  $wpdb->query(sprintf("DELETE FROM ".$wpdb->base_prefix."moos_oauth_access_tokens where access_token = '".$access_token."'"));
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    public function getAuthorizationCode($code)
    {
		global $wpdb;
		$code = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_authorization_codes where authorization_code = '".$code."' ;", ARRAY_A);
		if ($code) {
            // convert date string back to timestamp
            $code['expires'] = strtotime($code['expires']);
        }

        return $code;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        if (func_num_args() > 6) {
            // we are calling with an id token
            return call_user_func_array(array($this, 'setAuthorizationCodeWithIdToken'), func_get_args());
        }

        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        // if it exists, update it.
		global $wpdb;
		if ($this->getAuthorizationCode($code)) {
		   $wpdb->query($sql = sprintf("UPDATE ".$wpdb->base_prefix."moos_oauth_authorization_codes SET client_id='".$client_id."', user_id=".$user_id.", redirect_uri='".$redirect_uri."', expires='".$expires."', scope='".$scope."' where authorization_code='".$code."'"));
        } else {
			return  $wpdb->query(sprintf("INSERT INTO ".$wpdb->base_prefix."moos_oauth_authorization_codes (authorization_code, client_id, user_id, redirect_uri, expires, scope) VALUES ('".$code."', '".$client_id."', ".$user_id.", '".$redirect_uri."', '".$expires."', '".$scope."')"));
        }

    }

    private function setAuthorizationCodeWithIdToken($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        global $wpdb;
        update_option('mo_oauth_server_current_id_token', $id_token);

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
           return $wpdb->query($sql = sprintf("UPDATE ".$wpdb->base_prefix."moos_oauth_authorization_codes SET client_id='".$client_id."', user_id=".$user_id.", redirect_uri='".$redirect_uri."', expires='".$expires."', scope='".$scope."', id_token ='".$id_token."' where authorization_code='".$code."'"));
        } else {
            return  $wpdb->query(sprintf("INSERT INTO ".$wpdb->base_prefix."moos_oauth_authorization_codes (authorization_code, client_id, user_id, redirect_uri, expires, scope, id_token) VALUES ('".$code."', '".$client_id."', ".$user_id.", '".$redirect_uri."', '".$expires."', '".$scope."', '".$id_token."')"));
        }
    }

    public function expireAuthorizationCode($code)
    {
		global $wpdb;
		return  $wpdb->query(sprintf("DELETE FROM ".$wpdb->base_prefix."moos_oauth_authorization_codes where authorization_code = '".$code."'"));
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    public function checkUserCredentials($username, $password)
    {
        if ($user = $this->getUser($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    public function getUserDetails($username)
    {
        return $this->getUser($username);
    }

    /* UserClaimsInterface */
    public function getUserClaims($user_id, $claims)
    {
        if (!$userDetails = $this->getUserDetails($user_id)) {
            return false;
        }

        $claims = explode(' ', trim($claims));
        $userClaims = array();

        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    // address is an object with subfields
                    $userClaims['address'] = $this->getUserClaim($validClaim, $userDetails['address'] ?: $userDetails);
                } else {
                    $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
                }
            }
        }

        return $userClaims;
    }

    protected function getUserClaim($claim, $userDetails)
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);

        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        }

        return $userClaims;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    public function getRefreshToken($refresh_token)
    {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."moos_oauth_refresh_tokens where refresh_token = '".$refresh_token."';", ARRAY_A);
		if($result)
			$result['expires'] = strtotime($result['expires']);
		return $result;
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        global $wpdb;
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        return $wpdb->query(sprintf("INSERT INTO ".$wpdb->base_prefix."moos_oauth_refresh_tokens (refresh_token, client_id, user_id, expires, scope) VALUES ('".$refresh_token."', '".$client_id."', ".$user_id.", '".$expires."', '".$scope."')"));
    }

    public function unsetRefreshToken($refresh_token)
    {
        $stmt = $this->db->prepare(sprintf('DELETE FROM %s WHERE refresh_token = :refresh_token', $this->config['refresh_token_table']));

        return $stmt->execute(compact('refresh_token'));
    }

    // plaintext passwords are bad!  Override this for your application
    protected function checkPassword($user, $password)
    {
        // return $user['password'] == sha1($password);
        return wp_check_password($password, $user["user_pass"], $user["ID"]);
    }

    public function getUser($username)
    {
        /*$stmt = $this->db->prepare($sql = sprintf('SELECT * from %s where username=:username', $this->config['user_table']));
        $stmt->execute(array('username' => $username));

        if (!$userInfo = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return false;
        }

        // the default behavior is to use "username" as the user_id
        return array_merge(array(
            'user_id' => $username
        ), $userInfo);*/

        //$user = get_user_by( 'user_login', $username);
        //return $user;

        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users where user_login = '".$username."';", ARRAY_A);

        if(!$result) {
            $result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users where user_email = '".$username."';", ARRAY_A);
        }

		if($result) {
            if(isset($result["ID"]))
                $result["user_id"] = $result["ID"];
            return $result;
        }
        else
            return false;
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        // do not store in plaintext
        $password = sha1($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where username=:username', $this->config['user_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (username, password, first_name, last_name) VALUES (:username, :password, :firstName, :lastName)', $this->config['user_table']));
        }

        return $stmt->execute(compact('username', 'password', 'firstName', 'lastName'));
    }

    /* ScopeInterface */
    public function scopeExists($scope)
    {
		if(empty($scope))
			return false;

        $scope = explode(' ', $scope);
        $whereIn = implode(',', $scope);
		$whereIn = str_replace(",","','",$whereIn);


		global $wpdb;
		$result = $wpdb->get_row("SELECT count(scope) as count  FROM ".$wpdb->base_prefix."moos_oauth_scopes where scope IN ('".$whereIn."');", ARRAY_A);

		if ($result)
			return $result['count'] == count($scope);

        return false;
    }

    public function getDefaultScope($client_id = null)
    {
		return 'profile';
    }

    /* JWTBearerInterface */
    public function getClientKey($client_id, $subject)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT public_key from %s where client_id=:client_id AND subject=:subject', $this->config['jwt_table']));

        $stmt->execute(array('client_id' => $client_id, 'subject' => $subject));

        return $stmt->fetchColumn();
    }

    public function getClientScope($client_id)
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    public function getJti($client_id, $subject, $audience, $expires, $jti)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT * FROM %s WHERE issuer=:client_id AND subject=:subject AND audience=:audience AND expires=:expires AND jti=:jti', $this->config['jti_table']));

        $stmt->execute(compact('client_id', 'subject', 'audience', 'expires', 'jti'));

        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return array(
                'issuer' => $result['issuer'],
                'subject' => $result['subject'],
                'audience' => $result['audience'],
                'expires' => $result['expires'],
                'jti' => $result['jti'],
            );
        }

        return null;
    }

    public function setJti($client_id, $subject, $audience, $expires, $jti)
    {
        $stmt = $this->db->prepare(sprintf('INSERT INTO %s (issuer, subject, audience, expires, jti) VALUES (:client_id, :subject, :audience, :expires, :jti)', $this->config['jti_table']));

        return $stmt->execute(compact('client_id', 'subject', 'audience', 'expires', 'jti'));
    }

    /* PublicKeyInterface */
    public function getPublicKey($client_id = null)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT public_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $result['public_key'];
        }
    }

    public function getPrivateKey($client_id = null)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT private_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $result['private_key'];
        }
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        return 'HS256';
    }

}
