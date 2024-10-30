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
 * @class		Indexy_Related_Posts_Widget
 * @brief		The related posts widget.
 *
 * The related posts widget displays a list posts that are related to a glossary term, when visiting the page for that
 * specific term. On any other page, it does nothing.
 */
class Indexy_Related_Posts_Widget extends WP_Widget
{
	/*!
	 * @brief		Object constructor.
	 */
	public function __construct()
	{
		$widget_options = array
		(
			'classname'		=> __CLASS__,
			'description'	=> __( 'Displays a list of related posts when viewing a glossary page.', 'indexy' )
		);

		$control_options = array
		(
			'id-base'		=> 'indexy-related-posts-widget'
		);

		parent::__construct( 'indexy-related-posts', __( 'Indexy: Related Posts', 'indexy' ), $widget_options, $control_options );
	}


	/*!
	 * @brief		Display the widget.
	 * @param		$args
	 *				An array containing general settings for how widgets are displayed.
	 * @param		$instance
	 *				An array containing the settings for this instance of the widget.
	 */
	public function widget( $args, $instance )
	{
		// Are we showing a single glossary page?
		if( !is_single() || get_post_type() != 'glossary' )
		{
			return;
		}

		// Generate the output.
		$output = '';
		$output .= $args['before_widget'];

		// Show a title if we have one.
		if( !empty($instance['title']) )
		{
			$output .= $args['before_title'];
			$output .= apply_filters( 'widget_title', $instance['title'] );
			$output .= $args['after_title'];
		}

		// Show the related posts. If the function fails, don't show the widget at all.
		$related_posts = Indexy::show_related_posts( null, $instance['method'], $instance['post_limit'], false );
		$output .= $related_posts;

		if( $related_posts === false )
		{
			return;
		}

		$output .= $args['after_widget'];
		print $output;
	}


	/*!
	 * @brief		Display the widget edit form for the admin section.
	 * @param		$instance
	 *				An array holding the current values of the widget's settings.
	 */
	public function form( $instance )
	{
		// Set up defaults.
		$defaults = array
		(
			'title'			=> '',
			'method'		=> 'tag',
			'post_limit'	=> 10
		);
		$instance = wp_parse_args( (array)$instance, $defaults );

		// Generate and display form HTML.
		$output  = '<p>';
		$output .= __( 'Please keep in mind that this widget is only displayed when a single glossary page is shown, and only if there are related posts to show.', 'indexy' );
		$output .= '</p>';

		// Title field.
		$output .= "<p>\n";
		$output .= "\t<label for=\"";
		$output .= $this->get_field_id('title');
		$output .= "\">" . __( 'Title:', 'indexy' ) . "</label>\n";
		$output .= "\t<input type=\"text\" class=\"widefat\" id=\"";
		$output .= $this->get_field_id('title');
		$output .= "\" name=\"";
		$output .= $this->get_field_name('title');
		$output .= "\" value=\"";
		$output .= $instance['title'];
		$output .= "\" />\n";
		$output .= "</p>\n";

		// Method field.
		$output .= "<p>\n";
		$output .= "\t<label for=\"";
		$output .= $this->get_field_id('method');
		$output .= "\">" . __( 'Search method:', 'indexy' ) . "</label>\n";
		$output .= "\t<select id=\"";
		$output .= $this->get_field_id('method');
		$output .= "\" name=\"";
		$output .= $this->get_field_name('method');
		$output .= "\" class=\"widefat\">\n";
		$output .= "\t\t<option value=\"tag\"";
		$output .= ( $instance['method'] == 'tag' ? ' selected="selected"' : "" );
		$output .= ">" . __( 'Posts tagged with term or synonyms', 'indexy' ) . "</option>\n";
		$output .= "\t\t<option value=\"search\"";
		$output .= ( $instance['method'] == 'search' ? ' selected="selected"' : "" );
		$output .= ">" . __( 'Keyword search', 'indexy' ) . "</option>\n";
		$output .= "\t</select>\n";
		$output .= "</p>\n";

		// Post limit field.
		$output .= "<p>\n";
		$output .= "\t<label for=\"";
		$output .= $this->get_field_id('post_limit');
		$output .= "\">" . __( 'Maximum number of posts to display:', 'indexy' ) . "</label>\n";
		$output .= "\t<input type=\"text\" class=\"widefat\" id=\"";
		$output .= $this->get_field_id('post_limit');
		$output .= "\" name=\"";
		$output .= $this->get_field_name('post_limit');
		$output .= "\" value=\"";
		$output .= $instance['post_limit'];
		$output .= "\" />\n";
		$output .= "</p>\n";

		$output .= "<p><b>" . __( 'Note:', 'indexy' ) . "</b> ";
		$output .= __( 'set to -1 to display all related posts.', 'indexy' ) . "</p>";

		print $output;
	}


	/*!
	 * @brief		Process the widget settings form.
	 * @param		$new_instance
	 *				The new settings, as submitted by the user.
	 * @param		$old_instance
	 *				The old settings.
	 * @returns		The sanitized widget settings.
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;

		// Filter the new values.
		$instance['title']		= strip_tags($new_instance['title']);
		$instance['method']		= ( $new_instance['method'] == 'search' ? 'search' : 'tag' );
		$instance['post_limit']	= ( (int)$new_instance['post_limit'] == 0 ? -1 : max( (int)$new_instance['post_limit'], -1 ) );

		return $instance;
	}
}

?>