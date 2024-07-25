<?php
/*
Plugin Name: Simple Member Register
Description: Registers new members with a one-time payment.
Version: 1.0
Author: Your Name
*/

register_activation_hook(__FILE__, 'simple_member_install');
require_once('install.php');

add_shortcode('member_register_form', 'display_registration_form');

function simple_member_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_member';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        username varchar(60) NOT NULL,
        email varchar(100) NOT NULL,
        PRIMARY KEY  (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function display_registration_form() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
        // Perform the payment processing and user registration
        process_registration_form();
    } else {
        // Display the form
        echo '<form action="" method="POST">
            Username: <input type="text" name="username" required><br>
            Email: <input type="email" name="email" required><br>
            <input type="submit" name="submit" value="Register">
        </form>';
    }
}

function process_registration_form() {
    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);

    // Simulate payment via PayPal
    echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="YOUR_BUTTON_ID">
    <input type="hidden" name="custom" value="'.esc_attr(json_encode(array('username' => $username, 'email' => $email))).'">
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>';
}

add_action('wp', 'handle_paypal_return');

function handle_paypal_return() {
    if (isset($_GET['paypal']) && $_GET['paypal'] == 'return') {
        $data = json_decode(stripslashes($_GET['data']), true);
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'simple_member',
            array('username' => $data['username'], 'email' => $data['email']),
            array('%s', '%s')
        );

        echo '<p>Thank you for your payment and registration!</p>';
    }
}
