<?php
/*
Plugin Name: HeroVideo (WPBakery)
Description: Élément WPBakery "Hero Video".
Version: 1.1.2
Author: BWC
Author URI: https://github.com/beworldcorp
Text Domain: wpb-bwc-herovideo
Domain Path: /languages
Requires at least: 6.0
Requires PHP: 8.0
GitHub Plugin URI: beworldcorp/wpb-bwc-herovideo
Primary Branch: main
*/

if (!defined('ABSPATH')) exit;

define('BWC_HEROVIDEO_VERSION', '1.1.2');
define('BWC_HEROVIDEO_FILE', __FILE__);
define('BWC_HEROVIDEO_PATH', plugin_dir_path(__FILE__));
define('BWC_HEROVIDEO_URL', plugin_dir_url(__FILE__));

require_once BWC_HEROVIDEO_PATH . 'includes/class-bwc-herovideo.php';

add_action('plugins_loaded', function () {
  load_plugin_textdomain('wpb-bwc-herovideo', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('init', function () {
  new BWC_Herovideo_Plugin();
});
