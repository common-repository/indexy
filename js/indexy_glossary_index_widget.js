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
 * @brief		Object constructor.
 * @param		widget_container
 *				A jQuery object pointing to a single container div of a glossary index widget
 *				(ie, div.glossary-index-widget).
 */
var IndexyGlossaryIndexWidget = function( widget_container )
{
	/*!
	 * this.widget_container holds a jQuery object pointing to the container div for this widget.
	 */
	this.widget_container = widget_container.first();


	/*!
	 * this.current_letter holds the current letter beind displayed.
	 */
	this.current_letter = "";


	// Register event handlers.
	this.widget_container.children(".widget-header").children(".column-arrow").on( "click", jQuery.proxy( this.go_to_adjacent, this ) );
	this.widget_container.children(".widget-letter-index").find("li").on( "click", jQuery.proxy( this.go_to_letter, this ) );

	// Determine which starting letter to display initially. If we're displaying a glossary page, one of the links in
	// the letter index will be marked with the 'current-page' class; otherwise, we just whichever letter is the first
	// one that we actually have (note that not all letters may occur in the list because we don't necessarily have at
	// least one glossary page starting with each possible letter).
	var starting_letter = '';
	var current_page = this.widget_container.children(".widget-letter-block").find(".current-page");
	if( current_page.length > 0 )
	{
		starting_letter = current_page.parent().parent().data("starting-letter");
	}
	else
	{
		// Find the first starting letter that isn't one of the special groups.
		this.widget_container.children(".widget-letter-block").each(function( index, element )
		{
			starting_letter = jQuery(element).data("starting-letter");

			if( starting_letter.charAt(0) != '0' && starting_letter.charAt(0) != '!' )
			{
				return false;
			}
		});
	}

	// Find that letter block and display it.
	this.widget_container.children(".widget-letter-block").each(function( index, element )
	{
		element = jQuery(element);
		if( element.data("starting-letter") == starting_letter )
		{
			element.show();
			return false;
		}
	});

	// ... and don't forget to update the current letter display.
	this.widget_container.find(".column-currentletter").html(starting_letter.toUpperCase());
	this.current_letter = starting_letter;
}


/*!
 * @brief		Switch the widget to show the data for a different starting letter.
 * @param		letter
 *				The starting letter to show; must be lower case.
 * @returns		@c true on success, or @c false if the requested letter is already being displayed.
 */
IndexyGlossaryIndexWidget.prototype.go_to = function( letter )
{
	// Do nothing if we're already showing that letter.
	if( this.current_letter == letter )
	{
		return false;
	}
	this.current_letter = letter;

	// Hide whatever may be currently visible.
	this.widget_container.children(".widget-letter-block").slideUp(200);

	// Update the current letter display.
	this.widget_container.find(".column-currentletter").html(letter.toUpperCase());

	// Find and display the block for the new letter.
	this.widget_container.children(".widget-letter-block").each(function( index, element )
	{
		element = jQuery(element);
		if( element.data("starting-letter") == letter )
		{
			element.delay(200);
			element.slideDown(200);
			return false;
		}
	});

	return true;
}


/*!
 * @brief		Switch to an adjacent (left or right) letter.
 *				This function is meant as event handler.
 * @param		event
 *				The event object.
 */
IndexyGlossaryIndexWidget.prototype.go_to_adjacent = function( event )
{
	event.preventDefault();

	// Determine which direction to go in.
	var direction = jQuery(event.currentTarget).data("direction");

	if( direction != 'left' && direction != 'right' )
	{
		return false;
	}

	// Find the target block.
	var new_block      = false;
	var current_letter = this.current_letter;
	this.widget_container.children(".widget-letter-block").each(function( index, element )
	{
		element = jQuery(element);
		var element_letter = element.data("starting-letter");

		if( direction == 'left' )
		{
			// On each iteration, assign this block's letter to new_block. Before doing that, if current_letter is
			// equal to element_letter, we found the current one, so break out of the loop *before* changing the value
			// of new_block, so that it still points to the previous one.
			if( element_letter == current_letter )
			{
				return false;
			}

			new_block = element_letter;
		}
		else if( direction == 'right' )
		{
			// When we find the current letter, set new_block to true to signify that the next letter will be the one
			// we're looking for; then, on the next iteration, take that letter and break out of the loop.
			if( element_letter == current_letter )
			{
				new_block = true;
			}
			else if( new_block == true )
			{
				new_block = element_letter;
				return false;
			}
		}
	});

	// If new_block is a boolean value and not a string, we're at the end of where we can go in the requested direction.
	if( typeof new_block != "string" )
	{
		return false;
	}

	// Go to the new starting letter!
	return this.go_to(new_block);
}


/*!
 * @brief		Switch directly to a specific starting letter.
 *				This function is meant as event handler.
 * @param		event
 *				The event object.
 */
IndexyGlossaryIndexWidget.prototype.go_to_letter = function( event )
{
	event.preventDefault();
	this.go_to(jQuery(event.currentTarget).html().toLowerCase())
}


var indexy_index_widgets = [];
jQuery(document).ready(function()
{
	jQuery(".glossary-index-widget").each(function( index, element )
	{
		indexy_index_widgets.push(new IndexyGlossaryIndexWidget(jQuery(element)));
	});
});
