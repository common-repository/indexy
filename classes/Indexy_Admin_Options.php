<?php
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

/*!
 * @class		Indexy_Admin_Options
 * @brief		The plugin's options screen in the administration section.
 */
abstract class Indexy_Admin_Options
{
	/*!
	 * @brief		Object constructor.
	 */
	public function __construct()
	{
		// Register the hooks for this.
		add_action( 'admin_menu', array( __CLASS__, 'setup_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_page_init' ) );
	}


	/*!
	 * @brief		Register and add settings.
	 */
	public static function admin_page_init()
	{
		// Create the settings section for our page.
		add_settings_section
		(
			'indexy_settings',
			__( 'Indexy Settings', 'indexy' ),
			null,
			'indexy' 
		);

		// Option: indexy_highlight_post_types
		register_setting
		(
			'indexy_settings',
			'indexy_highlight_post_types',
			array( __CLASS__, 'sanitize_highlight_post_types_option' )
		);
		add_settings_field
		(
			'indexy_highlight_post_types',								// Option ID
			__( 'Posts to highlight', 'indexy' ),						// Option title
			array( __CLASS__, 'render_highlight_post_types_option' ),	// Rendering callback
			'indexy',													// Page
			'indexy_settings'											// Section
		);

		// Option: indexy_highlight_style
		register_setting
		(
			'indexy_settings',
			'indexy_highlight_style',
			array( __CLASS__, 'sanitize_highlight_style_option' )
		);
		add_settings_field
		(
			'indexy_highlight_style',
			__( 'Highlighting style', 'indexy' ),
			array( __CLASS__, 'render_highlight_style_option' ),
			'indexy',
			'indexy_settings'
		);

		// Option: indexy_highlight_repeat
		register_setting
		(
			'indexy_settings',
			'indexy_highlight_repeat',
			array( __CLASS__, 'sanitize_highlight_repeat_option' )
		);
		add_settings_field
		(
			'indexy_highlight_repeat',
			__( 'Repeated highlighting', 'indexy' ),
			array( __CLASS__, 'render_highlight_repeat_option' ),
			'indexy',
			'indexy_settings'
		);

		// Option: indexy_include_in_main_loop
		register_setting
		(
			'indexy_settings',
			'indexy_include_in_main_loop',
			array( __CLASS__, 'sanitize_yes_or_no_option' )
		);
		add_settings_field
		(
			'indexy_include_in_main_loop',
			__( 'Show glossary articles as posts', 'indexy' ),
			array( __CLASS__, 'render_include_in_main_loop_option' ),
			'indexy',
			'indexy_settings'
		);

		// Option: indexy_include_css_file
		register_setting
		(
			'indexy_settings',
			'indexy_include_css_file',
			array( __CLASS__, 'sanitize_yes_or_no_option' )
		);
		add_settings_field
		(
			'indexy_include_css_file',
			__( 'Include CSS file', 'indexy' ),
			array( __CLASS__, 'render_include_css_file_option' ),
			'indexy',
			'indexy_settings'
		);
	}


	/*!
	 * @brief		Output the HTML for the options page.
	 */
	public static function admin_page_output()
	{

		echo "<div class=\"wrap\">\n";
		echo "\t<h2>Indexy</h2>\n";

		// The welcome panel.
		if( get_user_meta( get_current_user_id(), 'indexy_welcome_panel', true ) != 'hide' )
		{
			echo Indexy_Admin_Options::get_welcome_panel();
		}
	

		echo "\t<form method=\"post\" action=\"options.php\">\n";

		settings_fields('indexy_settings');
		do_settings_sections('indexy');
		submit_button();

		echo "\t</form>\n";
		echo "</div>\n";
	}


	/*!
	 * @brief		Close the welcome panel on the settings page.
	 */
	public static function close_welcome_panel()
	{
		check_ajax_referer( 'indexy-welcome-panel-nonce', 'welcomepanelnonce_indexy' );
		update_user_meta( get_current_user_id(), 'indexy_welcome_panel', 'hide' );
		wp_die(1);
	}


