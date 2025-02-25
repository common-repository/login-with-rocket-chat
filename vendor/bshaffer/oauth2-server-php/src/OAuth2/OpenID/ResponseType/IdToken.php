<?php

namespace OAuth2\OpenID\ResponseType;

use OAuth2\Encryption\EncryptionInterface;
use OAuth2\Encryption\Jwt;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface;

class IdToken implements IdTokenInterface
{
    protected $userClaimsStorage;
    protected $publicKeyStorage;
    protected $config;
    protected $encryptionUtil;

    public function __construct(UserClaimsInterface $userClaimsStorage, PublicKeyInterface $publicKeyStorage, array $config = array(), EncryptionInterface $encryptionUtil = null)
    {
        $this->userClaimsStorage = $userClaimsStorage;
        $this->publicKeyStorage = $publicKeyStorage;
        if (is_null($encryptionUtil)) {
            $encryptionUtil = new Jwt();
        }
        $this->encryptionUtil = $encryptionUtil;

        if (!isset($config['issuer'])) {
            throw new \LogicException('config parameter "issuer" must be set');
        }
        $this->config = array_merge(array(
            'id_lifetime' => 3600,
        ), $config);
    }

    public function getAuthorizeResponse($params, $userInfo = null)
    {
        // build the URL to redirect to
        $result = array('query' => array());
        $params += array('scope' => null, 'state' => null, 'nonce' => null);

        // create the id token.
        list($user_id, $auth_time) = $this->getUserIdAndAuthTime($userInfo);
        $userClaims = $this->userClaimsStorage->getUserClaims($user_id, $params['scope']);

        $id_token = $this->createIdToken($params['client_id'], $userInfo, $params['nonce'], $userClaims, null);
        $result["fragment"] = array('id_token' => $id_token);
        if (isset($params['state'])) {
            $result["fragment"]["state"] = $params['state'];
        }
        
        return array($params['redirect_uri'], $result);
    }

    public function createIdToken($client_id, $userInfo, $nonce = null, $userClaims = null, $access_token = null)
    {
        // pull auth_time from user info if supplied
        list($user_id, $auth_time) = $this->getUserIdAndAuthTime($userInfo);

        $token = array(
            'iss'        => $this->config['issuer'],
            'sub'        => $user_id,
            'aud'        => $client_id,
            'ID'         => $user_id,
            'iat'        => time(),
            'exp'        => time() + $this->config['id_lifetime'],
            'auth_time'  => $auth_time,
        );

        $user_info = get_userdata($user_id);
        $avatar = explode("//", get_avatar_url( $user_id, 32 ));
        if(is_array($avatar) && count($avatar) > 1) {
            if(strlen($avatar[1]) > 5)
                $avatar = $avatar[1];
        } else {
            $avatar = $avatar[0];
        }
        $api_response = array(
            'ID' => $user_info->ID,
            'id' => $user_info->ID,
            'username' => $user_info->user_login
        );
        $token['userinfo'] = $api_response;
        
        if ($nonce) {
            $token['nonce'] = $nonce;
        }

        if ($userClaims) {
            $token += $userClaims;
        }

        if ($access_token) {
            $token['at_hash'] = $this->createAtHash($access_token, $client_id);
        }
        
        return $this->encodeToken($token, $client_id);
    }

    protected function createAtHash($access_token, $client_id = null)
    {
        // maps HS256 and RS256 to sha256, etc.
        $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);
        $hash_algorithm = 'sha' . substr($algorithm, 2);
        $hash = hash($hash_algorithm, $access_token);
        $at_hash = substr($hash, 0, strlen($hash) / 2);

        return $this->encryptionUtil->urlSafeB64Encode($at_hash);
    }

    protected function encodeToken(array $token, $client_id = null)
    {
        // $private_key = $this->publicKeyStorage->getPrivateKey($client_id);
        // $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);
		$algorithm = "HS256";
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."moos_oauth_clients where client_id = '".$client_id."';", ARRAY_A);
		$client_secret = $result['client_secret'];
        return $this->encryptionUtil->encode($token, $client_secret, $algorithm);
    }

    private function getUserIdAndAuthTime($userInfo)
    {
        $auth_time = null;

        // support an array for user_id / auth_time
        if (is_array($userInfo)) {
            if (!isset($userInfo['user_id'])) {
                throw new \LogicException('if $user_id argument is an array, user_id index must be set');
            }

            $auth_time = isset($userInfo['auth_time']) ? $userInfo['auth_time'] : null;
            $user_id = $userInfo['user_id'];
        } else {
            $user_id = $userInfo;
        }

        if (is_null($auth_time)) {
            $auth_time = time();
        }

        // userInfo is a scalar, and so this is the $user_id. Auth Time is null
        return array($user_id, $auth_time);
    }
}
