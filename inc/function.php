<?php

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


	add_action( 'my_hourly_event',  'update_db_hourly' );
	public function update_db_hourly() {

	}

