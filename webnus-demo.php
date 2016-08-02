<?php
/**
 * @link              http://webnus.biz
 * @since             1.0.0
 * @package           webnus demo
 *
 * @wordpress-plugin
 * Plugin Name:       webnus test drive for demo testing 
 * Description:       plugin for webnus test drive.
 * Version:           1.0.0
 * Author:            webnus
 * Author URI:        http://webnus.biz/
 */

  if ( ! defined( 'ABSPATH' ) ) exit; 
  require_once(plugin_dir_path(__FILE__).'inc/sample.php' );
  new Wed_Setting;
  require_once(plugin_dir_path(__FILE__).'inc/function.php' );

