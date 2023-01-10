<?php
namespace WPFormEncryptor;

class SettingsPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_item']);
        add_action('init', [$this, 'save_settings']);
    }

    public function add_menu_item()
    {
        add_menu_page(
            'WP Form Encryptor Settings',
            'WP Form Encryptor',
            'manage_options',
            'wp-form-encryptor',
            [$this, 'render'],
            'dashicons-lock'
        );
    }

    public function save_settings() {
        if( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check if the form was submitted
        if (isset($_POST['wp_form_encryptor_field_names'])) {
            // Save the field names in the options table
            update_option('wp_form_encryptor_field_names', $_POST['wp_form_encryptor_field_names']);
        }
    }

    public function render()
    {
        $generate_keys_url = add_query_arg(
            'wp_form_encryptor_action', 'generate_keys', admin_url( 'admin.php' )
        );
        ?>
        <div class="wrap">
            <h1>WP Form Encryptor Settings</h1>
            <a href="<?php echo esc_url( $generate_keys_url ); ?>" class="button button-primary">Generate Keys</a>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="private-key-file">Private key file</label></th>
                    <td><input type="file" name="private_key_file" id="private-key-file"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="text-to-decrypt">Text to decrypt</label></th>
                    <td>
                        <input type="text" name="text_to_decrypt" id="text-to-decrypt">
                        <div id="decrypted-text" style="padding: 5px"></div>
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Decrypt text"></p>
        </div>
        <?php
        // Get the field names from the options table
        $field_names = get_option('wp_form_encryptor_field_names', '');
        ?>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wp_form_encryptor_field_names"><?php _e('Field names to encrypt (separated by newlines)', 'wp-form-encryptor'); ?></label></th>
                    <td><textarea rows="5" name="wp_form_encryptor_field_names" id="wp_form_encryptor_field_names"><?php echo esc_textarea($field_names); ?></textarea></td>
                </tr>
            </table>
            <button type="submit" class="button button-primary"><?php _e('Save', 'wp-form-encryptor'); ?></button>
        </form>
        <?php
    }
}
