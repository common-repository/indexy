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
 * @brief		Hide the glossary tooltip popup.
 * @param		event
 *				The mouseout event object.
 */
function indexy_hide_popup( event )
{
	jQuery(".glossary-popup").hide( 250, indexy_remove_popup );
}


/*!
 * @brief		Remove all expired popups.
 */
function indexy_remove_popup()
{
	jQuery(".glossary-popup").each(function(index)
	{
		if( jQuery(this).css("display") == "none" )
		{
			jQuery(this).remove();
		}
	});
}


/*!
 * @brief		Show the glossary tooltip popup.
 * @param		event
 *				The mouseover event object.
 *
 * This function is meant as mouseover event handler for links that have a 'data-term' and 'data-excerpt' attribute set.
 * Those two attributes are expected to contain the respective term's name and an excerpt of the glossary page text,
 * respectively.
 */
function indexy_show_popup( event )
{
	// Collect information...
	var link_elem    = jQuery(event.currentTarget);
	var link_pos     = link_elem.offset();
	var term_name    = link_elem.data('term');
	var term_linkurl = link_elem.attr('href');
	var term_excerpt = link_elem.data('excerpt');

	// Remove the title attribute from the link (if it is set) to prevent the standard title popup from happening.
	link_elem.attr( 'title', '' );

	// Create the popup HTML code.
	var popup_id = Math.ceil(Math.random() * 100000);
	var popup = '<div id="glossary-popup-' + popup_id + '" class=\"glossary-popup\">'
			  + term_excerpt + '<br />'
			  + '<div class="glossary-popup-readmore">' + indexy_popup_data.readmore_text + '</div>';
	          + '</div>';

	// Determine where to position the popup.
	var x = Math.round( link_pos.left + parseInt(indexy_popup_data.popup_offset_x) );
	var y = Math.round( link_pos.top  + parseInt(indexy_popup_data.popup_offset_y) );

	// Create and position the popup.
	jQuery("body").append(popup);
	var p = jQuery( "#glossary-popup-" + popup_id );
	p.css( "position", "absolute" );
	p.css( "top", y );
	p.css( "left", x );
	p.css( "z-index", 10 );
	p.show(250);
}


/*!
 * Set the mouse hover handlers for a.glossary links when the document is ready.
 */
jQuery(document).ready(function()
{
	jQuery("a.glossary").hover( indexy_show_popup, indexy_hide_popup );
});
