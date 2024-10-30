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
 * @class		Indexy_Glossary_Index_Widget
 * @brief		The glossary index widget.
 *
 * The glossary index widget features a compact, scrollable list of glossary pages, organized by their starting letter,
 * in the sidebar (or other widget area) of your website.
 */
class Indexy_Glossary_Index_Widget extends WP_Widget
{
	/*!
	 * @brief		Object constructor.
	 */
	public function __construct()
	{
		$widget_options = array
		(
			'classname'		=> __CLASS__,
			'description'	=> __( 'A scrollable index of glossary articles.', 'indexy' )
		);

		$control_options = array
		(
			'id-base'		=> 'indexy-glossary-index-widget'
		);

		parent::__construct( 'indexy-glossary-index', __( 'Indexy: Glossary Index', 'indexy' ), $widget_options, $control_options );
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
		// Fetch input data.
		$terms = Indexy::get_all_terms();
		$index = Indexy::get_index($instance['ignore_synonyms']);

		// Do nothing if there are no glossary terms.
		if( sizeof($terms) == 0 )
		{
			return;
		}

		// Enqueue the JavaScript to make the widget work.
		wp_enqueue_script
		(
			'indexy-glossary-index-widget',
			plugins_url( '/js/indexy_glossary_index_widget.js', INDEXY_PLUGIN_FILE ),
			array('jquery')
		);

		// Generate the widget.
		$output  = '';
		$output .= $args['before_widget'];

		// Show a title if we have one.
		if( !empty($instance['title']) )
		{
			$output .= $args['before_title'];
			$output .= apply_filters( 'widget_title', $instance['title'] );
			$output .= $args['after_title'];
		}

		// Are we currently displaying a single glossary page?
		$current_term_id = null;
		if( is_single() && get_post_type() == 'glossary' )
		{
			global $post;
			if( !empty($post) )
			{
				$current_term_id = $post->ID;
			}
		}

		// Put the whole thing in a container element for JavaScript ease.
		$output .= "\n<div class=\"glossary-index-widget\">\n";

		// Generate the widget's header block.
		$output .= "\t<div class=\"widget-header\">\n";
		$output .= "\t\t<div class=\"column-arrow\" data-direction=\"left\">&laquo;</div>\n";
		$output .= "\t\t<div class=\"column-currentletter\"></div>\n";
		$output .= "\t\t<div class=\"column-arrow\" data-direction=\"right\">&raquo;</div>\n";
		$output .= "\t\t<div style=\"clear: both;\"></div>\n";
		$output .= "\t</div>\n";


		// Generate the blocks with terms for each starting letter.
		foreach( $index as $index_start => $index_terms )
		{
			$output .= "\t<ul class=\"widget-letter-block\" data-starting-letter=\"" . strtolower($index_start). "\">\n";

			foreach( $index_terms as $term )
			{
				$term_info = $terms[$term];
				$current = ( $current_term_id == $term_info->page_id && is_null($term_info->synonym_for) ? " class=\"current-page\"" : "" );
				$output .= "\t\t<li><a href=\"" . get_permalink($term_info->page_id) . "\"{$current}>";
				$output .= $term_info->term . "</a></li>\n";
			}

			$output .= "\t</ul>\n";
		}

		// Generate the list of starting letters.
		$output .= "\t<ul class=\"widget-letter-index\">\n";
		foreach( $index as $index_start => $index_terms )
		{
			$output .= "\t\t<li>" . $index_start . "</li>\n";
		}
		$output .= "\t</ul>\n";

		$output .= "</div>\n";
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
			'title'				=> '',
			'ignore_synonyms'	=> false
		);
		$instance = wp_parse_args( (array)$instance, $defaults );

		// Generate and display form HTML.
		$output = '';

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

		// Ignore synonyms field.
		$output .= "<p>\n";
		$output .= "<label for=\"" . $this->get_field_id('ignore_synonyms') . "\">";
		$output .= "<input type=\"checkbox\" id=\"";
		$output .= $this->get_field_id('ignore_synonyms');
		$output .= "\" name=\"";
		$output .= $this->get_field_name('ignore_synonyms');
		$output .= "\"";
		$output .= ( $instance['ignore_synonyms'] ? " checked=\"checked\"" : "" );
		$output .= "> " . __( 'Do not include synonyms', 'indexy' );
		$output .= "</p>\n";

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
		$instance['title']				= strip_tags($new_instance['title']);
		$instance['ignore_synonyms']	= (bool)$new_instance['ignore_synonyms'];

		return $instance;
	}
}

?>