	/*!
	 * @brief		Get the HTML for the welcome/donation panel.
	 * @returns		The HTML code for the welcome/donation panel, as string.
	 */
	public static function get_welcome_panel()
	{
		$panel  = "\t<div id=\"welcome-panel\" class=\"welcome-panel\">\n";
		$panel .= "\t\t" . wp_nonce_field( 'indexy-welcome-panel-nonce', 'welcomepanelnonce_indexy', false, false ) . "\n";
		$panel .= "\t\t<a class=\"welcome-panel-close\" href=\"javascript:;\">" . __( 'Dismiss', 'indexy' ) . "</a>\n";
		$panel .= "\t\t<div class=\"welcome-panel-content\">\n";
		$panel .= "\t\t\t<div class=\"welcome-panel-column\">\n";
		$panel .= "\t\t\t\t<h4>" . __( 'Support Indexy', 'indexy' ) . "</h4>\n";
		$panel .= "\t\t\t\t<p class=\"message\" style=\"width: 80%;\">" . __( 'Developing Indexy takes time and effort. If you enjoy using the plugin and find it useful, please consider making a donation to support its development.', 'indexy' ) . "</p>\n";
		$panel .= "\t\t\t\t<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" id=\"indexy-donate-form\" target=\"_blank\">\n";
		$panel .= "\t\t\t\t\t<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\" />\n";
		$panel .= "\t\t\t\t\t<input type=\"hidden\" name=\"hosted_button_id\" value=\"6PUCCX9W5F9QA\" />\n";
		$panel .= "\t\t\t\t\t<p><a href=\"javascript:;\" class=\"button button-primary\" onclick=\"document.getElementById('indexy-donate-form').submit();\">" . __( 'Donate', 'indexy' ) . "</a></p>";
		$panel .= "\t\t\t\t</form>";
		$panel .= "\t\t\t</div>\n";
		$panel .= "\t\t\t<div class=\"welcome-panel-column\">\n";
		$panel .= "\t\t\t\t<h4>" . __( 'Get Started', 'indexy' ) . "</h4>\n";
		$panel .= "\t\t\t\t<ul>\n";
		$panel .= "\t\t\t\t\t<li><a href=\"" . get_admin_url( null, 'post-new.php?post_type=glossary' ) . "\">" . __( 'Create Glossary Page', 'indexy' ) . "</a></li>\n";
		$panel .= "\t\t\t\t\t<li><a href=\"" . get_admin_url( null, 'edit.php?post_type=glossary' ) . "\">" . __( 'View Glossary Pages', 'indexy' ) . "</a></li>\n";
		$panel .= "\t\t\t\t\t<li><a href=\"" . get_admin_url( null, 'options-general.php?page=indexy_settings_main' ) . "\">" . __( 'Manage Settings', 'indexy' ) . "</a></li>\n";
		$panel .= "\t\t\t\t</ul>\n";
		$panel .= "\t\t\t</div>\n";
		$panel .= "\t\t\t<div class=\"welcome-panel-column\">\n";
		$panel .= "\t\t\t\t<h4>" . __( 'How To...', 'indexy' ) . "</h4>\n";
		$panel .= "\t\t\t\t<ul>\n";
		$panel .= "\t\t\t\t\t<li><a href=\"http://www.damnleet.com/indexy/using-widgets\" target=\"_blank\">" . __( 'Use Widgets', 'indexy' ) . "</a></li>\n";
		$panel .= "\t\t\t\t\t<li><a href=\"http://www.damnleet.com/indexy/using-shortcodes\" target=\"_blank\">" . __( 'Use Shortcodes', 'indexy' ) . "</a></li>\n";
		$panel .= "\t\t\t\t\t<li><a href=\"http://www.damnleet.com/indexy/customizing-indexy\" target=\"_blank\">" . __( 'Customize Indexy', 'indexy' ) . "</a></li>\n";
		$panel .= "\t\t\t\t</ul>\n";
		$panel .= "\t\t\t\t</div>\n";
		$panel .= "\t\t</div>\n";
		$panel .= "\t</div>\n";

		return $panel;
	}


	/*!
	 * @brief		Output the HTML for the indexy_highlight_post_types option.
	 */
	public static function render_highlight_post_types_option()
	{
		// Get a list of post types and a list of post types currently enabled.
		$post_types			= get_post_types( array(), 'objects' );
		$enabled_post_types	= explode( ',', get_site_option('indexy_highlight_post_types'));

		// Spit out a list of checkboxes.
		foreach( $post_types as $post_type_slug => $post_type )
		{
			// Omit some of the built-in post types for which highlighting does not make any sense.
			if( $post_type_slug == 'revision' || $post_type_slug == 'nav_menu_item' )
			{
				continue;
			}

			$is_enabled = in_array( $post_type_slug, $enabled_post_types );

			// Let's make a checkbox.
			echo "\t\t<input type=\"checkbox\" name=\"indexy_highlight_post_types[{$post_type_slug}]\" id=\"indexy_highlight_post_types[{$post_type_slug}]\" ";
			echo ( $is_enabled ? 'checked="checked" ' : '' );
			echo "/> <label for=\"indexy_highlight_post_types[{$post_type_slug}]\">";
			echo $post_type->labels->name;
			echo "</label><br />\n";
		}

		echo "\t\t<p class=\"description\">";
		echo __( 'Select in which types of posts you\'d like glossary terms to be highlighted automatically.', 'indexy' );
		echo "</p>\n";
	}


