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
 * @class		Indexy
 * @brief		Main class for the Indexy plugin; acts as container for most of the functions.
 */
abstract class Indexy
{
	/*!
	 * @brief		A cache of glossary terms and related information.
	 *
	 * When initialized, this object will be an array of @c stdClass objects, with the following members:
	 *
	 * * @c term: string; the term, in all lower case
	 * * @c page_id: int; the ID of the post describing this glossary article
	 * * @c excerpt: string; an excerpt of the glossary article text
	 * * @c synonym_for: string; the term for which this term is a synonym
	 * * @c highlight: bool; whether or not to highlight instances of the term in posts
	 * * @c show_on_index: bool; whether or not to include the term in automatically generated index pages
	 *
	 * The key of each entry in the array will be equal to the value of the @c term field (since it is unique for every
	 * term), and they will be sorted alphabetically.
	 */
	private static $terms = null;


	/*!
	 * @brief		Add the glossary post type to the main query, if the option to do so is enabled.
	 * @param		$query
	 *				The @c WP_Query object to process.
	 * @returns		The $query object, but with the glossary post type added to it, if desired.
	 */
	public static function add_glossary_posts_to_query( WP_Query $query )
	{
		if( get_site_option('indexy_include_in_main_loop') == 'yes' && is_home() && $query->is_main_query() )
		{
			$post_types = $query->get('post_type');

			// Respect whatever may already be there.
			if( is_array($post_types) )
			{
				$post_types[] = 'glossary';
			}
			else
			{
				$post_types = array( 'post', 'glossary' );
			}

			$query->set( 'post_type', $post_types );
		}

		return $query;
	}


