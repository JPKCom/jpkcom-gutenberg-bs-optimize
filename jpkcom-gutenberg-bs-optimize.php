<?php
/*
Plugin Name: JPKCom Gutenberg Bootstrap Optimizer
Description: Fixes and optimizes settings for Gutenberg and Bootstrap.
Version: 1.0.1
Author: Jean Pierre Kolb <jpk@jpkc.com>
Author URI: https://www.jpkc.com
GitHub Plugin URI: JPKCom/jpkcom-gutenberg-bs-optimize
Primary Branch: main
*/

function jpkcom_bs_deregister_plugin_admin_styles(): void {

  // Check if the specific plugin's stylesheet is enqueued in the admin area
  if ( wp_style_is( 'areoi-bootstrap', 'enqueued' ) ) {

      // Deregister the plugin's stylesheet in the admin area
      wp_deregister_style( 'areoi-bootstrap' );

  }
}

add_action( 'admin_enqueue_scripts', 'jpkcom_bs_deregister_plugin_admin_styles', 100 );
