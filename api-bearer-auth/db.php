<?php

class API_Bearer_Auth_Db {

  private static $TOKEN_BYTE_LENGTH = 32;
  private static $JWT_ALG = 'HS256';
  private static $JWT_ALG_SIGNATURE = 'sha256';
  private static $datetimeFormat = 'Y-m-d H:i:s';

  /*
   * Returns WP user id from access token.
   * @param $access_token Access token of user
   * @return integer|null WP user is or null if nothing is found.
   */
  public function get_user_id_from_access_token($token) {
    global $wpdb;
    
    if (!defined('API_BEARER_JWT_SECRET')) {
      return $wpdb->get_var($wpdb->prepare('SELECT user_id
        FROM ' . $wpdb->base_prefix . 'user_tokens
        WHERE access_token = %s AND access_token_valid > NOW()', $token));
    }

    $payload = $this->verify_jwt($token);
    if (is_wp_error($payload)) {
      return $payload;
    }

    return $payload->sub;
  }

  public function get_user_token_info($user_id, $column_name) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SELECT ' . $column_name . ' FROM '
      . $wpdb->base_prefix . 'user_tokens
      WHERE user_id = %d LIMIT 1', $user_id));
  }

  /**
   * Returns user id for this refresh token.
   * @param string $refresh_token Refresh token.
   * @return integer|null User id or null when nothing is found.
   */
  public function get_user_id_from_refresh_token($token, $client_name = '') {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SELECT user_id
        FROM ' . $wpdb->base_prefix . 'user_tokens
        WHERE refresh_token = %s AND client_name = %s', $token, $client_name));
  }

  /**
   * Remove token for this user.
   * @param integer $id User id.
   * @return void
   */
  public function delete_by_user_id($id) {
    global $wpdb;
    $wpdb->delete($wpdb->base_prefix . 'user_tokens', [
      'user_id' => $id
    ]);
  }

  /**
   * Refresh the access token for the user that belongs to this refresh token.
   * @param $refresh_token Refresh token of this user.
   * @return array|false Array with new access token or false on error.
   */
  public function refresh_access_token($refresh_token, $user_id, $client_name = '') {
    global $wpdb;
    $token = $this->create_access_token($user_id);
    if ($wpdb->query($wpdb->prepare("UPDATE " . $wpdb->base_prefix . "user_tokens
      SET access_token = %s, access_token_valid = %s
      WHERE user_id = %d AND client_name = %s",
      $token['token'], $token['expires_datetime'], $user_id, $client_name)))
    {
      return [
        'access_token' => $token['token'],
        'expires_in' => $token['expires_in']
      ];
    }
    return false;
  }

  /*
   * Inserts or update access and refresh tokens for user after login.
   * @param $user_id ID of WP user
   * @return false on error or array with access and refresh token on success
   */
  public function login($user_id, $client_name = '') {
    global $wpdb;
    $access_token = $this->create_access_token($user_id);
    $refresh_token = $this->create_refresh_token($user_id);

    if ($wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->base_prefix . "user_tokens
      SET
      user_id = %d,
      client_name = %s,
      access_token = %s,
      access_token_valid = %s,
      refresh_token = %s
      ON DUPLICATE KEY UPDATE
      access_token = %s,
      access_token_valid = %s,
      refresh_token = %s",
      $user_id,
      $client_name,
      $access_token['token'],
      $access_token['expires_datetime'],
      $refresh_token,
      $access_token['token'],
      $access_token['expires_datetime'],
      $refresh_token)) !== false)
    {
      return [
        'access_token' => $access_token['token'],
        'expires_in' => $access_token['expires_in'],
        'refresh_token' => $refresh_token
      ];
    }

    // Error
    return false;

  }

  /*
   * Returns access token.
   */
  private function create_access_token($user_id) {
    $exp = new DateTime();
    $nowTs = $exp->getTimeStamp();
    $exp->add(new DateInterval('PT' . API_BEARER_ACCESS_TOKEN_VALID_IN_SECONDS . 'S'));
    $expTs = $exp->getTimeStamp();
    $expiresIn = $expTs - $nowTs;

    $token = null;
    if (!defined('API_BEARER_JWT_SECRET')) {
      $token = bin2hex(openssl_random_pseudo_bytes(self::$TOKEN_BYTE_LENGTH));
    } else {
      $header = $this->base64url_encode(json_encode(['typ' => 'JWT', 'alg' => self::$JWT_ALG]));
      $payload = $this->base64url_encode(json_encode([
        'exp' => $expTs,
        'sub' => $user_id
      ]));
      $dataEncoded = $header . '.' . $payload;
      $signature = hash_hmac(self::$JWT_ALG_SIGNATURE, $dataEncoded, API_BEARER_JWT_SECRET, true);
      $signatureEncoded = $this->base64url_encode($signature);
      $token = $dataEncoded . '.' . $signatureEncoded;
    }

    return [
      'token' => $token,
      'expires_datetime' => $exp->format(self::$datetimeFormat),
      'expires_in' => $expiresIn
    ];
  }

  private function verify_jwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
      return new WP_Error('api_api_bearer_auth_jwt',
          __('JWT token bad format', 'api_bearer_auth'), ['status' => 401]);
    }
    //$header = json_decode($this->base64url_decode($parts[0]));
    if ($this->base64url_encode(
      hash_hmac(
        self::$JWT_ALG_SIGNATURE, $parts[0] . '.' . $parts[1], API_BEARER_JWT_SECRET, true
      )
    ) !== $parts[2])
    {
      return new WP_Error('api_api_bearer_auth_signature',
          __('Signature could not be verified', 'api_bearer_auth'), ['status' => 401]);
    }

    $payload = json_decode($this->base64url_decode($parts[1]));

    if ($payload->exp < time()) {
      return new WP_Error('api_api_bearer_auth_token_expired',
          __('Token expired', 'api_bearer_auth'), ['status' => 401]);
    }

    return $payload;
  }

  private function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }
  
  private function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
  }

  /*
   * Returns refresh token.
   */
  private function create_refresh_token($user_id) {
    return bin2hex(openssl_random_pseudo_bytes(self::$TOKEN_BYTE_LENGTH));
  }

}
