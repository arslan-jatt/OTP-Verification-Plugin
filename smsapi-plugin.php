<?php
/*
Plugin Name: SMSAPI Plugin
Description: This plugin sends SMS via the SMSAPI.com API.
Version: 1.0
Author: Arlsan Ahmad
Author URI: https://nekdatechnologies.com
*/

// Hook the function to run when WordPress is loaded
add_action('init', 'send_sms_via_smsapi');

function send_sms_via_smsapi() {
    // Check if this is an AJAX request
    if (isset($_POST['action']) && $_POST['action'] === 'send_sms') {
        // Get the fullPhoneNumber value from the AJAX request
        $fullPhoneNumber = sanitize_text_field($_POST['fullPhoneNumber']);

        // Modify your SMS sending code accordingly
        $url = 'https://api.smsapi.com/mfa/codes';
        $ch = curl_init($url);

        // Check if cURL initialization was successful
        if ($ch === false) {
            die('cURL initialization failed');
        }

        $params = array(
            'phone_number' => $fullPhoneNumber,  // Use the fullPhoneNumber here
            'from'         => 'Preventivi',
            'content'      => 'Your code: [%code%]',
            'fast'         => '1'
        );

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer XUUFeMq2wj00kesyV9kGmXC6F1UfLxRdP5k84IJ6'));

        $result = curl_exec($ch);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if ($result === false) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // Handle the response here
            echo 'Response: ' . $result;
        }

        curl_close($ch);

        // Always exit to avoid further execution
        exit();
    }
}

// Hook the function to a WordPress action or filter, e.g., 'init'
add_action('init', 'send_smsapi_verification');

function send_smsapi_verification() {
    if (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $codeToVerify = sanitize_text_field($_POST['userEnteredOTP']);
        $fullPhoneNumber = sanitize_text_field($_POST['fullPhoneNumber']);
        
         // Retrieve fullPhoneNumber from the AJAX request
        $url = 'https://api.smsapi.com/mfa/codes/verifications';
        $ch = curl_init($url);

        $params = array(
           'phone_number' => $fullPhoneNumber,    // Use phone number 
            'code'         => $codeToVerify          // code to check
        );

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer XUUFeMq2wj00kesyV9kGmXC6F1UfLxRdP5k84IJ6'));

        $result = curl_exec($ch);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode === 204) {
            echo "OTP Verified Successfully!";
        } elseif ($httpCode === 404) {
            echo "Wrong OTP. Please try again.";
        } else {
            echo "An error occurred while verifying OTP.";
        }
        curl_close($ch);
        // Always exit to avoid further execution
        exit();
    }
}

// Enqueue JavaScript with localized variables
function enqueue_smsapi_script() {
    wp_enqueue_script('smsapi-script', plugin_dir_url(__FILE__) . 'js/smsapi-script.js', array('jquery'), '1.0', true);

    // Pass the necessary variables to JavaScript securely
    wp_localize_script('smsapi-script', 'smsapi_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('smsapi-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_smsapi_script');
?>