	/*!
	 * @brief		Add action links to the 'Plugins' section of the administration area.
	 * @param		$links
	 *				The list of action links for the plugin.
	 * @returns		The new list of links, as array.
	 *
	 * @note		This function is meant as a filter for the plugin_action_links_{FILE} hook.
	 */
	public static function add_plugin_action_links( $links )
	{
		// Add the donate link.
		$donate_link  = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6PUCCX9W5F9QA" target="_blank">';
		$donate_link .= __( 'Donate', 'indexy' );
		$donate_link .= '</a>';
		array_unshift( $links, $donate_link );

		// Add the settings link.
		$settings_link  = '<a href="options-general.php?page=indexy_settings_main">';
		$settings_link .= __( 'Settings', 'indexy' );
		$settings_link .= '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}


	/*!
	 * @brief		Retrieve a list of all terms and their associated information
	 * @param		$no_cache
	 *				If true, the result cache will not be used.
	 * @returns		An array containing @c stdClass objects, holding data about the term. Please refer to the
	 *				documentation for @c Indexy::$terms for more details.
	 *
	 * @note		The output of this function is cached. If a glossary page is added after the first time this
	 *				function has been called, it will not be included in the results, unless @c $no_cache is set.
	 */
	public static function get_all_terms( $no_cache = false )
	{
		// Do we have the cache?
		if( is_array( Indexy::$terms && !$no_cache ) )
		{
			return Indexy::$terms;
		}

		Indexy::$terms = array();

		// Run a query to get all published glossary articles.
		$pages = get_posts(array
		(
			'posts_per_page'			=> -1,
			'post_type'					=> 'glossary',
			'post_status'				=> 'publish'
		));

		// Did we get any?
		if( empty($pages) )
		{
			return array();
		}

		// Build the actual list of glossary terms.
		foreach( $pages as $page )
		{
			// Get the custom fields for this page...
			$custom_fields = get_post_custom($page->ID);

			// Figure out the excerpt for this term.
			$term_excerpt = '';

			if( empty($page->post_excerpt) )
			{
				// Auto-generate an excerpt based on the page content.
				$excerpt_length = apply_filters( 'indexy_excerpt_length', 55 );
				$term_excerpt = strip_shortcodes($page->post_content);;

				if( $excerpt_length > 0 )
				{
					$term_excerpt = wp_trim_words( $term_excerpt, $excerpt_length, '' );
				}

				$term_excerpt = apply_filters( 'indexy_excerpt_text', $term_excerpt );
			}
			else
			{
				// A handcrafted excerpt is provided; use that as-is.
				$term_excerpt = $page->post_excerpt;
			}

			// Create the object holding the data for this term.
			$term					= new stdClass();
			$term->term				= strtolower(trim($page->post_title));
			$term->page_id			= $page->ID;
			$term->excerpt			= $term_excerpt;
			$term->synonym_for		= null;
			$term->highlight		= true;
			$term->show_on_index	= true;

			// Determine the actual values for the highlight and show_on_index fields.
			if( isset($custom_fields['glossary_highlighting'][0]) && $custom_fields['glossary_highlighting'][0] == 'none' )
			{
				$term->highlight		= false;
			}
			if( isset($custom_fields['glossary_index'][0]) && $custom_fields['glossary_index'][0] == 'none' )
			{
				$term->show_on_index	= false;
			}

			// Add the term to the master list.
			Indexy::$terms[$term->term] = $term;

			// Get a list of synonyms for this term, if any are defined.
			if( !isset($custom_fields['glossary_synonyms']) || !is_array($custom_fields['glossary_synonyms']) )
			{
				continue;
			}

			$synonyms = $custom_fields['glossary_synonyms'];
			$synonyms = explode( "\n", $synonyms[0] );

			// Go over the list of synonyms and add them into the list of terms.
			foreach( $synonyms as $synonym )
			{
				$synonym = strtolower(trim($synonym));

				// Do we already have a term (or another synonym) by this name?
				// We make this check so we don't override a defined term with a synonym.
				if( isset(Indexy::$terms[$synonym]) )
				{
					continue;
				}

				// Go ahead and create it.
				$term						= new stdClass();
				$term->term					= $synonym;
				$term->page_id				= $page->ID;
				$term->excerpt				= $term_excerpt;
				$term->synonym_for			= strtolower(trim($page->post_title));
				$term->highlight			= true;
				$term->show_on_index		= true;

				if( isset($custom_fields['glossary_highlighting'][0]) && $custom_fields['glossary_highlighting'][0] != 'all' )
				{
					$term->highlight		= false;
				}
				if( isset($custom_fields['glossary_index'][0]) && $custom_fields['glossary_index'][0] != 'all' )
				{
					$term->show_on_index	= false;
				}

				Indexy::$terms[$term->term] = $term;
			}
		}

		// Sort the list we built alphabetically.
		ksort(Indexy::$terms);

		// All done!
		return Indexy::$terms;
	}


	/*!
	 * @brief		Get an index of all glossary terms.
	 * @param		$ignore_synonyms
	 *				Set to @c true to ignore synonyms and include only original terms; @c false to include synonyms.
	 * @returns		An associative array, listing all glossary terms per starting character. Some starting characters
	 *				are grouped together, and the term's settings to include or exclude the term from the index are
	 *				respected.
	 */
	public static function get_index( $ignore_synonyms = false )
	{
		// Get a list of all terms.
		$terms = Indexy::get_all_terms();

		// Set up the output array.
		$output = array();

		// Set up character groupings.
		$groupings = array
		(
			'0-9'	=> array( '0',  '1',  '2',  '3',  '4',  '5',  '6',  '7',  '8',  '9'  ),
			'!#$'	=> array( '!',  '\'', '#',  '$',  '%',  '&',  "'",  '(',  ')',  '*',
							  '+',  ',',  '-',  '.',  '/',  ':',  ';',  '<',  '=',  '>',
							  '?',  '@',  '[',  '\\', ']',  '^',  '_',  '`',  '{',  '|',
							  '}',  '~'                                                  )
		);

		// Go over all terms.
		foreach( $terms as $term )
		{
			// Ignore synonyms, if asked to do so.
			if( $ignore_synonyms && !is_null($term->synonym_for) )
			{
				continue;
			}

			// Ignore terms that are not to be listed in the index.
			if( !$term->show_on_index )
			{
				continue;
			}

			// Determine the term's starting character.
			$term_start = strtoupper($term->term[0]);

			foreach( $groupings as $group_name => $group_contents )
			{
				if( in_array( $term_start, $group_contents ) )
				{
					$term_start = $group_name;
					break;
				}
			}

			// Add the term under that category.
			if( isset($output[$term_start]) )
			{
				$output[$term_start][] = $term->term;	
			}
			else
			{
				$output[$term_start] = array($term->term);
			}
		}

		// Sort things. Since the input is already sorted we shouldn't need this theoretically, but starting character
		// grouping can mess that up, so better be sure.
		ksort($output);
		foreach( $output as &$group )
		{
			ksort($group);
		}

		// All done.
		return $output;
	}


	/*!
	 * @brief		Get a list of posts related to a glossary article.
	 * @param		$post_id
	 *				The ID of the glossary article to find related posts for. If left empty, the current article is used
	 *				(if in the loop).
	 * @param		$method
	 *				Can be 'tag' (default) to search for related posts by posts that are tagged with the term or any of
	 *				its synonyms, or 'search' to do a regular search on the term and its synonyms.
	 * @param		$limit
	 *				The limit on the number of items to return. If set to @c -1, all matching articles will be returned.
	 * @returns		An array of @c WP_Post objects that have tags matching the glossary article title and/or synonyms, or
	 *				@c false on failure. If there are no results, an empty array is returned.
	 *
	 * @note		This function is intended for use in templates.
	 */
	public static function get_related_posts( $post_id = null, $method = 'tag', $limit = -1 )
	{
		global $post;

		// Determine the actual post ID.
		if( !is_int($post_id) )
		{
			if( empty($post) )
			{
				return false;
			}

			$post_id = $post->ID;
		}

		// Query WP for that post.
		$query  = new WP_Query("post_type=glossary&p={$post_id}");
		$result = $query->get_posts();

		if( sizeof($result) != 1 )
		{
			return false;
		}

		$the_post = $result[0];

		// Start building the list of tags.
		$tags = array();
		$tags[] = strtolower($the_post->post_title);

		// Query to see if there are any synonyms to add to the list of tags.
		/*
		 * Note: this bit is commented out for two reasons:
		 * - When using the 'tag' method, WordPress still only matches against the first tag, not posts that have any of the
		 *   tags in the list, as would be desired.
		 * - When using the 'search' method, we cannot use this anyway because WordPress does not (yet) support advanced
		 *   multiple-keyword searches.
		 */
		/*$synonyms = get_post_meta( $the_post->ID, 'glossary_synonyms', false, true );

		if( is_array($synonyms) && sizeof($synonyms) > 0 )
		{
			$synonyms = explode( "\n", $synonyms[0] );

			foreach( $synonyms as $synonym )
			{
				$tags[] = strtolower(trim($synonym));
			}
		}*/

		// Set up arguments for the search.
		$args = array
		(
			'ignore_sticky_posts'	=> true,
			'post_status'			=> 'publish',
			'post_type'				=> 'post',
			'posts_per_page'		=> $limit
		);

		// What search method to use?
		if( $method == 'tag' )
		{
			$args['tag'] = implode( ',', $tags );
		}
		elseif( $method == 'search' )
		{
			$args['s'] = implode( ' ', $tags );
		}
		else
		{
			trigger_error( "Indexy::get_related_posts(): invalid value specified for \$method", E_USER_WARNING );
		}

		// Query for the list of related posts and return it.
		$query = new WP_Query($args);
		$result = $query->get_posts();

		return $result;
	}


	/*!
	 * @brief		Get a list of synonyms for the current glossary article.
	 * @returns		An alphabetically sorted array containing a list of synonyms for the current article, or @c false on
	 *				failure. If no synonyms are found, an empty array is returned instead.
	 */
	public static function get_synonyms()
	{
		global $post;

		// Are we currently in the loop for a glossary article?
		if( get_post_type() != 'glossary' )
		{
			return false;
		}

		// Find synonyms for this article.
		$synonyms = get_post_meta( $post->ID, 'glossary_synonyms', false, true );

		if( is_array($synonyms) && sizeof($synonyms) > 0 )
		{
			$synonyms = explode( "\n", $synonyms[0] );
		}
		else
		{
			return array();
		}

		// Sanitize.
		foreach( $synonyms as $key => $synonym )
		{
			$synonyms[$key] = strtolower(trim($synonym));
		}

		// Sort.
		sort($synonyms);

		return $synonyms;
	}


	/*!
	 * @brief		Get the information for a given term.
	 * @param		$term
	 *				The term to fetch information for.
	 * @returns		A @c stdClass object holding information about the term, or @c false if the given term does not exist.
	 */
	public static function get_term( $term )
	{
		Indexy::get_all_terms();

		$term = strtolower(trim($term));
		return ( isset(Indexy::$terms[$term]) ? Indexy::$terms[$term] : false );
	}


	/*!
	 * @brief		Highlight a term.
	 * @param		$text
	 *				The text to highlight.
	 * @param		$term
	 *				The term to use for highlighting the text. Defaults to the same value as $text.
	 * @return		The term, contained in the properHTML code to highlight it.
	 */
	public static function highlight_term( $text, $term = null )
	{
		if( is_null($term) )
		{
			$term = $text;
		}

		// Get information about the term we're trying to highlight for. Return just the text if the term is invalid.
		$term_info = Indexy::get_term($term);

		if( $term_info === false )
		{
			return $text;
		}

		// Highlight the text depending on the user's preference for highlighting style.
		switch( get_option('indexy_highlight_style') )
		{
			case 'simple':
				// Simple highlighting using just a plain hyperlink.
				$result  = "<a href=\"";
				$result .= get_permalink($term_info->page_id);
				$result .= "\" class=\"glossary simple\" title=\"" . __( 'View definition for', 'indexy') . " '";
				$result .= $term_info->term;
				$result .= "'.\">";
				$result .= $text;
				$result .= "</a>";
				return $result;
			case 'popup':
				// Popup style highlighting, showing an excerpt of the definition page.
				$result  = "<a href=\"";
				$result .= get_permalink($term_info->page_id);
				$result .= "\" class=\"glossary popup\" title=\"" . __( 'View definition for', 'indexy') . " '";
				$result .= $term_info->term;
				$result .= "'.\" data-term=\"";
				$result .= $term_info->term;
				$result .= "\" data-excerpt=\"";
				$result .= $term_info->excerpt;
				$result .= "\">";
				$result .= $text;
				$result .= "</a>";
				return $result;
			default:
				trigger_error( "Indexy::highlight_term(): invalid value for the indexy_highlight_style option", E_USER_WARNING );
				return $text;
		}
	}


	/*!
	 * @brief		Highlight the first occurence of a glossary term in the body of a post and link it to the respective
	 *				glossary page.
	 * @param		$content
	 *				The post body.
	 * @returns		The post body, with the glossary terms highlighted.
	 *
	 * This function is meant to be used as a filter (for hook the_content).
	 */
	public static function highlight_post( $content )
	{
		global $post;

		// Is highlighting disabled for this particular post?
		if( !empty($post) && (int)get_post_meta( $post->ID, 'glossary_disable_highlight', true ) )
		{
			return $content;
		}

		// Is highlighting enabled for this type of post?
		$highlight_post_types = explode( ',', get_site_option('indexy_highlight_post_types') );
		if( !in_array( $post->post_type, $highlight_post_types ) )
		{
			return $content;
		}

		// Get a list of terms and their associated glossary pages.
		$terms = Indexy::get_all_terms();
		$terms = Indexy::sort_for_highlighting($terms);

		// We need to know how to handle repeated occurences of terms.
		$current = get_site_option('indexy_highlight_repeat');
		$current = explode( ',', $current );
		$repeat_count = (int)$current[0];
		$repeat_skip  = (int)$current[1];

		if( $repeat_count == -1 )
		{
			$repeat_skip = 0;
		}
		if( $repeat_skip > 0 )
		{
			$repeat_count *= $repeat_skip;
		}

		// Go over each term.
		foreach( $terms as $term )
		{
			// Ignore terms that are not to be highlighted.
			if( !$term->highlight )
			{
				continue;
			}

			// Special case: do not search for the term (or any synonyms) if we are currently trying to display the
			// glossary page for this term. That'd be a little silly.
			if( !empty($post) && $post->ID == $term->page_id )
			{
				continue;
			}

			// Hit counter.
			$hit_count = 0;

			// Do a loopy thing to find occurences of the term that matches our criteria.
			$search_offset = 0;
			while(true)
			{
				// Does this term occur in the post text?
				$content_length	= strlen($content);
				$term_length	= strlen($term->term);
				$match_pos		= stripos( $content, $term->term, $search_offset );
				$search_offset	= $match_pos + 1;

				if( $match_pos === false )
				{
					continue 2;
				}

				// Determine if this match is inside a shortcode tag.
				$last_lbrace = strripos( substr( $content, 0, $match_pos ), '[' );
				$last_rbrace = strripos( substr( $content, 0, $match_pos ), ']' );

				if( $last_lbrace !== false && ( $last_rbrace === false || $last_lbrace > $last_rbrace ) )
				{
					continue;
				}

				// Determine if this match is inside an indexy_ignore shortcode. This check is similar to what happens
				// below when making sure the term is not inside a HTML tag. Note that this method of checking allows
				// the case of no end tag existing at all; this is useful to entirely exclude highlighting beyond a
				// given point in the post text.
				$last_start_tag = strripos( substr( $content, 0, $match_pos ), '[indexy_ignore' );
				$last_end_tag   = strripos( substr( $content, 0, $match_pos ), '[/indexy_ignore]' );

				if( $last_start_tag !== false && ( $last_end_tag === false || $last_start_tag > $last_end_tag ) )
				{
					continue;
				}

				// Determine if this match is inside a HTML tag (as attribute), as replacing such an occurence would
				// break that tag.
				$last_lbrace = strripos( substr( $content, 0, $match_pos ), '<' );
				$last_rbrace = strripos( substr( $content, 0, $match_pos ), '>' );

				if( $last_lbrace !== false && ( $last_rbrace === false || $last_lbrace > $last_rbrace ) )
				{
					continue;
				}

				// Determine if this match is inside certain HTML tags (between tags, like <h1> (here) </h1>),
				// like headers. Replacing a term within a header looks awkward and may break some themes, so we don't
				// want to do that. Also, placing links within an existing link is generally bad practice. This
				// algorithm might break if the HTML is broken in the first place, but if that's the case, what do you
				// expect to happen?
				$avoid_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a' );

				foreach( $avoid_tags as $tag )
				{
					$start_tag = '<' . $tag; // no end bracked to account for possible attributes
					$end_tag   = '</' . $tag . '>';

					$last_start_tag = strripos( substr( $content, 0, $match_pos ), $start_tag );
					$last_end_tag   = strripos( substr( $content, 0, $match_pos ), $end_tag );

					if( $last_start_tag !== false && ( $last_end_tag === false || $last_start_tag > $last_end_tag ) )
					{
						continue 2;
					}
				}

				// Only use this occurence of the term if it is a word on its own. Get the character before and after
				// the term from its position in $content and make sure that its not a letter. Deal with special cases
				// of the term being at the start or end of $content.
	 			$match_char_pre  = ( $match_pos == 0 ? null : $content[$match_pos - 1] );
	 			$match_char_post = ( $match_pos + $term_length + 1 >= $content_length ? null : $content[$match_pos + $term_length] );

				$letters = 'abcdefghijklmnopqrstuvwxyz0123456789';
				if( $match_char_pre != null && stripos( $letters, $match_char_pre ) !== false )
				{
					continue;
				}
				if( $match_char_post != null && stripos( $letters, $match_char_post ) !== false )
				{
					continue;
				}

				// If all the above checks passed and we're still here, we have a hit. Now it's time to check if we're
				// going to highlight this hit.
				$hit_count++;

				if( $repeat_skip != 0 && ( $hit_count % $repeat_skip ) != 0 )
				{
					continue;
				}

				// Separate the content before and after the term, and the term itself, to maintain capitalization
				$content_pre  = substr( $content, 0, $match_pos );
				$content_term = substr( $content, $match_pos, $term_length );
				$content_post = substr( $content, $match_pos + $term_length );

				// Insert the link! Yay.
				$content  = $content_pre;
				$content .= Indexy::highlight_term($content_term);
				$content .= $content_post;

				// We're done with this term if we've reached the repeat highlight count.
				if( $repeat_count == 0 || ( $repeat_count > 0 && $hit_count >= $repeat_count ) )
				{
					continue 2;
				}
			}
		}

		return $content;
	}


	/*!
	 * @brief		Perform actions necessary to properly activate the plugin.
	 */
	public static function plugin_activate_hook()
	{
		// Call setup(), to ensure that things are set up as they should be.
		//! @todo Figure out if the activation hook is called before or after the init hook. If it's after, we don't
		//!       need this call, or the run-only-once check in setup().
		Indexy::setup();

		// Flush rewrite rules so that permalinks for glossary posts work.
		flush_rewrite_rules();
	}


	/*!
	 * @brief		Perform actions necessary to properly deactivate the plugin.
	 */
	public static function plugin_deactivate_hook()
	{
		// Flush rewrite rules to that permalinks for glossary posts are no longer valid.
		flush_rewrite_rules();

		// Delete the welcome panel user metadata.
		Indexy::set_users_meta( 'indexy_welcome_panel', null, 'administrator', 'delete' );
	}


	/*!
	 * @brief		Perform actions necessary to properly uninstall the plugin.
	 */
	public static function plugin_uninstall_hook()
	{
		// Delete the welcome panel user metadata.
		Indexy::set_users_meta( 'indexy_welcome_panel', null, 'administrator', 'delete' );
	}


	/*!
	 * @brief		Add, update, or delete a meta value for all users with a given role.
	 * @param		$meta_name
	 *				The name of the metadata.
	 * @param		$meta_value
	 *				The (new) value for the metadata.
	 * @param		$role
	 *				The role to apply the update to.
	 * @param		$action
	 *				Specified the action to take. Can be @c 'add', @c 'update', or @c 'delete'.
	 * @returns		The number of users that were updated, or @c false on failure.
	 */
	public static function set_users_meta( $meta_name, $meta_value, $role, $action = 'add' )
	{
		// Get a list of users with the specified role.
		$args  = array( 'role' => $role );
		$users = get_users($args);

		if( empty($users) || !is_array($users) )
		{
			return false;
		}

		// Go over them...
		foreach( $users as $user )
		{
			switch($action)
			{
				case 'add':
					add_user_meta( $user->ID, $meta_name, $meta_value, true );
					break;
				case 'update':
					update_user_meta( $user->ID, $meta_name, $meta_value );
					break;
				case 'delete':
					delete_user_meta( $user->ID, $meta_name );
					break;
			}
		}

		// All done.
		return sizeof($users);
	}


	/*!
	 * @brief		Initialize the Indexy plugin.
	 */
	public static function setup()
	{
		// Make sure this function does things only once.
		// The only reason this bit of code exists is because of plugin_activate_hook().
		static $done = false;

		if( $done )
		{
			return;
		}
		else
		{
			$done = true;
		}

		// Register the 'glossary' post type.
		register_post_type
		(
			'glossary',
			array
			(
				'labels'				=> array
				(
					'name'				=> 'Glossary Pages',
					'singular_name'		=> 'Glossary Page',
					'add_new_item'		=> 'Add New Glossary Page',
					'edit_item'			=> 'Edit Glossary Page',
					'new_item'			=> 'New Glossary Page',
					'view_item'			=> 'View Glossary Page'
				),
				'public'				=> true,
				'exclude_from_search'	=> false,
				'publicly_queryable'	=> true,
				'show_ui'				=> true,
				'show_in_nav_menus'		=> false,
				'show_in_menu'			=> true,
				'show_in_admin_bar'		=> true,
				'menu_position'			=> 21,
				'menu_icon'				=> 'data:image/svg+xml;base64,' . base64_encode(file_get_contents( INDEXY_PLUGIN_PATH . '/images/admin-icon.svg' )),
				'hierarchical'			=> false,
				'supports'				=> array
				(
					'author',
					'comments',
					'editor',
					'excerpt',
					'revisions',
					'title'
				),
				'has_archive'			=> false
			)
		);

		// Register the options for this plugin, and their default values.
		add_site_option( 'indexy_highlight_repeat',			'0,0'										);
		add_site_option( 'indexy_highlight_post_types',		'glossary,media,page,post'					);
		add_site_option( 'indexy_highlight_style',			'popup'										);
		add_site_option( 'indexy_include_css_file',			'yes'										);
		add_site_option( 'indexy_include_in_main_loop',		'no'										);
	}


	/*!
	 * @brief		Register the meta boxes for the post editor.
	 * @returns		An array defining the meta box for the glossary plugin.
	 */
	public static function setup_metabox()
	{
		$metabox[] = array
		(
			'title'						=> __( 'Glossary', 'indexy' ),
			'post_types'				=> 'glossary',
			'context'					=> 'normal',
			'priority'					=> 'high',
			'autosave'					=> true,
			'fields'					=> array
			(
				array
				(
					'name'				=> __( 'Synonyms', 'indexy' ),
					'id'				=> 'glossary_synonyms',
					'desc'				=> __( 'Enter one synonym per line. Capitalization doesn\'t matter.', 'indexy' ),
					'type'				=> 'textarea',
					'cols'				=> 50,
					'rows'				=> 3
				),
				array
				(
					'name'				=> __( 'Highlighting', 'indexy' ),
					'id'				=> 'glossary_highlighting',
					'std'				=> 'all',
					'type'				=> 'radio',
					'options'			=> array
					(
						'all'			=> __( 'Highlight the term and its synonyms in articles', 'indexy' ) . '<br />',
						'term'			=> __( 'Highlight only the term in articles', 'indexy' ) . '<br />',
						'none'			=> __( 'Do not highlight the term or its synonyms', 'indexy' )
					)
				),
				array
				(
					'name'				=> __( 'Glossary Index', 'indexy' ),
					'id'				=> 'glossary_index',
					'std'				=> 'all',
					'type'				=> 'radio',
					'options'			=> array
					(
						'all'			=> __( 'Show the term and its synonyms in the glossary index', 'indexy' ) . '<br />',
						'term'			=> __( 'Show only the term in the glossary index', 'indexy' ) . '<br />',
						'none'			=> __( 'Hide the term and its synonyms from the glossary index', 'indexy' )
					)
				)
			)
		);
		$metabox[] = array
		(
			'title'						=> __( 'Glossary', 'indexy' ),
			'post_types'				=> array( 'post', 'page' ),
			'context'					=> 'advanced',
			'priority'					=> 'low',
			'autosave'					=> true,
			'fields'					=> array
			(
				array
				(
					'name'				=> __( 'Highlighting', 'indexy' ),
					'id'				=> 'glossary_disable_highlight',
					'std'				=> false,
					'type'				=> 'checkbox',
					'desc'				=> __( 'Disable highlighting of glossary terms for this article', 'indexy' )
				)
			)
		);

		return $metabox;
	}


	/*!
	 * @brief		Register required plugins.
	 */
	public static function setup_required_plugins()
	{
		$plugins = array
		(
			array
			(
				'name'		=> 'Meta Box',
				'slug'		=> 'meta-box',
				'required'	=> true
			)
		);

	    $config = array(
			'default_path' => '',						// Default absolute path to pre-packaged plugins.
			'menu'         => 'tgmpa-install-plugins',	// Menu slug.
			'has_notices'  => true,						// Show admin notices or not.
			'dismissable'  => false,					// If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',						// If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,					// Automatically activate plugins after installation or not.
			'message'      => ''						// Message to output right before the plugins table.
	    );

		tgmpa( $plugins, $config );
	}


	/*!
	 * @brief		Register JavaScript files.
	 */
	public static function setup_scripts()
	{
		// If popup style highlighting is selected, make sure the JS for that is enqueued.
		if( get_option("indexy_highlight_style") == 'popup' )
		{
			wp_enqueue_script( 'indexy_popup', plugins_url( '/js/indexy_popup.js', INDEXY_PLUGIN_FILE ), array('jquery') );

			// Export some data for settings and localization to the popup script.
			//! @todo Make the offset_x and offset_y values configurable, somehow.
			wp_localize_script( 'indexy_popup', 'indexy_popup_data', array
			(
				'readmore_text'		=> __( 'Click to read more...', 'indexy' ),
				'popup_offset_x'	=> -50,
				'popup_offset_y'	=> 30
			));
		}
	}


	/*!
	 * @brief		Register style sheets.
	 */
	public static function setup_stylesheet()
	{
		// Don't do anything if the stylesheet is disabled in the options.
		if( get_site_option('indexy_include_css_file') != 'yes' )
		{
			return;
		}

		// Use the file located in the current theme's directory if one exists; use the default one shipped with the plugin
		// otherwise. This lets themes easily override the plugin's default CSS if so desired.
		if( file_exists( get_stylesheet_directory() . '/indexy.css' ) )
		{
			wp_enqueue_style( 'indexy', get_stylesheet_directory_uri() . '/indexy.css' );
		}
		else
		{
			wp_enqueue_style( 'indexy', plugins_url( 'style/indexy.css', INDEXY_PLUGIN_FILE ) );
		}
	}


	/*!
	 * @brief		Load the plugin's text domain.
	 */
	public static function setup_textdomain()
	{
		load_plugin_textdomain( 'indexy', false, basename(dirname(INDEXY_PLUGIN_FILE)) . '/languages/' );
	}


	/*!
	 * @brief		Register widgets.
	 */
	public static function setup_widgets()
	{
		register_widget('Indexy_Glossary_Index_Widget');
		register_widget('Indexy_Related_Posts_Widget');
	}


	/*!
	 * @brief		Display a list of posts related to a glossary article.
	 * @param		$post_id
	 *				The ID of the glossary article to find related posts for. If left empty, the current article is used
	 *				(if in the loop).
	 * @param		$method
	 *				Can be 'tag' (default) to search for related posts by posts that are tagged with the term or any of
	 *				its synonyms, or 'search' to do a regular search on the term and its synonyms.
	 * @param		$limit
	 *				The limit on the number of items to return. If set to @c -1, all matching articles will be returned.
	 * @param		$print
	 *				If set to @c true (the default), the resulting HTML code is printed.
	 * @returns		The output.
	 *
	 * @note		This function is intended for use in templates.
	 */
	public static function show_related_posts( $post_id = null, $method = 'tag', $limit = -1, $print = true )
	{
		$related_posts = Indexy::get_related_posts( $post_id, $method, $limit );

		if( !$related_posts )
		{
			return false;
		}

		$output = "<ul class=\"glossary-related-posts\">\n";

		foreach( $related_posts as $related_post )
		{
			$output .=  "<li><a href=\"";
			$output .=  get_permalink($related_post->ID);
			$output .=  "\">";
			$output .=  $related_post->post_title;
			$output .=  "</a></li>\n";
		}

		$output .= "</ul>\n";

		if( $print )
		{
			print $output;
		}

		return $output;
	}


	/*!
	 * @brief		Sort a list of glossary terms in a way that is appropriate for highlighting.
	 * @param		$terms
	 *				The list of terms to sort. This must be an array as provided by @c Indexy::get_all_terms().
	 * @returns		The list from @c terms, but sorted in order for highlighting.
	 *
	 * This function sorts the list of terms in such a way that terms that include words or phrases that are terms of
	 * their own, will be placed before those terms, in order to avoid incorrect highlighting. For example, if a list of
	 * terms including 'foo' and 'foo bar', were to be sorted alphabetically, 'foo' would be placed first, and as such,
	 * would be processed first. If the phrase 'foo bar' were to be used in an article, it would be highlighted as if it
	 * was just 'foo' (unless a previous occurence of 'foo' was highlighted already). This would be incorrect.
	 *
	 * @note		Currently, this function breaks the key associations of the terms list created by
	 *				Indexy::get_all_terms(). This can be fixed if needed, but doing it this way makes the sorting
	 *				routine simpler, and Indexy::highlight_post() - the only function that calls this one - does not
	 *				need the key association to function.
	 */
	private static function sort_for_highlighting( array $terms )
	{
		// Re-map the array to ensure sequential numeric keys.
		$terms = array_values($terms);

		// Define a little helper function for recursive calling.
		$sort_func = function( array &$terms )
		{
			// Go over each item in the list.
			foreach( $terms as $term_key => $term )
			{
				// See if any term before the current one includes it.
				for( $i = $term_key + 1; $i < sizeof($terms); $i++ )
				{
					if( stripos( $terms[$i]->term, $term->term ) !== false )
					{
						// Swap the two terms.
						$temp				= $term;
						$terms[$term_key]	= $terms[$i];
						$terms[$i]			= $temp;

						return true;
					}
				}
			}

			// Nothing found.
			return false;
		};

		// Run that function as long as it has something to do.
		while($sort_func($terms));

		// All done.
		return $terms;
	}
}

?>