	/*!
	 * @brief		Output the HTML for the indexy_highlight_repeat option.
	 */
	public static function render_highlight_repeat_option()
	{
		$current = get_site_option('indexy_highlight_repeat');
		$current = explode( ',', $current );
		$repeat_count = (int)$current[0];
		$repeat_skip  = (int)$current[1];

		echo "\t\t<input type=\"radio\" name=\"indexy_highlight_repeat[enable]\" id=\"indexy_highlight_repeat_off\" value=\"off\" ";
		echo ( $repeat_count == 0 ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_highlight_repeat_off\">" . __( 'Highlight each term once', 'indexy' ) . "</label><br />\n";
		echo "\t\t<input type=\"radio\" name=\"indexy_highlight_repeat[enable]\" id=\"indexy_highlight_repeat_on\" value=\"on\" ";
		echo ( $repeat_count > 0 ? 'checked="checked"' : '' );
		echo "/> <label for=\"indexy_highlight_repeat_on\">" . __( 'Highlight each term up to', 'indexy' ) . " </label>\n";
		echo "\t\t<input type=\"number\" min=\"2\" max=\"100\" name=\"indexy_highlight_repeat[count]\" id=\"indexy_highlight_repeat_count\" ";
		echo ( $repeat_count <= 0 ? 'disabled="disabled" ' : '' );
		echo "value=\"" . max( 2, $repeat_count ) . "\" style=\"width: 8ch;\" /> ";
		echo "<label for=\"indexy_highlight_repeat_count\">" . __( 'time(s)', 'indexy' ) . "</label><br />\n";
		echo "\t\t<span style=\"margin-left: 25px;\">\n";
		echo "\t\t\t<input type=\"checkbox\" name=\"indexy_highlight_repeat[enable_skip]\" id=\"indexy_highlight_repeat_enable_skip\" ";
		echo ( $repeat_skip > 0 ? 'checked="checked" ' : '' );
		echo ( $repeat_count <= 0 ? 'disabled="disabled" ' : '' );
		echo "/> <label for=\"indexy_highlight_repeat_enable_skip\">" . __( 'Skip every', 'indexy' ) . " </label> ";
		echo "\t\t</span>\n";
		echo "\t\t<input type=\"number\" min=\"2\" max=\"100\" name=\"indexy_highlight_repeat[skip]\" id=\"indexy_highlight_repeat_skip\" ";
		echo "value=\"" . max( 2, $repeat_skip ) . "\" style=\"width: 8ch;\" ";
		echo ( $repeat_count <= 0 || $repeat_skip == 0 ? 'disabled="disabled" ' : '' );
		echo " /> <label for=\"indexy_highlight_repeat_skip\">" . __( 'th occurrence', 'indexy' ) . "</label><br />";
		echo "\t\t<input type=\"radio\" name=\"indexy_highlight_repeat[enable]\" id=\"indexy_highlight_repeat_all\" value=\"all\" ";
		echo ( $repeat_count == -1 ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_highlight_repeat_all\">" . __( 'Highlight all occurrences of each term', 'indexy' ) . "</label><br />\n";

		echo "\t\t<p class=\"description\">" . __( 'Choose how you would like to handle repeated highlighting of terms.', 'indexy' ) . "</p>\n";

		// Add some JS that makes the form behave a little more intuitively for the user.
		wp_enqueue_script("jquery");
		wp_enqueue_script( "indexy_admin_options", plugins_url( 'js/indexy_admin_options.js', INDEXY_PLUGIN_FILE ), array("jquery") );
	}


	/*!
	 * @brief		Output the HTML for the indexy_highlight_style option.
	 */
	public static function render_highlight_style_option()
	{
		$current = get_site_option('indexy_highlight_style');

		echo "\t\t<input type=\"radio\" name=\"indexy_highlight_style\" id=\"indexy_highlight_style_popup\" value=\"popup\" ";
		echo ( $current == 'popup' ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_highlight_style_popup\">Popup with excerpt of the glossary page</label><br />\n";
		echo "\t\t<input type=\"radio\" name=\"indexy_highlight_style\" id=\"indexy_highlight_style_simple\" value=\"simple\" ";
		echo ( $current == 'simple' ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_highlight_style_simple\">Just a link to the glossary page</label><br />\n";
		echo "\t\t<p class=\"description\">How would you like glossary terms to be highlighted?</p>\n";
	}


	/*!
	 * @brief		Output the HTML for the indexy_include_css_file option.
	 */
	public static function render_include_css_file_option()
	{
		$current = get_site_option('indexy_include_css_file');

		echo "\t\t<input type=\"radio\" name=\"indexy_include_css_file\" id=\"indexy_include_css_file_yes\" value=\"yes\" ";
		echo ( $current == 'yes' ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_include_css_file_yes\">" . __( 'Yes', 'indexy' ) . "</label><br />\n";
		echo "\t\t<input type=\"radio\" name=\"indexy_include_css_file\" id=\"indexy_include_css_file_no\" value=\"no\" ";
		echo ( $current != 'yes' ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_include_css_file_no\">" . __( 'No', 'indexy' ) . "</label><br />\n";
		echo "\t\t<p class=\"description\">" . __( 'Disable this if your theme\'s style sheet already includes custom CSS to style the highlights and other glossary-related elements.', 'indexy' ) . "</p>\n";
	}


	/*!
	 * @brief		Output the HTML for the indexy_include_in_main_loop option.
	 */
	public static function render_include_in_main_loop_option()
	{
		$current = get_site_option('indexy_include_in_main_loop');

		echo "\t\t<input type=\"radio\" name=\"indexy_include_in_main_loop\" id=\"indexy_include_in_main_loop_yes\" value=\"yes\" ";
		echo ( $current == 'yes' ? 'checked="checked" ' : '' );
		echo "/> <label for=\"glossary_include_in_main_loop_yes\">" . __( 'Yes', 'indexy' ) . "</label><br />\n";
		echo "\t\t<input type=\"radio\" name=\"indexy_include_in_main_loop\" id=\"indexy_include_in_main_loop_no\" value=\"no\" ";
		echo ( $current != 'yes' ? 'checked="checked" ' : '' );
		echo "/> <label for=\"indexy_include_in_main_loop_no\">" . __( 'No', 'indexy' ) . "</label><br />\n";
		echo "\t\t<p class=\"description\">" . __( 'If enabled, glossary posts will be displayed on the front page as if they were a regular post when they\'re first created.', 'indexy' ) . "</p>\n";
	}


	/*!
	 * @brief		Validate and sanitize the glossary_highlight_post_types option.
	 * @param		$value
	 *				The user-submitted value.
	 * @returns		The value passed in @c $value, but sanitized.
	 */
	public static function sanitize_highlight_post_types_option( $value )
	{
		return implode( ',', array_keys($value) );
	}


	/*!
	 * @brief		Validate and sanitize the glossary_highlight_repeat option.
	 * @param		$value
	 *				The user-submitted value.
	 * @returns		The value passed in @c $value, but sanitized.
	 */
	public static function sanitize_highlight_repeat_option( $value )
	{
		$current = get_site_option('indexy_highlight_repeat');
		$current = explode( ',', $current );
		$repeat_count = (int)$current[0];
		$repeat_skip  = (int)$current[1];

		switch($value['enable'])
		{
			case 'on':
				$repeat_count = min( 100, max( 2, intval($value['count']) ) );

				if( !empty($value['enable_skip']) )
				{
					$repeat_skip  = min( 100, max( 2, intval($value['skip']) ) );	
				}
				else
				{
					$repeat_skip = 0;
				}
				//var_dump($repeat_skip);
				return $repeat_count . ',' . $repeat_skip;
			case 'off':
				return '0,' . $repeat_skip;
			case 'all':
				return '-1,' . $repeat_skip;
			default:
				return '1,0';
		}
	}


	/*!
	 * @brief		Validate and sanitize the glossary_highlight_style option.
	 * @param		$value
	 *				The user-submitted value.
	 * @returns		The value passed in @c $value, but sanitized.
	 */
	public static function sanitize_highlight_style_option( $value )
	{
		if( $value == 'simple' || $value == 'popup' )
		{
			return $value;
		}
		else
		{
			return 'popup';
		}
	}


	/*!
	 * @brief		Validate and sanitize any option that expects the value to be 'yes' or 'no'.
	 * @param		$value
	 *				The user-submitted value.
	 * @returns		The value passed in @c $value, but sanitized.
	 */
	public static function sanitize_yes_or_no_option( $value )
	{
		if( $value == 'yes' || $value == 'no' )
		{
			return $value;
		}
		else
		{
			return 'yes';
		}
	}


	/*!
	 * @brief		Register the options page with WordPress.
	 */
	public static function setup_admin_menu()
	{
		// Primary options page.
		add_options_page
		(
			__( 'Indexy Settings', 'indexy' ),
			__( 'Indexy', 'indexy' ),
			'manage_options',
			'indexy_settings_main',
			array( __CLASS__, 'admin_page_output' )
		);

		// Secondary link to the settings page in the 'glossary pages' menu.
		add_submenu_page
		(
			'edit.php?post_type=glossary',
			__( 'Indexy Settings', 'indexy' ),
			__( 'Indexy Settings', 'indexy' ),
			'manage_options',
			'indexy_settings_secondary',
			array( __CLASS__, 'admin_page_output' )
		);
	}
}

?>