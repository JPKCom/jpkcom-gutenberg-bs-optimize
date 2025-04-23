<?php
/*
Plugin Name: JPKCom Gutenberg Bootstrap Optimizer
Plugin URI: https://github.com/JPKCom/jpkcom-gutenberg-bs-optimize
Description: Fixes and optimizes settings for Gutenberg and Bootstrap.
Version: 2.0.2
Author: Jean Pierre Kolb <jpk@jpkc.com>
Author URI: https://www.jpkc.com
Contributors: JPKCom
Tags: Bootstrap, CSS, Optimize, Editor, Gutenberg
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 2.0.2
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
GitHub Plugin URI: JPKCom/jpkcom-gutenberg-bs-optimize
Primary Branch: main
*/

if ( ! defined( constant_name: 'WPINC' ) ) {
	die;
}

if ( ! function_exists( function: 'jpkcom_bs_deregister_plugin_admin_styles' ) ) {


  function jpkcom_bs_deregister_plugin_admin_styles(): void {

    // Check if the specific plugin's stylesheet is enqueued in the admin area
    if ( wp_style_is( 'areoi-bootstrap', 'enqueued' ) ) {

        // Deregister the plugin's stylesheet in the admin area
        wp_deregister_style( 'areoi-bootstrap' );

    }

  }

}

add_action( 'admin_enqueue_scripts', 'jpkcom_bs_deregister_plugin_admin_styles', 100 );
