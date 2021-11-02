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
    if ('registrazione' !== $form_name) {
        return;
    }
    $form_data  = $record->get_formatted_data();

    $username   = $form_data['Email'];
    $email      = $form_data['Email'];
    $password   = $form_data['Password'];

    $user = wp_create_user($username,$password,$email);

    if (is_wp_error($user)){
        $ajax_handler->add_error_message("Creazione utenti non riuscita: ".$user->get_error_message());
        $ajax_handler->is_success = false;
        return;
    }

    // Assign Primary field value in the created user profile
    $first_name   =$form_data["Nome"];
    $last_name    =$form_data["Cognome"];
    wp_update_user(array("ID"=>$user,"first_name"=>$first_name,"last_name"=>$last_name));

    // Assign Additional added field value in the created user profile
    $azienda            =$form_data["Azienda"];
    $indirizzo_azienda  =$form_data["Indirizzo azienda"];
    $postazione_azienda =$form_data["Postazione in azienda"];
    $citta_azienda      =$form_data["Città"];
    $cap                =$form_data["CAP"];
    $stato              =$form_data["Stato"];

    update_user_meta($user, 'azienda', $azienda);
    update_user_meta($user, 'indirizzo_azienda', $indirizzo_azienda);
    update_user_meta($user, 'postazione_azienda', $postazione_azienda);
    update_user_meta($user, 'shipping_first_name', $first_name);
    update_user_meta($user, 'shipping_last_name', $last_name);
    update_user_meta($user, 'shipping_company', $azienda);
    update_user_meta($user, 'shipping_address_1', $indirizzo_azienda);
    update_user_meta($user, 'shipping_city', $citta_azienda);
    update_user_meta($user, 'shipping_postcode', $cap);
    update_user_meta($user, 'shipping_country', $stato);

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
