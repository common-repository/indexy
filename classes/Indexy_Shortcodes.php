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
 * @class		Indexy_Shortcodes
 * @brief		Container for all functions that act as shortcodes for Indexy.
 */
abstract class Indexy_Shortcodes
{
	/*!
	 * @brief		Include the excerpt of a glossary term as part of a post.
	 * @param		$attributes
	 *				Shortcode attributes. See below.
	 * @param		$content
	 *				The content of the shortcode. Ignored.
	 * @returns		The excerpt for the given term.
	 *
	 * Possible shortcode attributes:
	 * * @c term: the term to get the excerpt for. Required; if omitted, no excerpt is included. If the term does not
	 *   exist, nothing is included either.
	 * * @c length: an integer specifying the maximum number of words to include in the excerpt. Default is 55. If the
	 *   provided value is zero, or a negative value, the entire glossary article is included.
	 */
	public static function excerpt( $attributes, $content )
	{
		// Handle $attributes.
		$attributes = shortcode_atts( array( 'term' => '', 'length' => null ), $attributes );

		// Don't do anything if we don't have a term attribute.
		if( empty($attributes['term']) )
		{
			return;
		}

		// Don't do anything if that's not a valid term.
		$term = Indexy::get_term($attributes['term']);

		if( $term === false )
		{
			return;
		}

		// Query for the full glossary article.
		$query  = new WP_Query("post_type=glossary&p={$term->page_id}");
		$result = $query->get_posts();

		if( sizeof($result) != 1 )
		{
			return;
		}

		// Figure out how long to make the excerpt.
		$default_length = apply_filters( 'indexy_excerpt_length', 55 );
		$excerpt_length = ( is_null($attributes['length']) ? $default_length : intval($attributes['length']) );

		// Generate and return the excerpt.
		$result = $result[0]->post_content;
		$result = strip_shortcodes($result);
		$result = apply_filters( 'the_content', $result );

		if( $excerpt_length > 0 )
		{
			$more = apply_filters( 'indexy_excerpt_more', ' [&hellip;]' );
			$more = Indexy::highlight_term( $more, $term->term );
			$result = wp_trim_words( $result, $excerpt_length, $more );
		}

		$result = apply_filters( 'indexy_excerpt_text', $result );

		return $result;
	}


	/*!
	 * @brief		Explicitly highlight a glossary term.
	 * @param		$attributes
	 *				Shortcode attributes. See below.
	 * @param		$content
	 *				The text to highlight.
	 * @returns		The HTML code for the highlighted term.
	 *
	 * Possible shortcode attributes:
	 * * @c term: the term to highlight the text with. Required; if omitted, no highlighting is done. If the term does
	 *   not exist, no highlighting is done either; the text will be displayed as-is.
	 */
	public static function highlight( $attributes, $content )
	{
		// Handle $attributes.
		$attributes = shortcode_atts( array( 'term' => '' ), $attributes );

		// Don't do anything if we don't have a term attribute.
		if( empty($attributes['term']) )
		{
			return $content;
		}

		// Do magic, yay.
		return Indexy::highlight_term( $content, $attributes['term'] );
	}


	/*!
	 * @brief		Handle the glossary_ignore shortcode.
	 * @param		$attributes
	 *				Shortcode attributes. Unused for this shortcode.
	 * @param		$content
	 *				Shortcode content.
	 * @returns		The value of $content.
	 *
	 * This is not really a shortcode that does anything; it just returns whatever is inside it. However, this is needed
	 * in order for WordPress to recognize the shortcode and strip it from the output.
	 */
	public static function ignore( $attributes, $content )
	{
		return $content;
	}


	/*!
	 * @brief		Generate HTML for a glossary index.
	 * @param		$attributes
	 *				Shortcode attributes. See below.
	 * @param		$content
	 *				Shortcode content. Currently ignored.
	 * @returns		The glossary index HTML.
	 *
	 * Possible shortcode attributes:
	 * * @c ignore_synonyms: if set, synonyms are not included in the generated index.
	 */
	public static function index( $attributes, $content )
	{
		// Get a list of all terms.
		$terms = Indexy::get_all_terms();

		// Are we ignoring synonyms?
		$ignore_synonyms = false;

		if
		(
			   isset($attributes['ignore_synonyms'])
			&& $attributes['ignore_synonyms'] == true
			&& $attributes['ignore_synonyms'] != "no"
			&& $attributes['ignore_synonyms'] != "false"
		)
		{
			$ignore_synonyms = true;
		}

		// Fetch our input data.
		$terms = Indexy::get_all_terms();
		$index = Indexy::get_index($ignore_synonyms);

		// Build the letter link box.
		$output = "<ul class=\"glossary-index-letters\">\n";
		foreach( $index as $group_start => $group_terms )
		{
			$output .= "\t<li><a href=\"#glossary-index-" . $group_start . "\">";
			$output .= strtoupper($group_start);
			$output .= "</a></li>\n";
		}
		$output .= "</ul>\n";

		// Build the list of terms.
		$output .= "<div class=\"glossary-index\">\n";
		foreach( $index as $group_start => $group_terms )
		{
			$output .= "\t<a id=\"glossary-index-" . $group_start . "\">";
			$output .= "<h2>" . strtoupper($group_start) . "</h2></a>\n";
			$output .= "\t<ul>\n";

			foreach( $group_terms as $term )
			{
				$term_info = $terms[$term];
				$output .= "\t\t<li><a href=\"" . get_permalink($term_info->page_id) . "\">";
				$output .= $term_info->term;
				$output .= "</a></li>\n";
			}

			$output .= "\t</ul>\n";
		}
		$output .= "</div>\n";

		// All done.
		return $output;
	}
}

?>