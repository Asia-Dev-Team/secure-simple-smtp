<?php
/*
Plugin Name: Secure Simple SMTP
Description: Secure plugin for configuring SMTP in the WordPress admin panel.
Version: 1.3
Author: @trueresort
*/

// Add settings page
add_action('admin_menu', 'secure_smtp_menu');
function secure_smtp_menu() {
    add_options_page('Secure SMTP Settings', 'Secure SMTP', 'manage_options', 'secure-smtp', 'secure_smtp_settings_page');
}

// Display settings page
function secure_smtp_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have access to this page.'));
    }
    ?>
    <div class="wrap">
        <h1>Secure SMTP Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('secure_smtp_options');
            do_settings_sections('secure_smtp');
            submit_button();
            ?>
        </form>
        <h2>Send Test Email</h2>
        <form method="post">
            <?php wp_nonce_field('send_test_email_action', 'send_test_email_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="test_email">Recipient Email</label></th>
                    <td><input type="email" name="test_email" id="test_email" required></td>
                </tr>
                <tr>
                    <th><label for="test_subject">Email Subject</label></th>
                    <td><input type="text" name="test_subject" id="test_subject" required></td>
                </tr>
                <tr>
                    <th><label for="test_message">Message</label></th>
                    <td><textarea name="test_message" id="test_message" rows="5" required></textarea></td>
                </tr>
            </table>
            <?php submit_button('Send Test Email', 'primary', 'send_test_email'); ?>
        </form>
    </div>
    <?php
    if (isset($_POST['send_test_email']) && check_admin_referer('send_test_email_action', 'send_test_email_nonce')) {
        secure_smtp_send_test_email();
    }
}

// Register settings
add_action('admin_init', 'secure_smtp_settings');
function secure_smtp_settings() {
    register_setting('secure_smtp_options', 'secure_smtp_options', 'secure_smtp_sanitize_options');
    add_settings_section('smtp_settings_section', 'SMTP Configuration', '__return_false', 'secure_smtp');

    add_settings_field('smtp_host', 'SMTP Host', 'secure_smtp_field_callback', 'secure_smtp', 'smtp_settings_section', ['id' => 'smtp_host']);
    add_settings_field('smtp_port', 'SMTP Port', 'secure_smtp_field_callback', 'secure_smtp', 'smtp_settings_section', ['id' => 'smtp_port']);
    add_settings_field('smtp_user', 'SMTP Username', 'secure_smtp_field_callback', 'secure_smtp', 'smtp_settings_section', ['id' => 'smtp_user']);
    add_settings_field('smtp_pass', 'SMTP Password', 'secure_smtp_field_callback', 'secure_smtp', 'smtp_settings_section', ['id' => 'smtp_pass']);
    add_settings_field('smtp_secure', 'Encryption', 'secure_smtp_field_callback', 'secure_smtp', 'smtp_settings_section', ['id' => 'smtp_secure']);
}

// Display fields
function secure_smtp_field_callback($args) {
    $options = get_option('secure_smtp_options');
    $value = esc_attr($options[$args['id']] ?? '');
    $type = $args['id'] === 'smtp_pass' ? 'password' : 'text';
    echo "<input type='$type' name='secure_smtp_options[{$args['id']}]' value='$value' />";
    if ($args['id'] === 'smtp_secure') {
        echo "<select name='secure_smtp_options[smtp_secure]'>
                <option value='tls' " . selected($value, 'tls', false) . ">TLS</option>
                <option value='ssl' " . selected($value, 'ssl', false) . ">SSL</option>
                <option value='' " . selected($value, '', false) . ">No encryption</option>
              </select>";
    }
}

// Sanitize data
function secure_smtp_sanitize_options($options) {
    return [
        'smtp_host' => sanitize_text_field($options['smtp_host']),
        'smtp_port' => absint($options['smtp_port']),
        'smtp_user' => sanitize_email($options['smtp_user']),
        'smtp_pass' => sanitize_text_field($options['smtp_pass']),
        'smtp_secure' => in_array($options['smtp_secure'], ['tls', 'ssl', '']) ? $options['smtp_secure'] : '',
    ];
}

// Mailer configuration
add_action('phpmailer_init', 'secure_smtp_mailer');
function secure_smtp_mailer($phpmailer) {
    $options = get_option('secure_smtp_options');
    $phpmailer->isSMTP();
    $phpmailer->Host = $options['smtp_host'] ?? '';
    $phpmailer->SMTPAuth = true;
    $phpmailer->Port = $options['smtp_port'] ?? 587;
    $phpmailer->Username = $options['smtp_user'] ?? '';
    $phpmailer->Password = $options['smtp_pass'] ?? '';
    $phpmailer->SMTPSecure = $options['smtp_secure'] ?? 'tls';
    $phpmailer->From = $options['smtp_user'] ?? 'no-reply@yourdomain.com';
    $phpmailer->FromName = get_bloginfo('name');
}

// Send test email
function secure_smtp_send_test_email() {
    $test_email = sanitize_email($_POST['test_email']);
    $test_subject = sanitize_text_field($_POST['test_subject']);
    $test_message = sanitize_textarea_field($_POST['test_message']);

    $sent = wp_mail($test_email, $test_subject, $test_message);

    if ($sent) {
        echo '<div class="notice notice-success is-dismissible"><p>Test email sent successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Error sending email. Check SMTP settings.</p></div>';
    }
}
?>
