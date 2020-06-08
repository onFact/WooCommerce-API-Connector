<?php
/**
 * Plugin Name: onFact connector
 * Plugin URI: http://www.onfact.be
 * Description: Connect your WooCommerce website to your onFact account
 * Version: 0.0.1
 * Author: Kevin Van Gyseghem
 * Author URI: http://www.infinwebs.be
 */
define( 'ONFACT__VERSION', '0.0.1' );
define( 'ONFACT__MINIMUM_WP_VERSION', '4.0' );
define( 'ONFACT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ONFACT__PLUGIN_DIR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' );
require_once( ONFACT__PLUGIN_DIR . 'class.onfact-documents.php' );
require_once( ONFACT__PLUGIN_DIR . 'class.onfact-settings.php' );
require_once( ONFACT__PLUGIN_DIR . 'class.onfact-stock.php' );
require_once( ONFACT__PLUGIN_DIR . 'class.onfact-views.php' );

add_action( 'init', array( 'Onfact_Stock', 'init' ) );
add_action( 'init', array( 'Onfact_Settings', 'init' ) );
add_action( 'init', array( 'Onfact_Documents', 'init' ) );

