<?php

/* 
Plugin Name: colbyTicket
Version: 1.0.2
Description: Plugin to integrate WordPress with Colby's web authentication ticket system
Author: Keith McGlauflin
Date: 09/25/2009

Copyright (C) 2013 Colby College - use with permission only!!!
*/

add_action('wp_login', array('colbyTicket','login'), 10, 2);
add_action('wp_logout', array('colbyTicket','logout'));
add_action('lost_password', array('colbyTicket','disable_function'));
add_action('retrieve_password', array('colbyTicket','disable_function'));
add_action('password_reset', array('colbyTicket','disable_function'));

class colbyTicket {

    function login($user_login, $user) {

        if( !isset($_COOKIE['ColbyAuth']) ) {

            $value = [
              'email' => $user->user_email,
              'roles' => (array) $user->roles,
            ];

            setcookie('ColbyAuth', json_encode($value), time() + 31536000, "/wp");
        }
    }
  
    function logout($user_id) {
        // die('logout');
        // expire cookie by setting the expiry time to the past
        setcookie('ColbyAuth', '', time() - 3600);
    }

    // disable reset, list and retrieve password features
    function disable_function() {
        die( __( 'Sorry, this feature is disabled.', 'colbyTicket' ));
    }
}

?>
