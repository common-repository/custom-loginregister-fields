<?php
/*
Plugin Name: Custom Login/Register Fields
Description: Add custom fields to the WordPress login and registration forms.
Version: 1.0.1
Author: Ketu Sojitra
Text Domain: custom-loginregister-fields
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Enqueue admin scripts and styles
function clrfEnqueueAdminacripts($hook_suffix) {
    if ($hook_suffix !== 'toplevel_page_custom_login_register') {
        return;
    }
    wp_enqueue_script('clrf-admin-js', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
    wp_enqueue_style('clrf-admin-css', plugin_dir_url(__FILE__) . 'css/admin.css', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'clrfEnqueueAdminacripts');

// Add admin menu
function clrfAddAdminmenu() {
    add_menu_page('Custom Login/Register Fields', 'Custom Fields', 'manage_options', 'custom_login_register', 'clrfAdminPage', 'dashicons-admin-generic');
}
add_action('admin_menu', 'clrfAddAdminmenu');

// Admin page content
function clrfAdminPage() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Custom Login/Register Fields', 'custom-loginregister-fields'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('clrf_custom_fields'); ?>
            <?php wp_nonce_field('clrf_custom_fields_nonce', 'clrf_custom_fields_nonce_field'); ?>
            <div id="clrf-fields-container">
                <h2><?php esc_html_e('Add New Field', 'custom-loginregister-fields'); ?></h2>
                <div id="clrf-new-field">
                    <input type="text" id="clrf-field-label" placeholder="Field Label" />
                    <select id="clrf-field-type">
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="tel">Tel</option>
                        <option value="textarea">Textarea</option>
                    </select>
                    <input type="text" id="clrf-field-validation" placeholder="Validation (e.g., required|pattern=[0-9]{10})" />
                    <button type="button" id="clrf-add-field"><?php esc_html_e('Add Field', 'custom-loginregister-fields'); ?></button>
                </div>
                <h2><?php esc_html_e('Custom Fields', 'custom-loginregister-fields'); ?></h2>
                <ul id="clrf-fields-list"></ul>
            </div>
            <input type="hidden" name="clrf_custom_fields" id="clrf-custom-fields" value="<?php echo esc_attr(wp_json_encode(get_option('clrf_custom_fields', array()))); ?>">
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
function clrfRegisterSettings() {
    register_setting('clrf_custom_fields', 'clrf_custom_fields', 'clrfSanitizeCustomFields');
    add_settings_section('clrf_custom_fields_section', __('Custom Fields', 'custom-loginregister-fields'), null, 'clrf_custom_fields');
    add_settings_field('clrf_custom_fields', __('Custom Fields', 'custom-loginregister-fields'), 'clrfCustomFieldsCallback', 'clrf_custom_fields', 'clrf_custom_fields_section');
}
add_action('admin_init', 'clrfRegisterSettings');

// Sanitize custom fields
function clrfSanitizeCustomFields($input) {
    if (!isset($_POST['clrf_custom_fields_nonce_field']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['clrf_custom_fields_nonce_field'])), 'clrf_custom_fields_nonce')) {
        return array();
    }
     // Decode the JSON string into an array
    // Check if the input is a valid JSON string
    if (is_string($input)) {
        $input = json_decode(stripslashes($input), true);
    }

    // If the input is not an array, return an empty array
    if (!is_array($input)) {
        return array();
    }
    
    if (!empty($input) && is_array($input)) {
        foreach ($input as $key => $field) {
            $input[$key]['label'] = sanitize_text_field($field['label']);
            $input[$key]['type'] = sanitize_text_field($field['type']);
            $input[$key]['validation'] = sanitize_text_field($field['validation']);
        }
    } else {
        $input = array();
    }
    return $input;
}

// Custom fields callback
function clrfCustomFieldsCallback() {
    // Rendered by JavaScript
}

/**
 * Outputs custom fields to the registration form.
 *
 * Note: Nonce verification is not performed here because this function is only responsible for rendering form fields.
 * Nonce verification is handled in the clrf_custom_registration_errors and clrf_custom_user_register functions.
 */
function clrfCustomRegistrationFields() {
    $custom_fields = get_option('clrf_custom_fields', array());
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $field) {
            $validation = !empty($field['validation']) ? ' ' . $field['validation'] : '';
            $string = preg_replace('/\s+/', '', $field['label']);
            ?>
            <p>
                <label for="<?php echo esc_attr($string); ?>"><?php echo esc_html($field['label']); ?><br/></label>
                <input type="<?php echo esc_attr(sanitize_text_field($field['type'])); ?>" name="<?php echo esc_attr(sanitize_text_field($string)); ?>" id="<?php echo esc_attr(sanitize_text_field($string)); ?>" class="input" value="<?php if (!empty($_POST[$string])) echo esc_attr(sanitize_text_field(wp_unslash($_POST[$string]))); ?>" size="25" <?php echo esc_attr(sanitize_text_field($validation)); ?> />
            </p>
            <?php
        }
    }
    wp_nonce_field('clrf_custom_registration_fields_nonce', 'clrf_custom_registration_fields_nonce_field');
}
add_action('register_form', 'clrfCustomRegistrationFields');


