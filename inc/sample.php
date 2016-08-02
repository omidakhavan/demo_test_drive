<?php
/**
 * @link              http://webnus.biz
 * @since             1.0.0
 * @package           webnus demo
 * */

require_once plugin_dir_path(__FILE__) . 'class.php' ; 
if ( !class_exists('Wed_Setting' ) ):
class Wed_Setting {
    private $settings_api;
    function __construct() {
        $this->settings_api = new Wed_Setting_Api;
        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }
    function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
    }
    function admin_menu() {
        add_menu_page( 'Webnus Demo', 'Webnus Demo', 'delete_posts', 'webnus-demo', array($this, 'plugin_page'),'dashicons-welcome-view-site',79
);
    }
    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'general_tab',
                'title' => __( 'General Settings', 'avla-maintenance' )
            ),
        );
        return $sections;
    }
    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'general_tab' => array(
                array(
                    'name'    => 'wed_active',
                    'label'   => __( 'Activation', 'webnus_demo' ),
                    'type'    => 'radio',
                    'options' => array(
                        'yes' => 'Yes',
                        'no'  => 'No'
                    )
                ),
                array(
                    'name'              => 'wed_user_name',
                    'label'             => __( 'User Name', 'webnus_demo' ),
                    'desc'              => __( 'Enter User Name', 'webnus_demo' ),
                    'type'              => 'text',
                    'default'           => ''
                ),
                array(
                    'name'              => 'wed_password',
                    'label'             => __( 'password', 'webnus_demo' ),
                    'desc'              => __( 'Enter password', 'webnus_demo' ),
                    'type'              => 'password'
                ),
                array(
                    'name'              => 'wed_mail',
                    'label'             => __( 'Email', 'webnus_demo' ),
                    'desc'              => __( 'Enter mail', 'webnus_demo' ),
                    'type'              => 'text',
                    'default'           => ''
                ),
            array(
                    'name'              => 'loginpageid',
                    'label'             => __( 'Login Page Id', 'webnus_demo' ),
                    'desc'              => __( 'Insert Login Page Id', 'webnus_demo' ),
                    'type'              => 'text',
                    'default'           => ''
                )
            )
        );
        return $settings_fields;
    }
    function plugin_page() {
        echo '<div class="wrap">';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';
    }
    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }
        return $pages_options;
    }
}
endif;