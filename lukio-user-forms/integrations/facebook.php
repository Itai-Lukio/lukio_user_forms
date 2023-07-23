<?php

defined('ABSPATH') || exit;

require_once LUKIO_USER_FORMS_DIR . 'vendors/Facebook/autoload.php';

/**
 * Lukio user forms facebook login class
 */
class Lukio_User_Forms_Facebook
{
    /**
     * login the user, create a new user when yet to register with the user email
     * 
     * @param array $user_data user data retrieved from facebook API
     * 
     * @author Itai Dotan
     */
    private static function login_user($user_data)
    {
        $user = get_user_by('email', $user_data['email']);

        if ($user) {
            // whem the email is registered get the user ID
            $user_id = $user->ID;
        } else {
            // create new user with the data from the token
            $user_id = wp_insert_user(array(
                'user_login' => $user_data['email'],
                'user_email' => $user_data['email'],
                'first_name' => $user_data['first_name'],
                'last_name' => $user_data['last_name'],
            ));
        }

        // login the user
        wp_clear_auth_cookie();
        wp_set_current_user($user_id); // Set the current user detail
        wp_set_auth_cookie($user_id, true); // Set auth details in cookie
    }

    /**
     * handle facebook ajax request
     * 
     * login user, when there is no user with the token's email create new user first
     * 
     * @param string $access_token facebook access token
     *  
     * @throws Exception when facebook API returned an error
     * 
     * @author Itai Dotan
     */
    public static function handle_request($access_token)
    {
        $fb = new Facebook\Facebook([
            'app_id' => Lukio_User_Forms_Options_Class::get_active_option('facebook_app_id'),
            'app_secret' => Lukio_User_Forms_Options_Class::get_active_option('facebook_app_secret'),
            'default_graph_version' => 'v17.0',
        ]);

        $error = false;
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name,first_name,last_name,email', $access_token);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            $error = 'Graph returned an error: ' . $e->getMessage();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            $error = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        if ($error !== false) {
            throw new Exception($error);
        } else {
            $user = $response->getGraphUser();
            self::login_user($user);
        }
    }
}
