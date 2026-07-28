<?php
/*
Plugin Name: JPKCom Gutenberg Bootstrap Optimizer
Plugin URI: https://github.com/JPKCom/jpkcom-gutenberg-bs-optimize
Description: Fixes and optimizes settings for Gutenberg and Bootstrap.
Version: 2.0.5
Author: Jean Pierre Kolb <jpk@jpkc.com>
Author URI: https://www.jpkc.com
Contributors: JPKCom
Tags: Bootstrap, CSS, Optimize, Editor, Gutenberg
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 2.0.5
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

declare(strict_types=1);

if ( ! defined( constant_name: 'WPINC' ) ) {
	die;
}


/**
 * Plugin Constants
 *
 * @since 2.0.3
 */
if ( ! defined( 'JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION' ) ) {
    define( 'JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION', '2.0.5' );
}


/**
 * Initialize Plugin Updater
 *
 * Loads and initializes the GitHub-based plugin updater with SHA256 checksum verification.
 *
 * @since 2.0.3
 *
 * @return void
 */
add_action( 'init', static function (): void {
    $updater_file = plugin_dir_path( __FILE__ ) . 'includes/class-plugin-updater.php';

    if ( file_exists( $updater_file ) ) {
        require_once $updater_file;

        if ( class_exists( 'JPKComGutenbergBsOptimizeGitUpdate\\JPKComGitPluginUpdater' ) ) {
            new \JPKComGutenbergBsOptimizeGitUpdate\JPKComGitPluginUpdater(
                plugin_file: __FILE__,
                current_version: JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION,
                manifest_url: 'https://jpkcom.github.io/jpkcom-gutenberg-bs-optimize/plugin_jpkcom-gutenberg-bs-optimize.json'
            );
        }
    }
}, 5 );

if ( ! function_exists( function: 'jpkcom_bs_deregister_plugin_admin_styles' ) ) {

  /**
   * Deregister the AREOI Bootstrap stylesheet in the admin area.
   *
   * Prevents the `areoi-bootstrap` stylesheet from interfering with the
   * Gutenberg editor UI when it is enqueued in wp-admin.
   *
   * @since 1.0.0
   *
   * @return void
   */
  function jpkcom_bs_deregister_plugin_admin_styles(): void {

    // Check if the specific plugin's stylesheet is enqueued in the admin area
    if ( wp_style_is( 'areoi-bootstrap', 'enqueued' ) ) {

        // Deregister the plugin's stylesheet in the admin area
        wp_deregister_style( 'areoi-bootstrap' );

    }

  }

}

add_action( 'admin_enqueue_scripts', 'jpkcom_bs_deregister_plugin_admin_styles', 100 );
