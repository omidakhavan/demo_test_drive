<?php
/**
 * @link              http://webnus.biz
 * @since             1.0.0
 * @package           time table
 *
 * @wordpress-plugin
 * Plugin Name:       webnus demo 
 * Description:       plugin for webnus test drive.
 * Version:           1.0.0
 * Author:            webnus
 * Author URI:        http://webnus.biz/
 */


require_once(plugin_dir_path(__FILE__).'inc/sample.php' );
new Avma_Settings;
require_once(plugin_dir_path(__FILE__).'inc/function.php' );
public static function activate() {
    wp_schedule_event( time(), 'hourly', 'my_hourly_event' );
} 

public static function deactivate() {
    wp_clear_scheduled_hook('my_hourly_event');
} 

    // admin_init action hook operations
    // Checks for wordpress_reset post value and if there deletes all wp tables
    // and performs an install, populating the users previous password also
    function admin_init() {
        global $current_user;
        if ( $wordpress_reset && $wordpress_reset_confirm && $valid_nonce ) {
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

            $blogname = get_option( 'blogname' );
            $admin_email = get_option( 'admin_email' );
            $blog_public = get_option( 'blog_public' );

            if ( $current_user->user_login != 'admin' )
                $user = get_user_by( 'login', 'admin' );

            if ( empty( $user->user_level ) || $user->user_level < 10 )
                $user = $current_user;

            global $wpdb, $reactivate_wp_reset_additional;

            $prefix = str_replace( '_', '\_', $wpdb->prefix );
            $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
            foreach ( $tables as $table ) {
                $wpdb->query( "DROP TABLE $table" );
            }

            $result = wp_install( $blogname, $user->user_login, $user->user_email, $blog_public );
            extract( $result, EXTR_SKIP );

            $query = $wpdb->prepare( "UPDATE $wpdb->users SET user_pass = %s, user_activation_key = '' WHERE ID = %d", $user->user_pass, $user_id );
            $wpdb->query( $query );

            $get_user_meta = function_exists( 'get_user_meta' ) ? 'get_user_meta' : 'get_usermeta';
            $update_user_meta = function_exists( 'update_user_meta' ) ? 'update_user_meta' : 'update_usermeta';

            if ( $get_user_meta( $user_id, 'default_password_nag' ) )
                $update_user_meta( $user_id, 'default_password_nag', false );

            if ( $get_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' ) )
                $update_user_meta( $user_id, $wpdb->prefix . 'default_password_nag', false );

            if ( defined( 'REACTIVATE_WP_RESET' ) && REACTIVATE_WP_RESET === true )
                @activate_plugin( plugin_basename( __FILE__ ) );

            if ( ! empty( $reactivate_wp_reset_additional ) ) {
                foreach ( $reactivate_wp_reset_additional as $plugin ) {
                    $plugin = plugin_basename( $plugin );
                    if ( ! is_wp_error( validate_plugin( $plugin ) ) )
                        @activate_plugin( $plugin );
                }
            }

            wp_clear_auth_cookie();
            wp_set_auth_cookie( $user_id );

            wp_redirect( admin_url() . '?reset' );
            exit();
        }
    }

    // add_option_page callback operations
    // The settings page
    function admin_page() {
        global $current_user, $reactivate_wp_reset_additional;
        if ( isset( $_POST['wordpress_reset_confirm'] ) && $_POST['wordpress_reset_confirm'] != 'reset' )
            echo '<div class="error fade"><p><strong>Invalid confirmation word. Please type the word \'reset\' in the confirmation field.</strong></p></div>';
        elseif ( isset( $_POST['_wpnonce'] ) )
            echo '<div class="error fade"><p><strong>Invalid nonce. Please try again.</strong></p></div>';

        $missing = array();
        if ( ! empty( $reactivate_wp_reset_additional ) ) {
            foreach ( $reactivate_wp_reset_additional as $key => $plugin ) {
                if ( is_wp_error( validate_plugin( $plugin ) ) ) {
                    unset( $reactivate_wp_reset_additional[$key] );
                    $missing[] = $plugin;
                }
            }
        }

        $will_reactivate = ( defined( 'REACTIVATE_WP_RESET') && REACTIVATE_WP_RESET === true ) ? true : false;
    ?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"><br /></div>
        <h2>Reset</h2>
        <h3>Details about the reset</h3>
        <p><strong>After completing this reset you will taken to the dashboard.</strong></p>
        <?php $admin = get_user_by( 'login', 'admin' ); ?>
        <?php if ( ! isset( $admin->user_login ) || $admin->user_level < 10 ) : $user = $current_user; ?>
        <p>The 'admin' user does not exist. The user '<strong><?php echo esc_html( $user->user_login ); ?></strong>' will be recreated with its <strong>current password</strong> with user level 10.</p>
        <?php else : ?>
        <p>The '<strong>admin</strong>' user exists and will be recreated with its <strong>current password</strong>.</p>
        <?php endif; ?>
        <p>This plugin <strong>will<?php echo $will_reactivate ? '' : ' not';?> be automatically reactivated</strong> after the reset. <?php echo ! $will_reactivate ? "To have this plugin auto-reactivate add <span class='code'>define( 'REACTIVATE_WP_RESET', true );</span> to <span class='code'>wp-config.php</span>." : ''; ?></p>
        <?php if ( ! empty( $reactivate_wp_reset_additional ) ) : ?>
        <p>The following <strong>additional plugins will be reactivated</strong>:
            <ul style="list-style-type: disc;">
            <?php foreach ( $reactivate_wp_reset_additional as $plugin ) : $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin ); ?>
                <li style="margin: 5px 0 0 30px;"><strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong></li>
            <?php endforeach; unset( $reactivate_wp_reset_additional, $plugin, $plugin_data ); ?>
            </ul>
        </p>
        <?php endif; ?>
        <?php if ( ! empty( $missing ) ) : ?>
        <p>The following <strong>additional plugins are missing</strong> and cannot be reactivated:
            <ul style="list-style-type: disc;">
            <?php foreach ( $missing as $plugin ) : ?>
                <li style="margin: 5px 0 0 30px;"><strong><?php echo esc_html( $plugin ); ?></strong></li>
            <?php endforeach; unset( $missing, $plugin ); ?>
            </ul>
        </p>
        <?php endif; ?>
        <h3>Reset</h3>
        <p>Type '<strong>reset</strong>' in the confirmation field to confirm the reset and then click the reset button:</p>
        <form id="wordpress_reset_form" action="" method="post">
            <?php wp_nonce_field( 'wordpress_reset' ); ?>
            <input id="wordpress_reset" type="hidden" name="wordpress_reset" value="true" />
            <input id="wordpress_reset_confirm" type="text" name="wordpress_reset_confirm" value="" />
            <p class="submit">
                <input id="wordpress_reset_submit" style="width: 80px;" type="submit" name="Submit" class="button-primary" value="Reset" />
            </p>
        </form>
    </div>
    <?php
    }
}

// Instantiate the class
$WordPressReset = new WordPressReset();

// End if for is_admin
endif;