<?php

defined('ABSPATH') || exit;

require_once LUKIO_USER_FORMS_DIR . '/vendors/php-jwt-6.8.0/src/JWT.php';
require_once LUKIO_USER_FORMS_DIR . '/vendors/php-jwt-6.8.0/src/Key.php';
require_once LUKIO_USER_FORMS_DIR . '/vendors/phpseclib/autoload.php';

use Firebase\JWT\JWT as JWT;
use Firebase\JWT\Key as Key;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;

/**
 * Lukio user forms google login class
 */
class Lukio_User_Forms_Google
{
    /**
     * url to get google public JWT keys from
     * @var string google public JWT keys url
     */
    const public_keys_url = 'https://www.googleapis.com/oauth2/v3/certs';

    /**
     * valid google iss keys to check the google token is set with
     * @var array valid google iss
     */
    const valid_iss = ['accounts.google.com', 'https://accounts.google.com'];

    /**
     * run needed checks to validate google token
     * 
     * @param array $token_data decoded  token data
     * 
     * @return bool true when checks passed
     * 
     * @throws Exception when a check failed
     * 
     * @author Itai Dotan
     */
    private static function google_token_checks($token_data)
    {
        $checks = array(
            'aud' => array(
                'valid' => $token_data['payload']['aud'] === Lukio_User_Forms_Options_Class::get_google_client(),
                'error_msg' => 'Invalid app token',
            ),
            'iss' => array(
                'valid' => in_array($token_data['payload']['iss'], self::valid_iss),
                'error_msg' => 'Invalid google signature',
            ),
        );
        foreach ($checks as $check) {
            if ($check['valid'] === false) {
                throw new Exception($check['error_msg']);
            }
        }

        return true;
    }

    /**
     * decode google token and arrange it to token_data format used in this class
     * 
     * @param string $token JWT google token
     * 
     * @return array formated token_data of the token
     * 
     * @author Itai Dotan
     */
    private static function decode_token($token)
    {
        $raw_token_array = explode('.', $token);
        $token_data = array(
            'headers' => json_decode(JWT::urlsafeB64Decode($raw_token_array[0]), true),
            'payload' => json_decode(JWT::urlsafeB64Decode($raw_token_array[1]), true)
        );
        return $token_data;
    }

    /**
     * get PEM public keys
     * 
     * @param array $cert cert set from google
     * 
     * @return string pem string
     */
    private static function get_Public_key($cert)
    {
        $modulus = new BigInteger(JWT::urlsafeB64Decode($cert['n']), 256);
        $exponent = new BigInteger(JWT::urlsafeB64Decode($cert['e']), 256);
        $component = ['n' => $modulus, 'e' => $exponent];

        $loader = PublicKeyLoader::load($component);

        return $loader->toString('PKCS8');
    }

    /**
     * get matching cert set from google public JWT keys
     * 
     * @param string $header_kid kid in the token header to find its set
     * 
     * @return array cert array
     * 
     * @throws Exception when can't get google keys
     * @throws Exception when there is no matching cert 
     * 
     * @author Itai Dotan
     */
    private static function get_public_google_certs($header_kid)
    {
        $certs_body = json_decode(wp_remote_get(self::public_keys_url)['body'], true);
        if (!isset($certs_body['keys']) || empty($certs_body['keys'])) {
            throw new Exception('Can\'t get google certs keys');
        }

        $cert = array();
        foreach ($certs_body['keys'] as $key_data) {
            if ($key_data['kid'] == $header_kid) {
                $cert = $key_data;
                break;
            }
        }
        if (empty($cert)) {
            throw new Exception('No matching cert found');
        }

        return $cert;
    }

    /**
     * handle google ajax request
     * 
     * login user, when there is no user with the token's email create new user first
     * 
     * @param string $token JWT google token
     *  
     * @author Itai Dotan
     */
    public static function handle_request($token)
    {
        $token_data = self::decode_token($token);
        $cert = self::get_public_google_certs($token_data['headers']['kid']);

        // run the google specific JWT checks
        self::google_token_checks($token_data);
        // create Key with encryption for the token
        $key = new Key(self::get_Public_key($cert), 'RS256');
        // send the jwt for validation and decodeing
        $payload = JWT::decode($token, $key);

        $google_email = $payload->email;
        $user = get_user_by('email', $google_email);

        if ($user) {
            // whem the email is registered get the user ID
            $user_id = $user->ID;
        } else {
            // create new user with the data from the token
            $user_id = wp_insert_user(array(
                'user_login' => $google_email,
                'user_email' => $google_email,
                'first_name' => $payload->given_name,
                'last_name' => $payload->family_name,
            ));
        }

        // login the user
        wp_clear_auth_cookie();
        wp_set_current_user($user_id); // Set the current user detail
        wp_set_auth_cookie($user_id, true); // Set auth details in cookie
    }
}
