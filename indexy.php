<?php
/**
 * Plugin Name: Indexy
 * Plugin URI: http://www.damnleet.com/indexy
 * Description: Create a glossary of terms for your website, automatically highlighting terms in your posts and generating an index page.
 * Version: 1.0.2
 * Author: Jeroen Treurniet
 * Author URI: http://www.damnleet.com/
 * Text Domain: indexy
 * Domain Path: /languages
 * License: GPLv2
 */

/*
 * Indexy - a WordPress plugin to manage glossary pages.
 *
 * Visit the plugin's webpage at http://www.damnleet.com/indexy
 *
 * Copyright 2015 Jeroen Treurniet (contact@damnleet.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined('ABSPATH') )
{
	die();
}

// Define things.
define( 'INDEXY_PLUGIN_FILE', __FILE__ );
define( 'INDEXY_PLUGIN_PATH', dirname(INDEXY_PLUGIN_FILE) );

// Load files.
require_once INDEXY_PLUGIN_PATH . '/classes/Indexy.php';
require_once INDEXY_PLUGIN_PATH . '/classes/Indexy_Admin_Options.php';
require_once INDEXY_PLUGIN_PATH . '/classes/Indexy_Glossary_Index_Widget.php';
require_once INDEXY_PLUGIN_PATH . '/classes/Indexy_Related_Posts_Widget.php';
require_once INDEXY_PLUGIN_PATH . '/classes/Indexy_Shortcodes.php';
require_once INDEXY_PLUGIN_PATH . '/classes/TGM_Plugin_Activation.php';

// Register actions.
add_action( 'init',					array( 'Indexy', 'setup' )								);
add_action( 'plugins_loaded',		array( 'Indexy', 'setup_textdomain' )					);
add_action( 'pre_get_posts',		array( 'Indexy', 'add_glossary_posts_to_query' )		);
add_action( 'tgmpa_register',		array( 'Indexy', 'setup_required_plugins' )				);
add_action( 'widgets_init',			array( 'Indexy', 'setup_widgets' )						);
add_action( 'wp_enqueue_scripts',	array( 'Indexy', 'setup_scripts' )						);
add_action( 'wp_enqueue_scripts',	array( 'Indexy', 'setup_stylesheet' )					);
add_action( 'admin_menu', 			array( 'Indexy_Admin_Options', 'setup_admin_menu' ) 	);
add_action( 'admin_init', 			array( 'Indexy_Admin_Options', 'admin_page_init' )		);
add_action( 'wp_ajax_indexy_welcome_panel_close_action',
									array( 'Indexy_Admin_Options', 'close_welcome_panel' )	);

// Register activation/deactivation hooks.
register_activation_hook(   INDEXY_PLUGIN_FILE, array( 'Indexy', 'plugin_activate_hook' )	);
register_deactivation_hook( INDEXY_PLUGIN_FILE, array( 'Indexy', 'plugin_deactivate_hook' )	);
register_uninstall_hook(    INDEXY_PLUGIN_FILE, array( 'Indexy', 'plugin_uninstall_hook' )	);

// Register filters.
add_filter( 'rwmb_meta_boxes',		array( 'Indexy', 'setup_metabox' )						);
add_filter( 'plugin_action_links_' . plugin_basename(INDEXY_PLUGIN_FILE),
									array( 'Indexy', 'add_plugin_action_links')				);
add_filter( 'the_content',			array( 'Indexy', 'highlight_post' )						);

// Register shortcodes.
add_shortcode( 'indexy_excerpt',	array( 'Indexy_Shortcodes', 'excerpt' )					);
add_shortcode( 'indexy_highlight',	array( 'Indexy_Shortcodes', 'highlight' )				);
add_shortcode( 'indexy_ignore',		array( 'Indexy_Shortcodes', 'ignore' )					);
add_shortcode( 'indexy_index',		array( 'Indexy_Shortcodes', 'index' )					);

?>