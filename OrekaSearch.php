<?php
/**
* Plugin Name: Oreka Search
* Plugin URI: https://oreka.dolphinai.ir/
* Description: Bring the power of Oreka search service to WooCommerce.
* Version: 1.6.2
* Author: DolphinAI
* Author URI: https://www.dolphinai.ir/
**/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

// Currently plugin version.
define( 'Oreka Search', '1.6.2' );

// Include plugin activation functions
function oreka_search_plugin_install() { require_once plugin_dir_path( __FILE__ ) . 'admin/plugin_install.php'; Oreka_Search_Activator::activate(); }
register_activation_hook( __FILE__, 'oreka_search_plugin_install' );

// Include plugin deactivation functions
function oreka_search_plugin_uninstall() { require_once plugin_dir_path( __FILE__ ) . 'admin/plugin_unistall.php'; Oreka_Search_Deactivator::deactivate(); }
register_deactivation_hook( __FILE__, 'oreka_search_plugin_uninstall' );

// Include main functions
require_once plugin_dir_path( __FILE__ ) . 'search/search.php'; $OrekaSearch = new OrekaSearch;

// Replace form
require_once plugin_dir_path( __FILE__ ) . 'search/forms.php'; $OrekaSearchForm = new OrekaSearchForm;

// Include ingestion functions
require_once plugin_dir_path( __FILE__ ) . 'search/ingestion.php'; $OrekaSearchIngestion = new OrekaSearchIngestion;

// Include shortcode
require_once plugin_dir_path( __FILE__ ) . 'search/shortcode.php'; $OrekaSearchShortcode = new OrekaSearchShortcode;

// Include widget
require_once plugin_dir_path( __FILE__ ) . 'search/widget.php'; add_action( 'widgets_init', function(){ register_widget( 'OrekaSearchWidget' ); });

// Include sort
require_once plugin_dir_path( __FILE__ ) . 'search/sort.php'; add_action( 'widgets_init', function(){ register_widget( 'OrekaSearchSort' ); });

// Include admin functions
if ( is_admin() ) { require_once plugin_dir_path( __FILE__ ) . 'admin/admin.php'; $OrekaSearchAdmin = new OrekaSearchAdmin; }