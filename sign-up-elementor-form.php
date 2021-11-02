<?php
/**
 * Plugin Name: Sign up Elementor forms
 * Description: Create a new user using Elementor Pro form
 * Author:      Giacomo Lanzi
 * Author URI:  https://planbproject.it
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.2.0
 */

add_action( 'elementor_pro/forms/new_record',  'planbproject_elementor_form_create_new_user' , 10, 2 );
function planbproject_elementor_form_create_new_user($record,$ajax_handler) // creating function
{
    $form_name = $record->get_form_settings('form_name');

    //Check that the form is the "Sign Up" if not - stop and return;
    if ('Sign Up' !== $form_name) {                                     // Add form name
        return;
    }
    $form_data  = $record->get_formatted_data();                        // Get the form field value using field Labels

    $username   = $form_data['Email'];
    $email      = $form_data['Email'];
    $password   = $form_data['Password'];

    $user = wp_create_user($username,$password,$email);                 // User creation

    if (is_wp_error($user)){
        $ajax_handler->add_error_message("Creazione utenti non riuscita: ".$user->get_error_message());
        $ajax_handler->is_success = false;
        return;
    }

    // Assign Primary field value in the created user profile
    $first_name   =$form_data["Name"];
    $last_name    =$form_data["Last Name"];
    wp_update_user(array("ID"=>$user,"first_name"=>$first_name,"last_name"=>$last_name));

    // Assign Additional added field value in the created user profile
    $phone            =$form_data["Phone Number"];                      // Assign the value from the field with label Phone Number to the var $phone
    $bio              =$form_data["Biography"];

    update_user_meta($user, 'phone', $phone);                           // Update user meta custom field 'phone' with the value of $phone
    update_user_meta($user, 'user_bio', $bio);

    // Use this process to add as many field meta as you want on your user. Remember that the field has to be registered.
    
    /* Automatically log in the user and redirect the user to the home page */
    $creds= array(
        "user_login"=>$username,
        "user_password"=>$password,
        "remember"=>true
    );

    $signon = wp_signon($creds);

    if ($signon) {
        $ajax_handler->add_response_data( 'redirect_url', get_home_url() );
  }
}