// Validate and save custom fields during registration
function clrfCustomRegistrationErrors($errors, $sanitized_user_login, $user_email) {
    if (!isset($_POST['clrf_custom_registration_fields_nonce_field']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['clrf_custom_registration_fields_nonce_field'])), 'clrf_custom_registration_fields_nonce')) {
        $errors->add('nonce_error', __('<strong>ERROR</strong>: Please try again.', 'custom-loginregister-fields'));
        return $errors;
    }

    $custom_fields = get_option('clrf_custom_fields', array());
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $field) {
            $string = preg_replace('/\s+/', '', $field['label']);
            $value = isset($_POST[$string]) ? sanitize_text_field(wp_unslash($_POST[$string])) : '';
            $validation_rules = explode('|', $field['validation']);
            foreach ($validation_rules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    // translators: %s is the label of the custom field.
                    $errors->add($field['label'] . '_error', sprintf(__('<strong>ERROR</strong>: Please enter your %s.', 'custom-loginregister-fields'), $field['label']));
                } elseif (strpos($rule, 'pattern=') === 0) {
                    $pattern = str_replace('pattern=', '', $rule);
                    if (!preg_match('/' . $pattern . '/', $value)) {
                        // translators: %s is the label of the custom field.
                        $errors->add($field['label'] . '_error', sprintf(__('<strong>ERROR</strong>: The %s you entered is not valid.', 'custom-loginregister-fields'), $field['label']));
                    }
                }
            }
        }
    }
    return $errors;
}
add_filter('registration_errors', 'clrfCustomRegistrationErrors', 10, 3);


function clrfCustomUserRegister($user_id) {
    if (!isset($_POST['clrf_custom_registration_fields_nonce_field']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['clrf_custom_registration_fields_nonce_field'])), 'clrf_custom_registration_fields_nonce')) {
        return false;
    }

    $custom_fields = get_option('clrf_custom_fields', array());
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $field) {
            $string = preg_replace('/\s+/', '', $field['label']);
            if (!empty($_POST[$string])) {
                update_user_meta($user_id, $string, sanitize_text_field(wp_unslash($_POST[$string])));
            }
        }
    }
}
add_action('user_register', 'clrfCustomUserRegister');

// Show custom fields in user profile
function clrfShowCustomUserProfileFields($user) {
    $custom_fields = get_option('clrf_custom_fields', array());
    ?>
    <h3><?php esc_html_e('Custom Profile Information', 'custom-loginregister-fields'); ?></h3>
    <table class="form-table">
        <?php
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $field) {
                $string = preg_replace('/\s+/', '', $field['label']);
                $value = get_user_meta($user->ID, $string, true);
                ?>
                <tr>
                    <th><label for="<?php echo esc_attr($field['label']); ?>"><?php echo esc_html($field['label']); ?></label></th>
                    <td>
                        <input type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($string); ?>" id="<?php echo esc_attr($string); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" /><br/>
                        <?php // translators: %s is the label of the custom field. ?>
                        <span class="description"><?php printf(esc_html__('Please enter your %s.', 'custom-loginregister-fields'), esc_html($field['label'])); ?></span>
                    </td>
                </tr>
                <?php
            }
        }
        wp_nonce_field('clrf_custom_fields_update_nonce', 'clrf_custom_fields_update_nonce_field');
        ?>
    </table>
    <?php
}
add_action('show_user_profile', 'clrfShowCustomUserProfileFields');
add_action('edit_user_profile', 'clrfShowCustomUserProfileFields');

// Save custom fields in user profile
function clrfSaveCustomUserProfileFields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (!isset($_POST['clrf_custom_fields_update_nonce_field']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['clrf_custom_fields_update_nonce_field'])), 'clrf_custom_fields_update_nonce')) {
        return false;
    }
    
    $custom_fields = get_option('clrf_custom_fields', array());
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $field) {
            $string = preg_replace('/\s+/', '', $field['label']);
            if (isset($_POST[$string])) {
                update_user_meta($user_id, $string, sanitize_text_field(wp_unslash($_POST[$string])));
            } else {
                delete_user_meta($user_id, $string);
            }
        }
    }
}
add_action('personal_options_update', 'clrfSaveCustomUserProfileFields');
add_action('edit_user_profile_update', 'clrfSaveCustomUserProfileFields');
?>
