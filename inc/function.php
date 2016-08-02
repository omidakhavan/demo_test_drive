<?php
/**
 * @link              http://webnus.biz
 * @since             1.0.0
 * @package           webnus demo
 * */

	function wed_get_option( $option, $section, $default = '' ) {
		if ( empty( $option ) )
			return;
	    $options = get_option( $section );
	    if ( isset( $options[$option] ) ) {
	        return $options[$option];
	    }
	    return $default;
	}

	$wed_user_name  =    wed_get_option( 'wed_user_name','general_tab' );
	$wed_password   =    wed_get_option( 'wed_password','general_tab' );
	$wed_mail       =    wed_get_option( 'wed_mail','general_tab' );
	$loginpageid    =    wed_get_option( 'loginpageid','general_tab' );

  //activation
  add_action('wp', 'my_activation');
  function my_activation() {
    if ( !wp_next_scheduled( 'wed_hourly_event' ) ) {
      wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'wed_hourly_event');
    }
  }

  add_action( 'admin_init', 'wed_create_user' );
	function wed_create_user(){
		if ( !username_exists( $wed_user_name ) && email_exists( $wed_mail ) == false ) {

			$user_id = wp_create_user( $wed_user_name, $wed_password, $wed_mail ) ;
			
		}
	}
	add_action('wp', 'auto_login');
	function auto_login() {
	    if (!is_user_logged_in()
	    	&& is_page($loginpageid)) { 
	        $user = get_user_by('login', $wed_user_name);
	        $user_id = $user->ID;
	        wp_set_current_user($user_id, $wed_user_name);
	        wp_set_auth_cookie($user_id);
	        do_action('wp_login', $wed_user_name);
	        wp_redirect( admin_url().'edit.php?post_type=mec-events' );
	        exit;
	    } elseif(is_page($loginpageid)) {
	        wp_redirect( home_url() );
	        exit;
	    } else {}
	}

	add_action( 'admin_footer', 'wed_create_page' );
	function wed_create_page() {
		$post_id = -1;
		$author_id = 1;
		$slug = 'webnus-demo';
		if( null == get_page_by_title( $loginpageid ) ) {
			$post_id = wp_insert_post(
				array(
					'comment_status'	=>	'closed',
					'ping_status'		  =>	'closed',
					'post_author'		  =>	$author_id,
					'post_name'		    =>	$slug,
					'post_title'	   	=>	$loginpageid,
					'post_status'	   	=>	'publish',
					'post_type'		    =>	'page'
				)
			);
		} else {
	    		$post_id = -2;
		} 
	} 

  //Do this hourly
  add_action('wed_hourly_event', 'wed_do_this_hourly');
  function wed_do_this_hourly() {

    global $current_user;
    if (  wed_get_option( 'wed_active','general_tab' ) == 'yes' ) {
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

        wp_redirect( admin_url() );
        exit();
    }
  }

