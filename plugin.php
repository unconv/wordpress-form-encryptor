<?php
/**
 * Plugin Name: WP Form Encryptor
 * Description: Encrypts WordPress form field data
 * Version: 1.0.1
 * Author: Unconventional Coding
 */

require_once __DIR__ . '/includes.php';

use WPFormEncryptor\SettingsPage;
use WPFormEncryptor\Encryption;

new SettingsPage();

wp_enqueue_script( 'wp-form-encryptor', plugin_dir_url( __FILE__ ) . 'assets/js/wp-form-encryptor.js', ['jquery'], '1.0.0', true );

add_filter('query_vars', 'wp_form_encryptor_query_vars');

function wp_form_encryptor_query_vars($query_vars)
{
    $query_vars[] = 'wp_form_encryptor_action';
    return $query_vars;
}

add_action('init', 'wp_form_encryptor_decrypt_text');

function wp_form_encryptor_decrypt_text()
{
    if(
        current_user_can( 'manage_options' ) &&
        isset( $_GET['wp_form_encryptor_action'] ) &&
        $_GET['wp_form_encryptor_action'] === 'decrypt'
    ) {
        $encryption = new Encryption();

        $private_key = $_POST['key'];
        $text = $_POST['text'];

        $decrypted = $encryption->decrypt( $text, $private_key );

        echo $decrypted;

        exit;
    }
}

add_action('init', 'wp_form_encryptor_generate_keys');

function wp_form_encryptor_generate_keys()
{
    if(
        current_user_can( 'manage_options' ) &&
        isset( $_GET['wp_form_encryptor_action'] ) &&
        $_GET['wp_form_encryptor_action'] === 'generate_keys'
    ) {
        $encryption = new Encryption();
        $keys = $encryption->create_keys();

        update_option( 'wp_form_encryptor_public_key', $keys['public'] );

        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="private.key"' );
        header( 'Content-Length: ' . strlen( $keys['private'] ) );

        echo $keys['private'];

        exit;
    }
}

add_filter('wpcf7_posted_data', 'wp_form_encryptor_wpcf7_process');

function wp_form_encryptor_wpcf7_process( $fields )
{
    $public_key = get_option( 'wp_form_encryptor_public_key' );
    $encryption = new Encryption();

    $field_names = get_option('wp_form_encryptor_field_names', '');
    $field_names = explode( "\n", $field_names );
    $field_names = array_map( "rtrim", $field_names );
    $field_names = array_map( "mb_strtolower", $field_names );

    foreach ( $fields as $name => $value ) {
        if ( in_array( mb_strtolower( $name ), $field_names ) ) {
            $fields[$name] = $encryption->encrypt( $value, $public_key );
        }
    }

    return $fields;
}

add_filter('wpforms_process_filter', 'wp_form_encryptor_wpforms_process');

function wp_form_encryptor_wpforms_process( $fields )
{
    $public_key = get_option( 'wp_form_encryptor_public_key' );
    $encryption = new Encryption();

    $field_names = get_option('wp_form_encryptor_field_names', '');
    $field_names = explode( "\n", $field_names );
    $field_names = array_map( "rtrim", $field_names );
    $field_names = array_map( "mb_strtolower", $field_names );

    foreach ( $fields as &$field ) {
        if ( in_array( mb_strtolower( $field['name'] ), $field_names ) ) {
            $field['value'] = $encryption->encrypt( $field['value'], $public_key );
        }
    }

    return $fields;
}

add_filter('ninja_forms_submit_data', 'wp_form_encryptor_ninja_forms_process');

function wp_form_encryptor_ninja_forms_process( $form_data )
{
    $form_id = $form_data['form_id'];
    $fields = $form_data['fields'];

    $public_key = get_option( 'wp_form_encryptor_public_key' );
    $encryption = new Encryption();

    $field_names = get_option('wp_form_encryptor_field_names', '');
    $field_names = explode( "\n", $field_names );
    $field_names = array_map( "rtrim", $field_names );
    $field_names = array_map( "mb_strtolower", $field_names );

    foreach ( $fields as &$field ) {
        if ( in_array( mb_strtolower( $field['key'] ), $field_names ) ) {
            $field['value'] = $encryption->encrypt( $field['value'], $public_key );
        }
    }

    $form_data['fields'] = $fields;

    return $form_data;
}

add_filter('nf_field_add_option_group', 'wp_form_encryptor_add_field_setting');

function wp_form_encryptor_add_field_setting($field_settings)
{
    $field_settings['encrypt'] = array(
        'name' => 'encrypt',
        'label' => __('Encrypt field', 'wp-form-encryptor'),
        'display_function' => 'wp_form_encryptor_display_field_setting',
    );
    return $field_settings;
}

function wp_form_encryptor_display_field_setting($field_id, $data)
{
    $encrypt = Ninja_Forms()->form()->field($field_id)->get_setting('encrypt');
    ?>
    <tr>
        <th scope="row"><label for="encrypt"><?php _e('Encrypt field', 'wp-form-encryptor'); ?></label></th>
        <td>
            <input type="checkbox" name="encrypt" id="encrypt" <?php checked($encrypt); ?>>
            <p class="description"><?php _e('Check this box to encrypt the field', 'wp-form-encryptor'); ?></p>
        </td>
    </tr>
    <?php
}


